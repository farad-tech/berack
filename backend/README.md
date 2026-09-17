# Berack Backend

Laravel application for Berack: receiving page view events, managing sites, issuing API keys, and exploring visit journeys in a standalone Blade user panel. Filament is reserved for `/admin`.

Website: [https://berack.ir](https://berack.ir)

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

The user panel is available at:

```text
http://127.0.0.1:8000/panel
```

Registration is enabled for local/self-hosted use. Create an account, add a site, then copy the SDK snippet from the site detail page.

The product admin panel is available at:

```text
http://127.0.0.1:8000/admin
```

Promote an existing user to admin:

```bash
php artisan user:promote-admin you@example.com
```

All pages, authentication messages, and both panels use English regardless of session language preferences. Legacy `/en` and `/en/guide` URLs redirect to `/` and `/guide`. The user panel uses local assets and needs no frontend build. Configure Laravel mail to deliver password reset links.

The public SDK loader is served from:

```text
https://berack.ir/sdk/{api_key}.js
```

## Tests

```bash
php artisan test
```
