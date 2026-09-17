const DEFAULT_CONFIG = {
    endpoint: 'https://berack.ir/save-tracker',
    apiKey: null,
    flushInterval: 5000,
    maxBatchSize: 10,
    debug: false,
};

export function createTrackerConfig(options = {})
{
    return {
        ...DEFAULT_CONFIG,
        ...options,
    };
}

export function createTrackerConfigFromScript()
{
    const currentScript = document.currentScript;
    const globalConfig = window.Berack || {};

    return createTrackerConfig({
        endpoint: currentScript?.dataset.endpoint || globalConfig.endpoint || DEFAULT_CONFIG.endpoint,
        apiKey: currentScript?.dataset.apiKey || globalConfig.apiKey,
        debug: currentScript?.dataset.debug === 'true' || globalConfig.debug === true,
    });
}
