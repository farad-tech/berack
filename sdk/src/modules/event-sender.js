import { getQueuedEvents, getQueueSize, removeQueuedEvents } from "./event-queue.js";

let isSending = false;

export async function sendQueuedEvents(endpoint, apiKey)
{
    if(isSending)
    {
        return false;
    }

    if(getQueueSize() === 0)
    {
        return true;
    }

    const batch = getQueuedEvents();

    isSending = true;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                api_key: apiKey,
                events: batch,
            })

        });

        if(response.ok)
        {
            removeQueuedEvents(batch);
            return true;

        }
        return false;
        
    } catch {
        return false;

    } finally {
        isSending = false;
    }

}
