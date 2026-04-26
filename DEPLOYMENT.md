# RestoPOS — Production Deployment (Laravel Forge)

This guide describes deploying RestoPOS to a Forge-managed VPS. Steps for Ploi
or any other provider are nearly identical — only the UI differs.

---

## 1. Prerequisites

- A VPS running Ubuntu 22.04+ provisioned by Forge (PHP 8.3, Nginx, MySQL 8 or
  PostgreSQL 16, Redis 7, Node 20).
- Domain `pos.forris.uz` (and `*.pos.forris.uz` wildcard) pointed at the
  server's IP.
- Wildcard SSL certificate from Let's Encrypt (Forge → SSL → LE → enable
  wildcard via DNS-01).
- Forge connected to GitHub with read access to `Ilyosxon2721/RestoPos`.

---

## 2. Create the Forge site

1. Sites → Add Site
   - Domain: `pos.forris.uz`
   - Aliases: `*.pos.forris.uz`
   - Project type: General PHP / Laravel
   - PHP: 8.3
2. Git Repository
   - Repo: `Ilyosxon2721/RestoPos`
   - Branch: `main`
   - Enable "Install Composer Dependencies": **off** (deploy script handles it)

---

## 3. Configure environment

1. Copy `.env.production.example` from this repo into the Forge → Environment
   editor. Fill in every blank value:
   - `APP_KEY` — generate via `php artisan key:generate --show` and paste.
   - DB credentials (Forge gives you these when creating the database).
   - Redis password if you set one.
   - SMTP / SES / Postmark.
   - PlayMobile credentials.
   - Payme / Click merchant credentials.
   - `SENTRY_LARAVEL_DSN` from your Sentry project.
   - `HORIZON_ALLOWED_EMAILS` — comma-separated emails who may visit
     `/horizon`.
   - `CORS_ALLOWED_ORIGINS` and `CORS_ALLOWED_ORIGINS_PATTERNS` for any
     external client apps.

2. Run `php artisan key:generate` once if you didn't paste a key.

---

## 4. Deploy script

Replace Forge's default deploy script with the contents of
[`scripts/deploy.sh`](scripts/deploy.sh). It does:

- `git pull`
- `composer install --no-dev --optimize-autoloader`
- `npm ci && npm run build` (Vite manifest)
- `php artisan migrate --force`
- `config:cache`, `route:cache`, `view:cache`, `event:cache`
- `queue:restart`, `horizon:terminate`
- Graceful PHP-FPM reload via `flock`

Push the deploy button once to bootstrap.

---

## 5. Workers (Horizon)

1. Forge → Daemons → New Daemon
   - Command: `php artisan horizon`
   - User: `forge`
   - Directory: site path
   - Start on boot: yes
2. Horizon's web UI is at `/horizon`. Access is gated by
   `config/horizon.php` → `allowed_emails`. Set `HORIZON_ALLOWED_EMAILS` in
   the env to a comma-separated list of admin emails.

---

## 6. Scheduler

Forge → Scheduler → Add. Cron entry:

```
* * * * * php /home/forge/<site>/artisan schedule:run >> /dev/null 2>&1
```

This drives:
- `backup:clean` (01:00 UTC) — remove old backups.
- `backup:run` (01:30 UTC) — full DB + filesystem backup.
- `backup:monitor` (02:00 UTC) — alert if something is missing.

---

## 7. Backups

`spatie/laravel-backup` is installed. Configure `config/backup.php` for the
target disk (S3 recommended). Add a `backup` disk in `config/filesystems.php`
pointing at a private S3 bucket and reference it from `backup.backup.destination.disks`.

Test with `php artisan backup:run --only-db` from SSH.

---

## 8. Health check

`GET /health` returns a JSON status:

```json
{ "status": "ok", "checks": { "database": {"ok": true}, "redis": {"ok": true} } }
```

Wire this into Forge's monitoring or external uptime services (UptimeRobot,
Better Stack). It bypasses the tenant resolver, so it works on the apex domain.

---

## 9. CI

`.github/workflows/ci.yml` runs Pint, PHPStan, and Pest on every push and PR
to `main` / `develop`. Set the workflow as a required check in branch
protection rules.

---

## 10. Smoke test after first deploy

```sh
ssh forge@<server>
cd /home/forge/pos.forris.uz
php artisan about            # confirm env, drivers, queue
php artisan migrate:status   # nothing pending
curl -sf https://pos.forris.uz/health | jq
php artisan horizon:status   # active
```

---

## Rollback

```sh
git -C /home/forge/pos.forris.uz log --oneline -10
git -C /home/forge/pos.forris.uz checkout <previous-sha>
php artisan migrate:rollback # only if migrations run last deploy
php artisan config:cache && php artisan queue:restart
```

For quicker rollback enable Forge's "Quick Deploy" with a tag-based release
strategy; this README does not assume a release manager.

---

## Common issues

| Symptom | Likely cause | Fix |
|---|---|---|
| 502 from Nginx | PHP-FPM didn't reload | `sudo service php8.3-fpm restart` |
| Queue not draining | Horizon daemon died | Forge → Daemons → restart |
| `SQLSTATE[42000]` after deploy | Pending migration | rerun deploy or `php artisan migrate --force` manually |
| Sessions wiped | `APP_KEY` rotated | restore old key or accept logout-all |
| CORS blocked browser request | Wrong origin | add domain to `CORS_ALLOWED_ORIGINS` |
| Login attempts return 429 | Rate limiter (`auth`: 5/min/IP) | expected, ask user to wait |
