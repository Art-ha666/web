#!/usr/bin/env bash
#
# cf-firewall.sh - Restrict the Docker-published web ports (80/443) to Cloudflare
# source IPs only, so the origin can't be reached directly (bypassing Cloudflare's
# WAF / DDoS / bot protection) even if the origin IP is discovered.
#
# WHY DOCKER-USER (not ufw): Docker inserts its own iptables rules in the FORWARD
# path for published container ports, which BYPASS ufw's INPUT chain. The
# DOCKER-USER chain is the supported hook for filtering that traffic.
#
# Idempotent (rules tagged with the comment CF-LOCK). Re-run any time; a systemd
# unit re-applies it on every boot (rules are not persistent across reboots).
#
set -euo pipefail

CHAIN=DOCKER-USER
MARK=CF-LOCK

# Remove any rules we previously added (idempotent re-run).
for ipt in iptables ip6tables; do
  while $ipt -L "$CHAIN" --line-numbers -n 2>/dev/null | grep -q "$MARK"; do
    n=$($ipt -L "$CHAIN" --line-numbers -n | awk -v m="$MARK" '$0 ~ m {print $1; exit}')
    $ipt -D "$CHAIN" "$n" 2>/dev/null || break
  done
done

# Insert order matters: -I puts a rule at the TOP, so insert in REVERSE of the
# desired evaluation order. Final order (top->bottom):
#   1) ESTABLISHED,RELATED -> RETURN  (don't break ongoing connections)
#   2) <Cloudflare ranges> -> RETURN  (allow Cloudflare)
#   3) any -> DROP                    (block everyone else on 80/443)

# 3) catch-all DROP for 80/443
iptables  -I "$CHAIN" -p tcp -m multiport --dports 80,443 -m comment --comment "$MARK" -j DROP
ip6tables -I "$CHAIN" -p tcp -m multiport --dports 80,443 -m comment --comment "$MARK" -j DROP 2>/dev/null || true

# 2) allow Cloudflare source ranges (inserted above the DROP)
for cidr in $(curl -fsS https://www.cloudflare.com/ips-v4); do
  iptables -I "$CHAIN" -p tcp -m multiport --dports 80,443 -s "$cidr" -m comment --comment "$MARK" -j RETURN
done
for cidr in $(curl -fsS https://www.cloudflare.com/ips-v6); do
  ip6tables -I "$CHAIN" -p tcp -m multiport --dports 80,443 -s "$cidr" -m comment --comment "$MARK" -j RETURN 2>/dev/null || true
done

# 1) allow already-established connections (top priority)
iptables  -I "$CHAIN" -p tcp -m multiport --dports 80,443 -m conntrack --ctstate ESTABLISHED,RELATED -m comment --comment "$MARK" -j RETURN
ip6tables -I "$CHAIN" -p tcp -m multiport --dports 80,443 -m conntrack --ctstate ESTABLISHED,RELATED -m comment --comment "$MARK" -j RETURN 2>/dev/null || true

echo "[cf-firewall] origin web ports locked to Cloudflare IPs ($(iptables -S "$CHAIN" | grep -c "$MARK") v4 rules)"
