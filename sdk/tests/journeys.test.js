import test from 'node:test';
import assert from 'node:assert/strict';
import { randomUUID } from 'node:crypto';
import { createVisitSession, INACTIVITY_MS } from '../src/modules/session.js';
import { observePageChanges } from '../src/modules/navigation-observer.js';

function environment(data = new Map(), locks = new Set())
{
    return {
        crypto: { randomUUID },
        sessionStorage: { getItem: (key) => data.get(key) ?? null, setItem: (key, value) => data.set(key, value) },
        navigator: { locks: { async request(key, options, callback) {
            if (locks.has(key)) return callback(null);
            locks.add(key);
            try { await callback({ name: key }); } finally { locks.delete(key); }
        } } },
    };
}

test('navigation order, revisiting pages, and inactivity boundaries', async () => {
    const session = createVisitSession('site', environment());
    await session.initialize();
    const a = session.next('/a', 1000);
    const b = session.next('/b', 2000);
    const back = session.next('/a', 3000);
    assert.equal(b.previousUrl, '/a');
    assert.equal(back.previousUrl, '/b');
    assert.equal(back.sequence, 3);
    assert.equal(back.sessionId, a.sessionId);
    const fresh = session.next('/next', 3000 + INACTIVITY_MS);
    assert.notEqual(fresh.sessionId, a.sessionId);
    assert.equal(fresh.sequence, 1);
    assert.equal(fresh.previousUrl, null);
    assert.equal(fresh.tabId, a.tabId);
    session.release();
});

test('reload continues the visit, next-day return does not', async () => {
    const env = environment();
    const first = createVisitSession('site', env);
    await first.initialize();
    const a = first.next('/a', 1000);
    first.release();
    await new Promise(setImmediate);
    const reload = createVisitSession('site', env);
    await reload.initialize();
    const b = reload.next('/b', 2000);
    assert.equal(a.tabId, b.tabId);
    assert.equal(a.sessionId, b.sessionId);
    assert.equal(b.sequence, 2);
    assert.notEqual(reload.next('/tomorrow', 86400000).sessionId, b.sessionId);
    reload.release();
});

test('duplicated sessionStorage cannot merge two live tabs', async () => {
    const storage = new Map();
    const locks = new Set();
    const first = createVisitSession('site', environment(storage, locks));
    await first.initialize();
    const a = first.next('/a');
    const duplicate = createVisitSession('site', environment(new Map(storage), locks));
    await duplicate.initialize();
    const b = duplicate.next('/b');
    assert.notEqual(a.tabId, b.tabId);
    assert.notEqual(a.sessionId, b.sessionId);
    assert.equal(b.previousUrl, null);
    assert.equal(b.sequence, 1);
    first.release();
    duplicate.release();
});

test('blocked storage retains in-memory visit state and site keys remain isolated', async () => {
    const env = environment();
    env.sessionStorage = { getItem() { throw Error('blocked'); }, setItem() { throw Error('blocked'); } };
    const first = createVisitSession('site-a', env);
    const second = createVisitSession('site-b', env);
    await first.initialize();
    await second.initialize();
    const a = first.next('/a');
    assert.equal(first.next('/b').sessionId, a.sessionId);
    assert.notEqual(second.next('/a').sessionId, a.sessionId);
    first.release();
    second.release();
});

test('without Web Locks copied storage starts a separate document visit', async () => {
    const env = environment();
    env.navigator = {};
    const first = createVisitSession('site', env);
    await first.initialize();
    const a = first.next('/a');
    const second = createVisitSession('site', env);
    await second.initialize();
    assert.notEqual(second.next('/b').tabId, a.tabId);
});

test('SPA, popstate, hash, and BFCache restores retain actual navigation steps', () => {
    const win = new EventTarget();
    win.location = { href: 'https://example.test/a' };
    win.history = {
        pushState(_state, _title, url) { win.location.href = url; },
        replaceState(_state, _title, url) { win.location.href = url; },
    };
    globalThis.window = win;
    const paths = [];
    observePageChanges(() => paths.push(win.location.href));
    win.history.pushState({}, '', 'https://example.test/b');
    win.history.replaceState({}, '', 'https://example.test/b');
    win.location.href = 'https://example.test/a';
    win.dispatchEvent(new Event('popstate'));
    win.location.href += '#part';
    win.dispatchEvent(new Event('popstate'));
    win.dispatchEvent(new Event('hashchange'));
    const restored = new Event('pageshow');
    restored.persisted = true;
    win.dispatchEvent(restored);
    assert.deepEqual(paths, ['https://example.test/b', 'https://example.test/a', 'https://example.test/a#part', 'https://example.test/a#part']);
    delete globalThis.window;
});
