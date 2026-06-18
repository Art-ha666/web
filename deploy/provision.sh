#!/usr/bin/env bash
#
# provision.sh — Fresh-VPS bootstrap & security hardening for AKH Solutions.
#
# Target OS : Ubuntu 24.04 LTS (Vultr "Cloud Compute", smallest plan is fine —
#             1 vCPU / 1 GB RAM works thanks to the swap this script creates).
# Run as    : root, ONCE, on a brand-new box.  Re-running is safe (idempotent).
# Goal      : a low-maintenance, secure single-VPS host that runs the app as a
#             Docker Compose stack (php-fpm+nginx+supervisor container + MySQL 8
#             + Redis) behind Cloudflare.
#
# What it does:
#   1.  Sets the timezone.
#   2.  Updates the base system.
#   3.  Creates a non-root sudo "deploy" user and seeds its SSH key.
#   4.  Installs Docker Engine + Docker Compose plugin (official Docker repo).
#   5.  Creates a swap file (cushions small-RAM boxes; tunes swappiness).
#   6.  Configures the UFW firewall (OpenSSH + 80 + 443 only).
#   7.  Installs & enables fail2ban (brute-force protection for SSH).
#   8.  Hardens SSH (no root login, no password auth, key-only).
#   9.  Enables unattended security upgrades.
#  10.  Prints the remaining MANUAL steps.
#
# IMPORTANT — read before running:
#   * Provide your SSH PUBLIC key via the DEPLOY_SSH_PUBKEY env var (or paste it
#     into the variable below) BEFORE running, or you can be locked out once
#     password auth is disabled.  The script REFUSES to disable password auth
#     unless the deploy user has at least one authorized key.
#   * Keep your current root SSH session OPEN until you have confirmed you can
#     log in as the new deploy user in a SECOND terminal.
#
# Usage:
#   scp deploy/provision.sh root@<vps-ip>:/root/
#   ssh root@<vps-ip>
#   export DEPLOY_SSH_PUBKEY="ssh-ed25519 AAAA... you@host"
#   bash /root/provision.sh
#
# Optional env overrides (all have sane defaults):
#   DEPLOY_USER          deploy        # non-root sudo user to create
#   DEPLOY_SSH_PUBKEY    (empty)       # your SSH public key (REQUIRED first run)
#   SSH_PORT             22            # change to a high port to cut log noise
#   TIMEZONE             UTC           # e.g. "Asia/Tashkent"
#   SWAP_SIZE            2G            # swap file size (set "0" to skip)
#   LOCK_TO_CLOUDFLARE   false         # "true" => 80/443 only from Cloudflare IPs
#
set -euo pipefail

# ---------------------------------------------------------------------------
# Configuration (env-overridable)
# ---------------------------------------------------------------------------
DEPLOY_USER="${DEPLOY_USER:-deploy}"
DEPLOY_SSH_PUBKEY="${DEPLOY_SSH_PUBKEY:-}"
SSH_PORT="${SSH_PORT:-22}"
TIMEZONE="${TIMEZONE:-UTC}"
SWAP_SIZE="${SWAP_SIZE:-2G}"
LOCK_TO_CLOUDFLARE="${LOCK_TO_CLOUDFLARE:-false}"

export DEBIAN_FRONTEND=noninteractive

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
log()  { printf '\n\033[1;32m==>\033[0m %s\n' "$*"; }
warn() { printf '\n\033[1;33m[!]\033[0m %s\n' "$*"; }
die()  { printf '\n\033[1;31m[x]\033[0m %s\n' "$*" >&2; exit 1; }

require_root() {
    [ "$(id -u)" -eq 0 ] || die "This script must be run as root."
}

require_ubuntu_2404() {
    # Soft check — warn but don't hard-fail on minor version drift.
    if [ -r /etc/os-release ]; then
        . /etc/os-release
        [ "${ID:-}" = "ubuntu" ] || warn "Designed for Ubuntu; detected '${ID:-unknown}'. Proceeding anyway."
        [ "${VERSION_ID:-}" = "24.04" ] || warn "Designed for Ubuntu 24.04; detected '${VERSION_ID:-unknown}'."
    fi
}

# ---------------------------------------------------------------------------
# 0. Preconditions
# ---------------------------------------------------------------------------
require_root
require_ubuntu_2404
log "Provisioning starting (user=${DEPLOY_USER}, ssh_port=${SSH_PORT}, tz=${TIMEZONE})."

# ---------------------------------------------------------------------------
# 1. Timezone
# ---------------------------------------------------------------------------
log "Setting timezone to ${TIMEZONE}…"
timedatectl set-timezone "${TIMEZONE}"

# ---------------------------------------------------------------------------
# 2. Base system update + essentials
# ---------------------------------------------------------------------------
log "Updating package index and installing essentials…"
apt-get update -y
apt-get upgrade -y
apt-get install -y \
    ca-certificates curl gnupg lsb-release \
    ufw fail2ban unattended-upgrades apt-listchanges \
    git htop ncdu jq

# ---------------------------------------------------------------------------
# 3. Non-root sudo deploy user + SSH key
# ---------------------------------------------------------------------------
log "Ensuring sudo user '${DEPLOY_USER}' exists…"
if ! id -u "${DEPLOY_USER}" >/dev/null 2>&1; then
    adduser --disabled-password --gecos "" "${DEPLOY_USER}"
fi
usermod -aG sudo "${DEPLOY_USER}"

# Passwordless sudo so unattended automation / SSH-key-only login still works.
# (No password is set on this account, so a sudo password prompt would be a dead end.)
echo "${DEPLOY_USER} ALL=(ALL) NOPASSWD:ALL" > "/etc/sudoers.d/90-${DEPLOY_USER}"
chmod 440 "/etc/sudoers.d/90-${DEPLOY_USER}"
visudo -cf "/etc/sudoers.d/90-${DEPLOY_USER}" >/dev/null || die "sudoers validation failed."

DEPLOY_HOME="$(getent passwd "${DEPLOY_USER}" | cut -d: -f6)"
install -d -m 700 -o "${DEPLOY_USER}" -g "${DEPLOY_USER}" "${DEPLOY_HOME}/.ssh"
AUTH_KEYS="${DEPLOY_HOME}/.ssh/authorized_keys"
touch "${AUTH_KEYS}"

if [ -n "${DEPLOY_SSH_PUBKEY}" ]; then
    # Append only if not already present (idempotent).
    if ! grep -qxF "${DEPLOY_SSH_PUBKEY}" "${AUTH_KEYS}" 2>/dev/null; then
        echo "${DEPLOY_SSH_PUBKEY}" >> "${AUTH_KEYS}"
        log "Added provided SSH public key for ${DEPLOY_USER}."
    fi
elif [ -s /root/.ssh/authorized_keys ]; then
    # Fall back to whatever key let root in (Vultr injects it at boot).
    warn "DEPLOY_SSH_PUBKEY not set; copying root's authorized_keys to ${DEPLOY_USER}."
    cat /root/.ssh/authorized_keys >> "${AUTH_KEYS}"
fi
# De-dupe and fix ownership/perms.
sort -u "${AUTH_KEYS}" -o "${AUTH_KEYS}"
chown "${DEPLOY_USER}:${DEPLOY_USER}" "${AUTH_KEYS}"
chmod 600 "${AUTH_KEYS}"

HAVE_KEY=false
[ -s "${AUTH_KEYS}" ] && HAVE_KEY=true

# ---------------------------------------------------------------------------
# 4. Docker Engine + Compose plugin (official Docker apt repo)
# ---------------------------------------------------------------------------
if ! command -v docker >/dev/null 2>&1; then
    log "Installing Docker Engine + Compose plugin…"
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg
    ARCH="$(dpkg --print-architecture)"
    CODENAME="$(. /etc/os-release && echo "${VERSION_CODENAME}")"
    echo "deb [arch=${ARCH} signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu ${CODENAME} stable" \
        > /etc/apt/sources.list.d/docker.list
    apt-get update -y
    apt-get install -y \
        docker-ce docker-ce-cli containerd.io \
        docker-buildx-plugin docker-compose-plugin
else
    log "Docker already installed ($(docker --version)); skipping install."
fi

systemctl enable --now docker
# Let the deploy user run docker without sudo.
usermod -aG docker "${DEPLOY_USER}"

# Cap container log growth so a chatty app can't fill the small disk.
install -d -m 755 /etc/docker
if [ ! -f /etc/docker/daemon.json ]; then
    cat > /etc/docker/daemon.json <<'JSON'
{
  "log-driver": "json-file",
  "log-opts": { "max-size": "10m", "max-file": "3" },
  "live-restore": true
}
JSON
    systemctl restart docker
fi

# ---------------------------------------------------------------------------
# 5. Swap file (cheap insurance on 1–2 GB boxes)
# ---------------------------------------------------------------------------
if [ "${SWAP_SIZE}" != "0" ] && ! swapon --show | grep -q '/swapfile'; then
    log "Creating ${SWAP_SIZE} swap file…"
    if fallocate -l "${SWAP_SIZE}" /swapfile 2>/dev/null; then :; else
        # fallocate can fail on some filesystems; fall back to dd.
        SWAP_MB="$(numfmt --from=iec "${SWAP_SIZE}" | awk '{print int($1/1024/1024)}')"
        dd if=/dev/zero of=/swapfile bs=1M count="${SWAP_MB}" status=none
    fi
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
    # Lower swappiness: prefer RAM, use swap only under real pressure.
    install -d -m 755 /etc/sysctl.d
    cat > /etc/sysctl.d/99-akh-swap.conf <<'SYSCTL'
vm.swappiness=10
vm.vfs_cache_pressure=50
SYSCTL
    sysctl --system >/dev/null
else
    log "Swap already present or disabled; skipping."
fi

# ---------------------------------------------------------------------------
# 6. UFW firewall — allow only SSH + HTTP + HTTPS
# ---------------------------------------------------------------------------
log "Configuring UFW firewall…"
ufw --force reset >/dev/null
ufw default deny incoming
ufw default allow outgoing

# SSH: allow the chosen port (named profile if still 22, explicit port otherwise).
if [ "${SSH_PORT}" = "22" ]; then
    ufw allow OpenSSH
else
    ufw allow "${SSH_PORT}/tcp" comment 'SSH (custom)'
fi

if [ "${LOCK_TO_CLOUDFLARE}" = "true" ]; then
    # Hardening: only Cloudflare's edge may reach 80/443. Direct-to-origin scans
    # and most bots are dropped. Requires the site to be proxied (orange cloud).
    log "Restricting 80/443 to Cloudflare IP ranges…"
    CF_V4="$(curl -fsS https://www.cloudflare.com/ips-v4 || true)"
    CF_V6="$(curl -fsS https://www.cloudflare.com/ips-v6 || true)"
    if [ -z "${CF_V4}" ]; then
        warn "Could not fetch Cloudflare IP list; falling back to open 80/443."
        ufw allow 80/tcp  comment 'HTTP'
        ufw allow 443/tcp comment 'HTTPS'
    else
        while read -r cidr; do
            [ -n "${cidr}" ] || continue
            ufw allow from "${cidr}" to any port 80  proto tcp comment 'CF HTTP'
            ufw allow from "${cidr}" to any port 443 proto tcp comment 'CF HTTPS'
        done <<< "${CF_V4}"
        while read -r cidr; do
            [ -n "${cidr}" ] || continue
            ufw allow from "${cidr}" to any port 80  proto tcp comment 'CF HTTP6'
            ufw allow from "${cidr}" to any port 443 proto tcp comment 'CF HTTPS6'
        done <<< "${CF_V6}"
        warn "Cloudflare IP ranges change occasionally. See refresh-cloudflare-ufw note in manual steps."
    fi
else
    ufw allow 80/tcp  comment 'HTTP'
    ufw allow 443/tcp comment 'HTTPS'
fi

ufw --force enable
ufw status verbose || true

# ---------------------------------------------------------------------------
# 7. fail2ban — protect SSH from brute force
# ---------------------------------------------------------------------------
log "Configuring fail2ban for SSH…"
cat > /etc/fail2ban/jail.local <<EOF
[DEFAULT]
# Ban for 1 hour after 5 failures within 10 minutes; ignore loopback.
bantime  = 1h
findtime = 10m
maxretry = 5
ignoreip = 127.0.0.1/8 ::1
backend  = systemd

[sshd]
enabled  = true
port     = ${SSH_PORT}
EOF
systemctl enable --now fail2ban
systemctl restart fail2ban

# ---------------------------------------------------------------------------
# 8. SSH hardening — key-only, no root login, no passwords
# ---------------------------------------------------------------------------
# We DROP a config snippet (sshd_config.d) rather than editing the main file,
# so an OS upgrade that rewrites sshd_config won't clobber our hardening.
log "Hardening SSH…"
if [ "${HAVE_KEY}" = "true" ]; then
    cat > /etc/ssh/sshd_config.d/99-akh-hardening.conf <<EOF
# Managed by deploy/provision.sh — AKH Solutions SSH hardening.
Port ${SSH_PORT}
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
ChallengeResponseAuthentication no
PubkeyAuthentication yes
PermitEmptyPasswords no
X11Forwarding no
MaxAuthTries 3
LoginGraceTime 30
AllowUsers ${DEPLOY_USER}
EOF
    # Validate before reloading so a typo can't brick SSH.
    if sshd -t; then
        systemctl reload ssh 2>/dev/null || systemctl reload sshd 2>/dev/null || systemctl restart ssh
        log "SSH hardened: root login disabled, password auth disabled, key-only on port ${SSH_PORT}."
    else
        rm -f /etc/ssh/sshd_config.d/99-akh-hardening.conf
        warn "sshd config test FAILED — hardening reverted. SSH left at defaults."
    fi
else
    warn "No SSH key found for ${DEPLOY_USER}!  SKIPPING password-auth lockdown to avoid lockout."
    warn "Add a key (DEPLOY_SSH_PUBKEY=... ) and re-run this script to finish SSH hardening."
fi

# ---------------------------------------------------------------------------
# 9. Unattended security upgrades
# ---------------------------------------------------------------------------
log "Enabling unattended security upgrades…"
cat > /etc/apt/apt.conf.d/20auto-upgrades <<'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
EOF
cat > /etc/apt/apt.conf.d/50unattended-upgrades <<'EOF'
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
    "${distro_id}ESMApps:${distro_codename}-apps-security";
    "${distro_id}ESM:${distro_codename}-infra-security";
};
Unattended-Upgrade::AutoFixInterruptedDpkg "true";
Unattended-Upgrade::MinimalSteps "true";
Unattended-Upgrade::Remove-Unused-Kernel-Packages "true";
Unattended-Upgrade::Remove-Unused-Dependencies "true";
Unattended-Upgrade::Automatic-Reboot "true";
Unattended-Upgrade::Automatic-Reboot-Time "04:00";
EOF
systemctl enable --now unattended-upgrades

# ---------------------------------------------------------------------------
# 10. Summary + manual steps
# ---------------------------------------------------------------------------
log "Provisioning complete."
cat <<SUMMARY

──────────────────────────────────────────────────────────────────────────
  AKH Solutions VPS — provisioning summary
──────────────────────────────────────────────────────────────────────────
  Deploy user      : ${DEPLOY_USER}  (sudo, NOPASSWD)
  SSH              : port ${SSH_PORT}, key-only $( [ "${HAVE_KEY}" = true ] && echo '(root login + passwords DISABLED)' || echo '(NOT hardened — no key yet!)' )
  Firewall (ufw)   : SSH + 80 + 443 $( [ "${LOCK_TO_CLOUDFLARE}" = true ] && echo '(80/443 limited to Cloudflare)' )
  fail2ban         : active (sshd jail)
  Docker           : $(docker --version 2>/dev/null || echo 'not installed')
  Compose          : $(docker compose version --short 2>/dev/null || echo 'not installed')
  Swap             : $(swapon --show=NAME,SIZE --noheadings 2>/dev/null | tr '\n' ' ' || echo none)
  Auto-updates     : security upgrades enabled, auto-reboot 04:00
──────────────────────────────────────────────────────────────────────────

  NEXT — verify before you close this root session:
    1. In a SECOND terminal:  ssh -p ${SSH_PORT} ${DEPLOY_USER}@<vps-ip>
       Confirm it works AND that 'sudo whoami' returns root.
       (If hardening was skipped above, re-run with DEPLOY_SSH_PUBKEY set.)

  MANUAL steps to finish the deployment (outside this script's scope):
    A. DNS: point akh.dav88.dev at this VPS's IP in Cloudflare and set the
       record to PROXIED (orange cloud). Enable SSL/TLS mode "Full (strict)".
    B. TLS at the origin: create a Cloudflare Origin Certificate and place the
       cert+key where the host's reverse proxy reads them (the compose/proxy
       dimension owns this), OR terminate TLS in Cloudflare and serve plain
       HTTP on the origin if 80/443 is locked to Cloudflare (step LOCK_TO_CLOUDFLARE).
    C. App: as ${DEPLOY_USER}, clone the repo / pull the image and bring up the
       Docker Compose stack (compose file + .env are owned by the deploy/compose
       dimension). The app container listens on 8080 internally.
    D. Secrets: generate APP_KEY, set DB/Redis/mail creds in the production .env
       (never commit it; chmod 600). MySQL + Redis run as compose services.
    E. If you set LOCK_TO_CLOUDFLARE=true, schedule a periodic refresh of the
       Cloudflare IP allow-list (their ranges change a few times a year), e.g. a
       small cron that re-fetches ips-v4/ips-v6 and rewrites the ufw rules.
    F. Optional: change SSH_PORT to a high random port and re-run to cut log noise
       (remember to update the ufw rule and your ~/.ssh/config / known clients).

  Re-running this script is safe and will re-apply any drifted settings.
──────────────────────────────────────────────────────────────────────────
SUMMARY
