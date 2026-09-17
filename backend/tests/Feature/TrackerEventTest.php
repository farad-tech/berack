<?php

namespace Tests\Feature;

use App\Models\TrackerEvent;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackerEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_tracker_events(): void
    {
        $user = User::factory()->create();
        $site = Site::create([
            'user_id' => $user->id,
            'name' => 'Docs',
            'domain' => 'docs.test',
        ]);

        $response = $this->postJson('/save-tracker', [
            'api_key' => $site->api_key,
            'events' => [
                [
                    'event_id' => 'event-test-1',
                    'event_name' => 'page_view',
                    'anonymous_id' => 'anonymous-test-1',
                    'session_id' => 'session-test-1',
                    'sequence' => 1,
                    'url' => 'http://localhost:5173/pricing',
                    'path' => '/pricing',
                    'title' => 'Pricing',
                    'previous_url' => null,
                    'referrer' => '',
                    'timestamp' => '2026-09-11T12:00:00.000Z',
                    'viewport' => [
                        'width' => 1280,
                        'height' => 720,
                    ],
                ],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'saved' => 1,
            ]);

        $this->assertDatabaseHas('tracker_events', [
            'site_id' => $site->id,
            'event_id' => 'event-test-1',
            'event_name' => 'page_view',
            'anonymous_id' => 'anonymous-test-1',
            'session_id' => 'session-test-1',
            'path' => '/pricing',
        ]);

        $event = TrackerEvent::where('event_id', 'event-test-1')->firstOrFail();

        $this->assertSame(1280, $event->payload['viewport']['width']);
    }

    public function test_it_serves_site_specific_sdk_script(): void
    {
        $user = User::factory()->create();
        $site = Site::create([
            'user_id' => $user->id,
            'name' => 'Marketing',
            'domain' => 'marketing.test',
        ]);

        $response = $this->get("/sdk/{$site->api_key}.js");

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee($site->api_key, false)
            ->assertSee('/save-tracker', false)
            ->assertSee('berack\\/v1.0\\/berack.min.js', false);
    }

    public function test_tracker_endpoint_responds_to_cors_preflight(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://example.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type',
        ])->options('/save-tracker');

        $response
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods');
    }

    public function test_tracker_validation_errors_include_cors_headers(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://example.com',
        ])->postJson('/save-tracker', []);

        $response
            ->assertStatus(422)
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
