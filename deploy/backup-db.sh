#!/usr/bin/env bash
#
# backup-db.sh - Dump the AKH Solutions MySQL database to a rotated, gzipped
# file on the host disk (NOT inside the mysql data volume, so a volume wipe
# can't take the backups with it).
#
# Lives on the VPS at /opt/akh/backup-db.sh. Used two ways:
#   - nightly cron:        bash /opt/akh/backup-db.sh nightly
#   - pre-deploy snapshot: bash /opt/akh/backup-db.sh pre-deploy   (called by CI before migrate)
#
# Optional off-box copy: if RCLONE_REMOTE is set (e.g. "r2:akh-backups") and
# rclone is installed/configured, each dump is also uploaded there. Without it,
# backups are LOCAL ONLY and do not survive loss of the VPS - configure off-box
# storage for real durability.
#
set -euo pipefail

cd "$(dirname "$0")"                 # /opt/akh (compose file + .env live here)
COMPOSE="docker compose -f docker-compose.prod.yml"
BACKUP_DIR="${BACKUP_DIR:-/opt/akh/backups}"
RETAIN_DAYS="${RETAIN_DAYS:-14}"
LABEL="${1:-manual}"

mkdir -p "$BACKUP_DIR"
TS="$(date +%Y%m%d-%H%M%S)"
OUT="$BACKUP_DIR/akh-${TS}-${LABEL}.sql.gz"

# Consistent InnoDB dump without table locks. Credentials come from the mysql
# container's own env, so they never appear in this script or the process list.
# --no-tablespaces avoids needing the global PROCESS privilege (app user is DB-scoped).
$COMPOSE exec -T mysql sh -c \
  'exec mysqldump --single-transaction --quick --no-tablespaces --routines --triggers -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  | gzip -6 > "$OUT"

# Refuse to keep a truncated/empty dump.
if [ "$(gzip -dc "$OUT" 2>/dev/null | head -c 200 | wc -c)" -lt 50 ]; then
  echo "ERROR: backup looks empty, removing: $OUT" >&2
  rm -f "$OUT"
  exit 1
fi

SIZE="$(du -h "$OUT" | cut -f1)"
echo "[backup-db] OK: $OUT ($SIZE)"

# Off-box copy (optional, only if configured).
if [ -n "${RCLONE_REMOTE:-}" ] && command -v rclone >/dev/null 2>&1; then
  rclone copy "$OUT" "$RCLONE_REMOTE" && echo "[backup-db] uploaded to $RCLONE_REMOTE"
fi

# Rotate local copies.
find "$BACKUP_DIR" -name 'akh-*.sql.gz' -type f -mtime +"$RETAIN_DAYS" -delete
echo "[backup-db] retained last ${RETAIN_DAYS} days in $BACKUP_DIR"
