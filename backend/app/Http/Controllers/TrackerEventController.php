<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\TrackerEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class TrackerEventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'api_key' => ['required', 'string', 'exists:sites,api_key'],
            'events' => ['required', 'array', 'max:100'],
            'events.*.event_id' => ['required', 'string', 'max:100'],
            'events.*.event_name' => ['required', 'in:page_view'],
            'events.*.anonymous_id' => ['required_with:events.*.tab_id', 'nullable', 'string', 'max:100'],
            'events.*.session_id' => ['required_with:events.*.tab_id', 'nullable', 'string', 'max:100'],
            'events.*.tab_id' => ['nullable', 'string', 'max:100'],
            'events.*.sequence' => ['required_with:events.*.tab_id', 'nullable', 'integer', 'min:1', 'max:4294967295'],
            'events.*.url' => ['nullable', 'string', 'max:2048'],
            'events.*.path' => ['nullable', 'string', 'max:2048'],
            'events.*.title' => ['nullable', 'string', 'max:255'],
            'events.*.previous_url' => ['nullable', 'string', 'max:2048'],
            'events.*.referrer' => ['nullable', 'string', 'max:2048'],
            'events.*.timestamp' => ['nullable', 'date'],
        ]);

        $site = Site::where('api_key', $request->input('api_key'))->firstOrFail();
        $events = $request->input('events', []);
        $savedCount = 0;

        foreach ($events as $event) {
            // Retries never rewrite history, and event IDs are scoped to a site.
            TrackerEvent::firstOrCreate(
                ['site_id' => $site->id, 'event_id' => $event['event_id']],
                [
                    'site_id' => $site->id,
                    'event_name' => $event['event_name'],
                    'anonymous_id' => $event['anonymous_id'] ?? null,
                    'session_id' => $event['session_id'] ?? null,
                    'tab_id' => $event['tab_id'] ?? null,
                    'sequence' => $event['sequence'] ?? null,
                    'url' => $event['url'] ?? null,
                    'path' => $event['path'] ?? null,
                    'title' => $event['title'] ?? null,
                    'previous_url' => $event['previous_url'] ?? null,
                    'referrer' => $event['referrer'] ?? null,
                    'occurred_at' => isset($event['timestamp']) ? Carbon::parse($event['timestamp'])->utc() : now(),
                    'payload' => $event,
                ],
            );

            $savedCount++;
        }

        return response()
            ->json([
                'ok' => true,
                'saved' => $savedCount,
            ])
            ->withHeaders($this->corsHeaders());
    }

    public function preflight(): Response
    {
        return response('', 204)->withHeaders($this->corsHeaders());
    }

    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With',
        ];
    }
}
