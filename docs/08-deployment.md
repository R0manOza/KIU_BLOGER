# 8. Deployment (Docker & Render)

[← Previous: API](07-api.md) · [Back to index](README.md) · [Next: Exam Prep →](09-exam-prep-qa.md)

This chapter explains how the app goes from code on your laptop to a live website. It's beyond the core
rubric, but it's a great thing to show and explain.

## 8.1 The big picture

```
Local code ──git push──▶ GitHub ──auto-deploy──▶ Render builds Docker image ──▶ Live website
                                                          │
                                                          └── connects to ──▶ Render PostgreSQL
```

- We host on **Render** (a cloud platform).
- The app is packaged with **Docker** so it runs identically everywhere.
- Data lives in a managed **PostgreSQL** database, not the local SQLite file.

## 8.2 Why Docker?

Render has no built-in PHP runtime, so we ship a **Dockerfile** — a recipe that builds a self-contained
image with PHP, Apache, the required extensions, and our app. Key parts of `Dockerfile`:

```dockerfile
FROM php:8.4-apache                          # PHP 8.4 + Apache web server

RUN docker-php-ext-install pdo pdo_pgsql ... # install Postgres + other PHP extensions
COPY --from=composer:2 /usr/bin/composer ... # bring in Composer

COPY composer.json composer.lock ./
RUN composer install --no-dev ...            # install PHP dependencies
COPY . .                                     # copy the app code

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public  # serve Laravel's public/ folder
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
```

> **Why PHP 8.4?** Our `composer.lock` was generated on PHP 8.5, which pinned Symfony packages that
> require PHP ≥ 8.4.1. The first deploy used PHP 8.3 and the build failed — bumping the base image to
> 8.4 fixed it. (A real debugging story you can tell!)

### The entrypoint script

`docker/entrypoint.sh` runs each time the container starts:

```bash
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf   # bind to Render's port

php artisan migrate --force      # apply migrations to the Postgres DB
php artisan storage:link         # link uploaded files
php artisan db:seed --force      # seed demo data (idempotent — safe to re-run)
php artisan config:cache         # cache config/routes/views for speed
exec apache2-foreground          # start the web server
```

This means the database schema and demo content are set up **automatically** on first deploy.

## 8.3 The Render Blueprint (`render.yaml`)

Rather than clicking through the dashboard, the whole infrastructure is declared in `render.yaml` (this
is "infrastructure as code"):

```yaml
databases:
  - name: kiu-blogger-db          # a managed PostgreSQL database
    plan: free

services:
  - type: web
    name: kiu-blogger
    runtime: docker               # build from our Dockerfile
    healthCheckPath: /up          # Render pings this to confirm the app is alive
    envVars:
      - key: APP_KEY              # set manually (Laravel encryption key)
        sync: false
      - key: DB_HOST              # pulled automatically from the database above
        fromDatabase: { name: kiu-blogger-db, property: host }
      # ...DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD the same way...
      - key: DB_CONNECTION
        value: pgsql
```

The `fromDatabase` entries automatically wire the web service to the database credentials — we never
copy-paste passwords.

## 8.4 Environment configuration: dev vs prod

The app reads settings from environment variables (the `.env` file locally, Render's dashboard in
production). The same code runs in both places; only the config differs:

| Setting | Local (dev) | Render (prod) |
|---------|-------------|---------------|
| `DB_CONNECTION` | `sqlite` | `pgsql` |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_KEY` | generated locally | set in dashboard |

This is the **"Database configuration"** part of the rubric — the connection is environment-driven, so
switching from SQLite to Postgres needs **zero code changes**, only config.

## 8.5 The HTTPS-behind-a-proxy fix

Render terminates HTTPS at its load balancer and forwards plain HTTP to our container. Without telling
Laravel, it generated `http://` form URLs and browsers warned "this form is not secure". Two fixes:

```php
// app/Providers/AppServiceProvider.php — force HTTPS in production
if ($this->app->environment('production')) {
    URL::forceScheme('https');
}
```
```php
// bootstrap/app.php — trust the proxy's forwarded headers
$middleware->trustProxies(at: '*');
```

## 8.6 Known limitation (be honest about this)

Render's free filesystem is **ephemeral** — it resets on every restart/redeploy/spin-down. So:

- **Database content (posts, comments, users) is safe** — it's in PostgreSQL.
- **Uploaded images are not permanent** — they vanish on redeploy. The fix would be object storage
  (e.g. Cloudflare R2 / S3) by setting `FILESYSTEM_DISK=s3`; the code already supports it via the
  storage-disk abstraction, so it's a config change, not a rewrite.

Knowing and being able to articulate this limitation is itself a sign of understanding the platform.

---

[← Previous: API](07-api.md) · [Back to index](README.md) · [Next: Exam Prep →](09-exam-prep-qa.md)
