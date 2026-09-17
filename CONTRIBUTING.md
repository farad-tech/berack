# Contributing

Thanks for considering a contribution to Berack.

## Development Setup

Backend:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

SDK:

```bash
cd sdk
npm install
npm run dev
```

## Checks

Run these before opening a pull request:

```bash
cd backend
php artisan test

cd ../sdk
npm run build
```

## Pull Requests

Keep changes focused, include tests for backend behavior, and update the README when user-facing setup or behavior changes.
