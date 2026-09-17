# Berack

Berack records website visit journeys: entry page, ordered navigation steps, and last recorded page. Built with Laravel, Filament, and a lightweight JavaScript SDK.

Website: [https://berack.ir](https://berack.ir)

Users can register, add their websites, receive a site-specific API key and SDK script, then explore visits in a standalone Laravel Blade panel. Filament is used only for administration.

## Features

- Independent Laravel authentication, registration, and password recovery for users.
- Site management per user.
- Automatic API key generation per site.
- Generated SDK URL and install snippet per site.
- Page view collection from a browser SDK.
- Laravel endpoint for batched event ingestion.
- Per-user analytics isolation in the panel.
- Per-site visit journeys with independent tab histories and last-page reports.
- MySQL-friendly local setup.

## Repository Structure

```text
berack/
  backend/   Laravel + Filament backend and analytics panel
  sdk/       Browser SDK source for local development
```

## Requirements

- PHP 8.3+
- Composer 2+
- Node.js 20.19+ or 22.12+
- npm

## Quick Start

Clone the repository and install the backend:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Open the user panel:

```text
http://127.0.0.1:8000/panel
```

Register a user, create a site, then copy the SDK snippet from the site detail page.

The product admin panel is separate:

```text
http://127.0.0.1:8000/admin
```

To grant admin access to an existing registered user:

```bash
php artisan user:promote-admin you@example.com
```

All interfaces are English-only: the landing page, guide, user panel, and Filament admin. Existing session language preferences are ignored. Legacy `/en` and `/en/guide` URLs redirect to `/` and `/guide`. The user panel retains its local light/dark preference.

## User Panel

The `/panel` workspace is built with Blade, native Laravel authentication, and local CSS/JavaScript assets. No Filament or Livewire UI is loaded for users. Existing accounts and SDK tags continue to work.

- `/panel/login` and `/panel/register`: login and registration.
- `/panel/forgot-password`: password recovery (configure Laravel mail for delivery).
- `/panel/sites`: website management; `/panel/sites/{id}`: settings and copyable installation tag.
- `/panel/sites/{id}/reports`: visit explorer with page/browser search, date/status filters, ordered timelines, and last-page reports.
- `/admin`: the separate Filament administrator panel.

Panel assets are served from `backend/public/css/panel.css` and `backend/public/js/panel.js`; they require no Node build and use the locally hosted Vazirmatn font.

## SDK Usage

After creating a site in the panel, Berack gives you a snippet like:

```html
<script async src="https://berack.ir/sdk/{api_key}.js"></script>
```

Add it to every page of the tracked website. The SDK records page views, SPA navigation changes, browser/visit/tab IDs, sequence numbers, previous URLs, referrers, and timestamps. It does not collect screen, viewport, language, or user-agent data.

## Journey Semantics

- A browser ID is not a verified person. Storage deletion and browser changes produce different IDs.
- Each tab has an independent visit. After 30 minutes without recorded navigation, the next navigation starts a new visit and resets its sequence and previous URL.
- No heartbeat or interaction tracking is used. Someone reading a page for 30 minutes can still be present when a visit is considered ended.
- Back/forward, SPA URL changes, reloads, and back-forward cache restores are recorded. Steps are ordered by client sequence, not arrival time.
- Web Locks prevent duplicated tabs from reusing copied visit state. On unsupported browsers or unavailable storage, continuity can be reduced; without Web Locks each document load starts a separate visit.
- Last recorded page is an observation, not proof of closing a tab or a reason for abandonment. Browser/network restrictions can leave missing steps. Sequence gaps are kept visible.
- Pending events are kept in session storage when available and retried. Beacon acceptance is not treated as acknowledgement. Server deduplication is per site and event ID.
- Old events without tab IDs are retained, but excluded from journey reconstruction because their tab boundaries are unknown.

### Upgrade

Run `php artisan migrate` in `backend`, then `npm run build` in `sdk`. Publish the built SDK manually using the steps in [sdk/README.md](sdk/README.md). Deploy the migration before the SDK. Existing public assets are not overwritten by the build.

## Local SDK Development

```bash
cd sdk
npm install
npm run dev
```

For local SDK development, set `data-api-key` and `data-endpoint` on the SDK script tag using a real site key from the backend panel.

## Testing

To populate a registered account with sample journeys, run this from `backend`:

```bash
BERACK_DEMO_USER_ID=2 php artisan db:seed --class=DemoJourneySeeder
```

Use your account's ID; omitting `BERACK_DEMO_USER_ID` selects the first registered account. This creates two clearly named demo sites with 48 visits and 176 page views, including returns, separate tabs, SPA URLs, and recent/ended visits. Rerunning refreshes the same demo events without duplicating them or changing real events.

Backend:

```bash
cd backend
php artisan test
```

SDK:

```bash
cd sdk
npm test
npm run build
```

The SDK source lives in `sdk/src`. The Laravel route `/sdk/{api_key}.js` serves a small loader, and the shared browser SDK is built inside `sdk/dist` before being published to the backend public directory.

The published browser SDK file is versioned under:

```text
backend/public/berack/v1.0/berack.min.js
```

## Scope

Berack focuses on visit journeys and last recorded pages. It is not a session-replay, heatmap, custom-event, or general analytics platform.

## License

Berack is open-sourced software licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE).
