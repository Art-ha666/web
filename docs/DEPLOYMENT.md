# AKH Solutions - Deployment & Operations Runbook

Everything about how this site is hosted, deployed, and operated. Last updated 2026-06-19.

> **Security note:** the production server's public IP is **not** written in this (public) repo,
> because Cloudflare proxying hides the origin and publishing it would let attackers bypass
> Cloudflare. The real IP lives in the GitHub Actions variable `VPS_HOST`, the Vultr console,
> and the operator's private notes. This doc uses `<VPS_IP>` as a placeholder.

---

## 1. What this is

A single small VPS runs the whole app as a Docker Compose stack, behind Cloudflare.
Code lives at `github.com/Art-ha666/web` (branch `main`). Push to `main` → CI tests →
auto-build a Docker image → push to GHCR → SSH-deploy to the VPS. No build load on the server.

```
                 ┌─────────────┐      A record (proxied)      ┌──────────────────────── VPS (Ubuntu 24.04) ─────────────────────────┐
   visitors ───▶ │ Cloudflare  │ ───────── HTTPS ──────────▶ │  Caddy :80/:443  (TLS edge, self-signed "tls internal")            │
                 │ (proxy/CDN) │   SSL/TLS mode = Full        │      │ reverse_proxy app:8080 over private docker bridge          │
                 └─────────────┘                              │      ▼                                                            │
                                                              │   app  (php-fpm + nginx :8080 + queue worker)  ◀── edge network    │
                                                              │      │                                                            │
                                                              │      ├── mysql:8.4   ◀─┐                                           │
                                                              │      └── redis:7      │  backend network (internal: true,         │
                                                              │                       │  NO route to the internet)                │
                                                              └───────────────────────┴───────────────────────────────────────────┘
   CI/CD:  push main ─▶ GitHub Actions "CI" (tests) ─▶ "Deploy to Vultr" (build image ─▶ GHCR ─▶ ssh: backup ─▶ migrate ─▶ up)
```

---

## 2. The server (Vultr VPS)

| | |
|---|---|
| Provider / plan | Vultr Cloud Compute `vc2-1c-1gb` |
| Specs | 1 vCPU, 1 GB RAM, 25 GB SSD, ~$5/mo |
| Region | New York (NJ), USA |
| OS | Ubuntu 24.04 LTS |
| Hostname | `akh-prod` |
| Public IP | see `VPS_HOST` GitHub variable / Vultr console |
| Vultr login | `ahakhunts@gmail.com` |

**Access (SSH, key-only — passwords & root login disabled):**
```bash
ssh -i ~/.ssh/akh_vultr_deploy deploy@<VPS_IP>     # CI / automation key
ssh -i ~/.ssh/id_ed25519       deploy@<VPS_IP>     # personal key (also authorized)
```
`deploy` has passwordless sudo and is in the `docker` group.

**Hardening done by `deploy/provision.sh` (run once at setup):**
- non-root `deploy` user, key-only SSH, root login & password auth disabled
- 2 GB swap (cushions the 1 GB box), `vm.swappiness=10`
- `ufw`: only 22, 80, 443 inbound; `fail2ban` on SSH; `unattended-upgrades` (auto security patches, auto-reboot 04:00)
- Docker Engine + Compose plugin (official repo), container log caps (10 MB × 3)

---

## 3. The application stack (`/opt/akh/` on the VPS)

`docker compose -f /opt/akh/docker-compose.prod.yml` runs four containers (project name `akh`):

| Service | Image | Role | Exposed? |
|---|---|---|---|
| `caddy` | `caddy:2-alpine` | TLS edge :80/:443, reverse-proxy to app | **yes** (only internet-facing service) |
| `app` | `ghcr.io/art-ha666/web:<sha>` | php-fpm + nginx (:8080) + queue worker | no (internal :8080) |
| `mysql` | `mysql:8.4` | database, tuned for small box | no (internal network) |
| `redis` | `redis:7-alpine` | cache + session + queue, password-protected | no (internal network) |

- **Networks:** `edge` (caddy + app) and `backend` (app + mysql + redis, `internal: true` — DB/Redis have **no internet route** and are **not** published to the host).
- **Volumes (persistent):** `akh_mysql-data`, `akh_redis-data`, `akh_caddy-data`, `akh_caddy-config`, `akh_app-storage` (uploads).
- **MySQL tuning** (for 1 GB): `innodb-buffer-pool-size=256M`, `performance-schema=OFF`, `max-connections=50`.
- **Files on the box:** `/opt/akh/{docker-compose.prod.yml, Caddyfile, .env (chmod 600), backup-db.sh, backups/, certs/}`.

---

## 4. Repository files

| Path | Purpose |
|---|---|
| `docker/production/Dockerfile` | builds the app image (php 8.4-fpm + nginx + supervisor; assets baked in from CI) |
| `docker/production/docker-compose.prod.yml` | the production stack (copied to `/opt/akh/`) |
| `docker/production/Caddyfile` | TLS edge config (copied to `/opt/akh/`) |
| `docker/production/nginx.conf` | app's internal nginx (:8080); **note the FastCGI buffer settings — required, see §9** |
| `docker/production/{php.ini, php-fpm.conf, supervisord.conf, entrypoint.sh}` | app runtime config |
| `deploy/provision.sh` | one-time VPS hardening/bootstrap |
| `deploy/.env.production.example` | production env template |
| `deploy/backup-db.sh` | DB backup script (lives at `/opt/akh/backup-db.sh`) |
| `.github/workflows/ci.yml` | tests + lint + build on every push (PHP 8.4/8.5) |
| `.github/workflows/deploy-vultr.yml` | build image + deploy to VPS |

---

## 5. CI/CD pipeline

**Flow:** push to `main` → **CI** workflow (Pest tests, Pint, Vite build) → on success, **Deploy to Vultr**:
1. checkout the passing commit, build frontend assets (`npm run build`) + the Docker image
2. push image to GHCR as `:<sha>` and `:latest`
3. SSH to the VPS and run: **`backup-db.sh pre-deploy`** → `migrate --force` (one-off) → `compose up -d` (swap app) → prune old images
4. health-check `HEALTHCHECK_URL`; on failure, auto-rollback to the previous `:sha` (recorded in `/opt/akh/.last_good_image`)

Can also be run manually: **Actions → Deploy to Vultr → Run workflow** (optionally pass a previous SHA to roll back).

**GitHub config (Settings → Secrets and variables → Actions):**

| Type | Name | Value |
|---|---|---|
| Secret | `VPS_SSH_KEY` | private deploy key (`~/.ssh/akh_vultr_deploy`) |
| Variable | `VPS_HOST` | the VPS public IP |
| Variable | `VPS_USER` | `deploy` |
| Variable | `VPS_SSH_PORT` | `22` |
| Variable | `VPS_APP_DIR` | `/opt/akh` |
| Variable | `HEALTHCHECK_URL` | `https://akhsolutions.net/up` (update to the new domain) |

The VPS pulls the (private) GHCR image using the job's `GITHUB_TOKEN` — no extra PAT needed.

---

## 6. Database — persistence & backups

- **MySQL 8.4.10**, data on the persistent named volume `akh_mysql-data`. **Survives deploys and reboots**
  (deploys only recreate the `app` container; `restart: unless-stopped`). Verified: it stayed up across two deploys.
- **Nightly backup:** cron `30 3 * * *` runs `/opt/akh/backup-db.sh nightly` → gzipped dump in `/opt/akh/backups/`, kept 14 days.
- **Pre-deploy backup:** the deploy pipeline runs `backup-db.sh pre-deploy` before every migration; if it fails, the deploy aborts.
- **⚠ Still local-only.** Backups live on the same VPS disk, so they do **not** survive loss of the server.
  For real durability, configure off-box upload (set `RCLONE_REMOTE`, e.g. Cloudflare R2 / S3) — see §11.

**Restore from a backup:**
```bash
ssh deploy@<VPS_IP>
cd /opt/akh
gzip -dc backups/akh-YYYYMMDD-HHMMSS-LABEL.sql.gz | \
  docker compose -f docker-compose.prod.yml exec -T mysql \
  sh -c 'exec mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
```

---

## 7. TLS & Cloudflare

**Current:** Caddy serves an instant self-signed cert (`tls internal`); Cloudflare SSL/TLS mode should be **Full**.
Public visitors always get Cloudflare's valid edge certificate; the Cloudflare→origin hop is encrypted.

**Pending — the DNS cutover (the only thing left to go live):** see §10.

**Hardening upgrade (later) → Full (strict):**
1. Cloudflare → SSL/TLS → Origin Server → **Create Certificate** (covers `*.<domain>` + `<domain>`).
2. Save the cert as `/opt/akh/certs/origin.pem` and the key as `/opt/akh/certs/origin-key.pem` (chmod 600).
3. In `docker-compose.prod.yml`, uncomment the `./certs:/etc/caddy/certs:ro` mount.
4. In `Caddyfile`, replace `tls internal` with `tls /etc/caddy/certs/origin.pem /etc/caddy/certs/origin-key.pem`.
5. Redeploy; set Cloudflare SSL/TLS mode to **Full (strict)**.

---

## 8. Credentials — where they live (nothing secret is in git)

| Secret | Location |
|---|---|
| DB password, Redis password, `APP_KEY` | `/opt/akh/.env` on the VPS (chmod 600) — generated, never committed |
| SSH deploy key | local `~/.ssh/akh_vultr_deploy`; public half on the VPS; private half in GitHub secret `VPS_SSH_KEY` |
| Admin login (seeded default) | `admin@akhsolutions.com` / `password` — **change in production** (Admin → profile) |
| OpenAI / Gemini keys | optional, set in `/opt/akh/.env` or Admin → AI Writer |

---

## 9. Operations cheat-sheet

```bash
# SSH in
ssh -i ~/.ssh/akh_vultr_deploy deploy@<VPS_IP>
cd /opt/akh
C="docker compose -f docker-compose.prod.yml"

$C ps                      # container status / health
$C logs app --tail 100     # app (nginx + php-fpm + queue) logs
$C logs caddy --tail 50    # TLS edge logs
$C restart app             # restart just the app
$C exec -T app php artisan <cmd>   # run artisan IN the running container (no image pull needed)
bash backup-db.sh manual   # take a backup right now

# Deploy = just push to main (CI does the rest). Manual: GitHub Actions → "Deploy to Vultr" → Run workflow.
# Rollback: re-run "Deploy to Vultr" with a previous commit SHA, OR on the box:
#   APP_IMAGE=$(cat .last_good_image) $C up -d
```

**⚠ Gotchas (don't regress these):**
- **nginx FastCGI buffers** (`docker/production/nginx.conf`): the default 4 KB buffer overflows on full-page
  responses → nginx 502 "upstream sent too big header" on every page except `/up`. The 32 KB buffers fix it. Keep them.
- **Manual `docker compose run/pull` fails ("denied")** because the GHCR image is private and the box isn't logged in.
  Use `docker compose exec -T app ...` for manual artisan (runs in the live container, no pull). Or make the package public.
- **Never run `docker compose down -v`** — the `-v` deletes the data volumes.

---

## 10. ⏳ PENDING: go live (DNS cutover)

The app is fully built, deployed, and verified on the VPS (every page returns 200 over the origin IP).
It is **not yet public** because no domain points at it. The previous `akhsolutions.net` lived on a *different*
Cloudflare account; a **new domain** is being added to the account `4e629676797087ded621769a58256d2a`
(currently empty — "No domains found").

**When the new domain is added to Cloudflare and Active:**
1. (pre-wire) Repoint the app to the new domain: `Caddyfile` host block, `.env` `APP_URL` / `SESSION_DOMAIN` /
   `MAIL_FROM_ADDRESS`, the `HEALTHCHECK_URL` variable, and the site's SEO/canonical/sitemap. Redeploy.
2. DNS: add `A  <subdomain>  →  <VPS_IP>`, **Proxied** (orange cloud).
3. SSL/TLS → Overview → mode **Full**.
4. Verify: `curl https://<domain>/up` → 200 (served via Cloudflare).
5. Optional cleanup: retire the old `cloudflared-akh` tunnel on the dev machine once happy.

**Verify the origin before any DNS change (no downtime):**
```bash
curl -k --resolve <domain>:443:<VPS_IP> https://<domain>/up    # expect 200
```

---

## 11. TODO / next session

- [ ] Add the new domain to Cloudflare; pre-wire the app to it; do the DNS cutover (§10).
- [ ] **Off-box backups** for true durability (set `RCLONE_REMOTE` to Cloudflare R2 / S3 in `backup-db.sh` + cron). Currently local-only.
- [ ] Upgrade TLS to **Full (strict)** with a Cloudflare Origin Certificate (§7).
- [ ] Change the seeded admin password; set real SMTP (`MAIL_*`) so contact-form mail sends.
- [ ] Regenerate blog articles via the AI writer (production `articles=0`).
- [ ] (optional) Consider the 2 GB plan if the 1 GB box feels tight under real traffic.
- [ ] (cosmetic) Remove the `script_stop` input from `deploy-vultr.yml` to silence a harmless Actions warning.
```
