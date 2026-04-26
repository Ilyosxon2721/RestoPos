#!/usr/bin/env bash
# ============================================================
# RestoPOS — Forge deploy script
# ------------------------------------------------------------
# Paste the body of this script (without the shebang) into
# Forge → Site → Deploy Script. The variables Forge injects
# ($FORGE_SITE_BRANCH, $FORGE_PHP) are referenced below.
# ============================================================

set -euo pipefail

cd "$FORGE_SITE_PATH"

git pull origin "$FORGE_SITE_BRANCH"

# PHP dependencies (production only, optimized autoloader)
"$FORGE_COMPOSER" install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev

# Frontend assets
if [[ -f package-lock.json ]]; then
    npm ci
    npm run build
fi

# Run migrations (idempotent, exits 0 if no pending migrations)
"$FORGE_PHP" artisan migrate --force

# Refresh caches (route/config/view) — must run AFTER pulling new code
"$FORGE_PHP" artisan config:cache
"$FORGE_PHP" artisan route:cache
"$FORGE_PHP" artisan view:cache
"$FORGE_PHP" artisan event:cache

# Storage symlink (only first deploy creates it; subsequent runs no-op)
"$FORGE_PHP" artisan storage:link || true

# Restart workers so they pick up new code
"$FORGE_PHP" artisan queue:restart
"$FORGE_PHP" artisan horizon:terminate || true

# Reload PHP-FPM (Forge default is graceful; no downtime)
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'
    sudo -S service "$FORGE_PHP_FPM" reload
) 9>/tmp/fpmlock

echo "✅ Deploy complete: $(git log -1 --pretty=format:'%h %s')"
