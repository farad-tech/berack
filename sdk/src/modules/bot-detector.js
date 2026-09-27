export function isAutomatedBrowser(navigator = globalThis.navigator)
{
    return navigator?.webdriver === true
        || /HeadlessChrome|PhantomJS/i.test(navigator?.userAgent || '')
        || (navigator?.userAgentData?.brands || []).some(({ brand }) => /HeadlessChrome/i.test(brand));
}
