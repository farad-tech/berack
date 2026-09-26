(() => {
    const url = new URL(window.location.href);
    if (url.searchParams.has('timezone')) return;
    try {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (timezone && timezone !== 'UTC') {
            url.searchParams.set('timezone', timezone);
            window.location.replace(url);
        }
    } catch {
        // The server-rendered report remains usable in UTC.
    }
})();
