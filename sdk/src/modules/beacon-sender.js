import { getQueuedEvents, getQueueSize } from "./event-queue.js";

export function sendQueuedEventsWithBeacon(endpoint, apiKey)
{

    if(getQueueSize() === 0)
    {
        return true;
    }

    const batch = getQueuedEvents();

    const body = JSON.stringify({
        api_key: apiKey,
        events: batch,
    });

    const blob = new Blob([body], {
        type: 'application/json',
    });
    
    let sendBeaconResult;
    try {
        if(typeof navigator.sendBeacon === 'function')
        {
            sendBeaconResult = navigator.sendBeacon(endpoint, blob);
        } else {
            sendBeaconResult = false;
        }
    } catch {
        sendBeaconResult = false;
    }

    // Beacon acceptance is not a server acknowledgement; retry idempotently later.

    return sendBeaconResult;
}
