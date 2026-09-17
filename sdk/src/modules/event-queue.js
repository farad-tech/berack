let eventQueue = [];
let storageKey;

function persist()
{
    try { sessionStorage.setItem(storageKey, JSON.stringify(eventQueue)); } catch {}
}

export function initializeQueue(scope)
{
    storageKey = `berack:queue:${scope}`;
    try {
        const stored = JSON.parse(sessionStorage.getItem(storageKey));
        eventQueue = Array.isArray(stored) ? stored : [];
    } catch { eventQueue = []; }
}

export function enqueueEvent(event) { eventQueue.push(event); persist(); }
export function getQueuedEvents()
{
    const batch = [];
    let bytes = 0;
    for (const event of eventQueue.slice(0, 20)) {
        const size = new TextEncoder().encode(JSON.stringify(event)).length;
        if (batch.length && bytes + size > 48000) break;
        batch.push(event);
        bytes += size;
    }
    return batch;
}
export function getQueueSize() { return eventQueue.length; }
export function clearQueue() { eventQueue = []; persist(); }

export function removeQueuedEvents(batch)
{
    const ids = new Set(batch.map((event) => event.event_id));
    eventQueue = eventQueue.filter((event) => !ids.has(event.event_id));
    persist();
}
