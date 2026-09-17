export function createPageViewEvent(eventContext)
{
    return {
        anonymous_id: eventContext.anonymousId,
        session_id: eventContext.sessionId,
        tab_id: eventContext.tabId,
        sequence: eventContext.sequence,
        previous_url: eventContext.previousUrl,
        event_id: crypto.randomUUID(),
        event_name: 'page_view',
        url: window.location.href,
        path: window.location.pathname,
        title: document.title.slice(0, 255),
        referrer: document.referrer,
        timestamp: (new Date()).toISOString(),
        sdk_version: '1.0.0',
    }
}
