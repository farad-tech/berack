export const INACTIVITY_MS = 30 * 60 * 1000;

// A lock distinguishes duplicated sessionStorage from a continuation in one tab.
export function createVisitSession(apiKey, environment = globalThis)
{
    const key = `berack:visit:${apiKey}`;
    let state;
    let release;

    function resetTab() { state = { tabId: environment.crypto.randomUUID() }; }
    function persist() {
        try { environment.sessionStorage.setItem(key, JSON.stringify(state)); } catch {}
    }

    async function initialize()
    {
        try { state = JSON.parse(environment.sessionStorage.getItem(key)); } catch {}
        if (!state?.tabId) resetTab();
        if (!environment.navigator.locks) {
            // Without coordination, prefer a separate visit to a false merge.
            resetTab();
        } else {
            const claim = () => new Promise((resolve, reject) => {
                environment.navigator.locks.request(`berack:${apiKey}:${state.tabId}`, { ifAvailable: true }, async (lock) => {
                    if (!lock) return resolve(false);
                    const held = new Promise((done) => { release = done; });
                    resolve(true);
                    await held;
                }).catch(reject);
            });
            try {
                if (!await claim()) {
                    resetTab();
                    await claim();
                }
            } catch { resetTab(); }
        }
        persist();
        return state.tabId;
    }

    function next(url, timestamp = Date.now())
    {
        if (!state.sessionId || timestamp - state.lastActivity >= INACTIVITY_MS || timestamp < state.lastActivity) {
            state.sessionId = environment.crypto.randomUUID();
            state.sequence = 0;
            state.previousUrl = null;
        }
        const context = {
            tabId: state.tabId,
            sessionId: state.sessionId,
            sequence: ++state.sequence,
            previousUrl: state.previousUrl,
        };
        state.lastActivity = timestamp;
        state.previousUrl = url;
        persist();
        return context;
    }

    return { initialize, next, release: () => { release?.(); release = undefined; } };
}
