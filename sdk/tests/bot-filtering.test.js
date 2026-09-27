import test from 'node:test';
import assert from 'node:assert/strict';
import { randomUUID } from 'node:crypto';
import { runInNewContext } from 'node:vm';
import { fileURLToPath } from 'node:url';
import { build } from 'vite';
import { isAutomatedBrowser } from '../src/modules/bot-detector.js';

const bundle = await build({
    configFile: fileURLToPath(new URL('../vite.config.js', import.meta.url)),
    build: { write: false },
    logLevel: 'silent',
});
const code = [bundle].flat().flatMap((result) => result.output).find((file) => file.type === 'chunk').code;

async function runSdk(navigator)
{
    const calls = { storage: 0, listeners: 0, timers: 0, requests: [] };
    const storage = {
        getItem() { calls.storage++; return null; },
        setItem() { calls.storage++; },
    };
    const addEventListener = () => { calls.listeners++; };
    const window = {
        location: new URL('https://example.test/pricing'),
        history: { pushState() {}, replaceState() {} },
        addEventListener,
    };
    runInNewContext(code, {
        window, navigator, TextEncoder,
        document: {
            currentScript: { dataset: { apiKey: 'site-key', endpoint: '/save-tracker' } },
            title: 'Pricing', referrer: '', addEventListener,
        },
        sessionStorage: storage, localStorage: storage,
        crypto: { randomUUID },
        setInterval() { calls.timers++; },
        async fetch(url, options) {
            calls.requests.push({ url, body: JSON.parse(options.body) });
            return { ok: true };
        },
    });
    await new Promise(setImmediate);
    return calls;
}

test('normal and unknown browsers are not classified as automated', () => {
    for (const navigator of [{}, { webdriver: false }, { userAgent: 'Chrome/130.0.0.0' }, { userAgent: 'Android; CUBOT X70' }]) {
        assert.equal(isAutomatedBrowser(navigator), false);
    }
});

test('automated SDK runs do not access storage, attach listeners, or send events', async () => {
    for (const navigator of [
        { webdriver: true, userAgent: 'Chrome/130.0.0.0' },
        { userAgent: 'HeadlessChrome/130.0.0.0' },
        { userAgent: 'PhantomJS/2.1.1' },
        { userAgentData: { brands: [{ brand: 'HeadlessChrome', version: '130' }] } },
    ]) {
        assert.deepEqual(await runSdk(navigator), { storage: 0, listeners: 0, timers: 0, requests: [] });
    }
});

test('normal SDK runs still initialize and send their first page view', async () => {
    const calls = await runSdk({ webdriver: false, userAgent: 'Chrome/130.0.0.0' });
    assert.ok(calls.storage > 0);
    assert.ok(calls.listeners > 0);
    assert.equal(calls.timers, 1);
    assert.equal(calls.requests.length, 1);
    assert.equal(calls.requests[0].url, '/save-tracker');
    assert.equal(calls.requests[0].body.api_key, 'site-key');
    assert.equal(calls.requests[0].body.events[0].event_name, 'page_view');
    assert.equal(calls.requests[0].body.events[0].path, '/pricing');
});
