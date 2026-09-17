<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\TrackerEvent;
use App\Models\User;
use App\Services\JourneyReport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyReportTest extends TestCase
{
    use RefreshDatabase;

    private function site(): Site
    {
        return Site::create(['user_id' => User::factory()->create()->id, 'name' => 'Docs', 'domain' => 'docs.test']);
    }

    private function event(Site $site, int $sequence, array $attributes = []): TrackerEvent
    {
        return TrackerEvent::create(array_merge([
            'site_id' => $site->id, 'event_id' => fake()->uuid(), 'event_name' => 'page_view',
            'anonymous_id' => 'browser', 'session_id' => 'visit', 'tab_id' => 'tab',
            'sequence' => $sequence, 'path' => '/step-'.$sequence, 'url' => 'https://docs.test/step-'.$sequence,
            'occurred_at' => now()->subHour()->addSeconds($sequence), 'payload' => [],
        ], $attributes));
    }

    public function test_journeys_are_scoped_and_ordered_by_sequence_not_arrival(): void
    {
        $site = $this->site();
        $last = $this->event($site, 3);
        $first = $this->event($site, 1);
        $this->event($site, 2);
        $this->event($site, 1, ['tab_id' => 'second-tab']);
        $this->event($site, 1, ['session_id' => 'tomorrow']);
        $this->event($site, 1, ['tab_id' => null]);
        $foreign = $this->event($this->site(), 1);
        $report = new JourneyReport($site);
        $this->assertSame(3, $report->journeys()->total());
        $this->assertSame([1, 2, 3], $report->journey($last->id)->orderBy('sequence')->pluck('sequence')->all());
        $this->assertSame($first->id, $report->journey($last->id)->orderBy('sequence')->first()->id);
        $this->expectException(ModelNotFoundException::class);
        $report->journey($foreign->id);
    }

    public function test_only_last_pages_of_ended_visits_are_counted(): void
    {
        $site = $this->site();
        $this->event($site, 3, ['path' => '/last']);
        $this->event($site, 1, ['path' => '/first']);
        $this->event($site, 1, ['session_id' => 'open', 'path' => '/active', 'occurred_at' => now()]);
        $this->event($site, 1, ['session_id' => 'boundary', 'path' => '/boundary', 'occurred_at' => now()->subMinutes(30)]);
        $this->event($site, 1, ['tab_id' => null, 'path' => '/legacy']);
        $this->assertEqualsCanonicalizing([
            ['path' => '/last', 'visits' => 1], ['path' => '/boundary', 'visits' => 1],
        ], (new JourneyReport($site))->lastPages());
    }

    public function test_duplicate_delivery_is_immutable_and_event_ids_are_site_scoped(): void
    {
        $site = $this->site();
        $other = $this->site();
        $event = ['event_id' => 'same-id', 'event_name' => 'page_view', 'tab_id' => 'tab',
            'anonymous_id' => 'browser', 'session_id' => 'visit', 'sequence' => 1, 'path' => '/first'];
        $this->postJson('/save-tracker', ['api_key' => $site->api_key, 'events' => [$event]])->assertOk();
        $event['path'] = '/changed';
        $this->postJson('/save-tracker', ['api_key' => $site->api_key, 'events' => [$event]])->assertOk();
        $this->postJson('/save-tracker', ['api_key' => $other->api_key, 'events' => [$event]])->assertOk();
        $this->assertDatabaseCount('tracker_events', 2);
        $this->assertDatabaseHas('tracker_events', ['site_id' => $site->id, 'path' => '/first']);
        $this->assertDatabaseHas('tracker_events', ['site_id' => $other->id, 'path' => '/changed']);
    }

    public function test_invalid_sequence_and_custom_events_are_rejected(): void
    {
        $site = $this->site();
        $this->postJson('/save-tracker', ['api_key' => $site->api_key, 'events' => [[
            'event_id' => 'bad', 'event_name' => 'click', 'sequence' => -1,
        ]]])->assertUnprocessable();
        $this->postJson('/save-tracker', ['api_key' => $site->api_key, 'events' => [[
            'event_id' => 'incomplete', 'event_name' => 'page_view', 'tab_id' => 'tab',
        ]]])->assertUnprocessable();
    }

    public function test_journey_selection_cannot_read_another_site(): void
    {
        $site = $this->site();
        $event = $this->event($site, 1);
        $foreign = $this->event($this->site(), 1);
        $this->actingAs($site->user);
        $this->withSession(['locale' => 'en'])
            ->get('/panel/sites/'.$site->id.'/reports?journeyId='.$event->id)
            ->assertOk()->assertSee('https://docs.test/step-1')->assertSee('Last recorded page');
        $this->get('/panel/sites/'.$site->id.'/reports?journeyId='.$foreign->id)->assertNotFound();
    }
}
