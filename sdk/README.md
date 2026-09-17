# Berack SDK

Browser SDK used by Berack to record independent visit journeys and send their page views to Laravel. It only tracks navigation, not clicks, scrolling, device details, or session replay.

Visits expire after 30 minutes without recorded navigation. Each tab has independent ordered steps; a browser identifier is not a verified person. See [journey semantics](../README.md#journey-semantics) for delivery and browser limitations. Use HTTPS for browser tab coordination through Web Locks.

Website: [https://berack.ir](https://berack.ir)

For normal use, install the generated script from the backend panel:

```html
<script async src="https://berack.ir/sdk/{api_key}.js"></script>
```

The source in this folder is useful for local SDK development:

```bash
npm install
npm run dev
npm run build
```

Run the SDK regression tests with `npm test`.

`npm run build` writes the browser SDK inside this Vite project:

```text
sdk/dist/berack.js
```

To publish a version for the Laravel backend, copy the built file manually to the versioned public path:

```bash
mkdir -p ../backend/public/berack/v1.0
cp dist/berack.js ../backend/public/berack/v1.0/berack.min.js
```

Laravel serves a small site-specific loader from `/sdk/{api_key}.js`. That loader passes `data-api-key` and `data-endpoint` to the built SDK file, so the tracking logic has a single source of truth in `sdk/src`.
