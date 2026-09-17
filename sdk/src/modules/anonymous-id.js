const ANONYMOUS_ID_STORAGE_KEY = 'tracker_anonymous_id';
let memoryId;

export function getOrCreateAnonymousId()
{
    if (memoryId) return memoryId;
    let anonymousId;
    
    try {
        anonymousId = localStorage.getItem(ANONYMOUS_ID_STORAGE_KEY);
    } catch {
        anonymousId = crypto.randomUUID();
        return memoryId = anonymousId;
    }
    if(anonymousId)
    {
        return memoryId = anonymousId;
    }

    anonymousId = crypto.randomUUID();
    try {
        localStorage.setItem(ANONYMOUS_ID_STORAGE_KEY, anonymousId);
    } catch {
        return memoryId = anonymousId;
    }

    return memoryId = anonymousId;

}
