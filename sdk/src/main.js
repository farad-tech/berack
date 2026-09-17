import { createTrackerConfigFromScript } from './modules/config';
import { createPageViewEvent } from './modules/page-view';
import { getOrCreateAnonymousId } from './modules/anonymous-id';
import { createVisitSession } from './modules/session';
import { observePageChanges } from './modules/navigation-observer';
import { enqueueEvent, initializeQueue } from './modules/event-queue';
import { sendQueuedEventsWithBeacon } from './modules/beacon-sender';
import { sendQueuedEvents } from './modules/event-sender';

const config = createTrackerConfigFromScript();
const installed = window.__berackInstalled ||= new Set();

if (config.apiKey && !installed.has(config.apiKey)) {
    installed.add(config.apiKey);
    const session = createVisitSession(config.apiKey);
    const initialize = () => session.initialize().then((tabId) => initializeQueue(`${config.apiKey}:${tabId}`));
    let ready = initialize();

    async function track()
    {
        const event = createPageViewEvent({});
        await ready;
        const context = session.next(event.url, Date.parse(event.timestamp));
        Object.assign(event, {
            anonymous_id: getOrCreateAnonymousId(),
            session_id: context.sessionId,
            tab_id: context.tabId,
            sequence: context.sequence,
            previous_url: context.previousUrl,
        });
        enqueueEvent(event);
        if (config.debug) console.debug('[berack] page_view', event);
        void sendQueuedEvents(config.endpoint, config.apiKey);
    }

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) ready = initialize();
    });
    observePageChanges(track);
    void track();

    window.addEventListener('pagehide', () => {
        sendQueuedEventsWithBeacon(config.endpoint, config.apiKey);
        session.release();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') sendQueuedEventsWithBeacon(config.endpoint, config.apiKey);
    });
    setInterval(() => { void sendQueuedEvents(config.endpoint, config.apiKey); }, config.flushInterval);
}
