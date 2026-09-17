export function observePageChanges(callback)
{
    let lastKnownUrl = window.location.href;

    function handleUrlChange()
    {
        const currentUrl = window.location.href;

        if(currentUrl === lastKnownUrl)
        {
            return;
        }

        lastKnownUrl = currentUrl;
        callback();
    }

    window.addEventListener('popstate', handleUrlChange);
    window.addEventListener('hashchange', handleUrlChange);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            lastKnownUrl = window.location.href;
            callback();
        }
    });

    const originalPushState = window.history.pushState;
    const originalReplaceState = window.history.replaceState;

    window.history.pushState = function (...args)
    {
        const result = Reflect.apply(originalPushState, this, args);

        handleUrlChange();

        return result;
    };

    window.history.replaceState = function (...args)
    {
        const result = Reflect.apply(originalReplaceState, this, args);

        handleUrlChange();

        return result;
    };
}
