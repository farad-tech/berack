<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\TrackerEvent;
use App\Models\User;
use App\Services\SiteTrends;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteTrendsTest extends TestCase
{
    use RefreshDatabase;

    private function event(Site $site, string $at, string $session, int $sequence = 1, string $tab = 'tab'): TrackerEvent
    {
        return TrackerEvent::create([
            'site_id' => $site->id, 'event_id' => (string) Str::uuid(), 'event_name' => 'page_view',
            'anonymous_id' => 'browser', 'session_id' => $session, 'tab_id' => $tab,
            'sequence' => $sequence, 'path' => '/', 'url' => 'https://'.$site->domain.'/',
            'occurred_at' => $at, 'payload' => [],
        ]);
    }

    public function test_counts_are_isolated_and_a_visit_crossing_midnight_is_not_counted_twice(): void
    {
        $user = User::factory()->create();
        $site = $user->sites()->create(['name' => 'Shop', 'domain' => 'shop.test']);
        $other = $user->sites()->create(['name' => 'Docs', 'domain' => 'docs.test']);
        $this->event($site, '2026-09-24 20:29:59', 'crossing');
        $this->event($site, '2026-09-24 20:30:00', 'crossing', 2);
        $this->event($site, '2026-09-25 09:00:00', 'new');
        $this->event($site, '2026-09-25 09:01:00', 'new', 2);
        $this->event($site, '2026-09-25 09:00:00', 'new', 1, 'second-tab');
        $this->event($site, '2026-09-25 20:30:00', 'tomorrow');
        $this->event($other, '2026-09-25 09:00:00', 'other-site');
        $this->event($site, '2026-09-25 09:00:00', 'legacy')->update(['tab_id' => null]);
        $report = (new SiteTrends($site))->report(['period' => 'day', 'date' => '2026-09-25', 'timezone' => 'Asia/Tehran']);
        $this->assertCount(24, $report['buckets']);
        $this->assertSame(['visits' => 2, 'steps' => 4], $report['totals']);
        $this->assertSame(['visits' => 1, 'steps' => 1], $report['previous']);
        $this->assertSame(100.0, $report['changes']['visits']);
        $this->assertSame(1, $report['buckets'][0]['steps']);
        $this->assertSame(0, $report['buckets'][0]['visits']);
        $this->actingAs($user)->get('/panel/sites/'.$site->id.'/trends?period=day&date=2026-09-25&timezone=Asia%2FTehran')
            ->assertOk()->assertSee('Asia/Tehran')->assertSee('Period breakdown');
    }

    public function test_empty_periods_leap_year_and_dst_have_correct_buckets(): void
    {
        $site = User::factory()->create()->sites()->create(['name' => 'Shop', 'domain' => 'shop.test']);
        $service = new SiteTrends($site);
        foreach ([['day', '2026-03-08', 23], ['day', '2026-11-01', 25], ['week', '2026-09-25', 7], ['month', '2024-02-15', 29], ['year', '2026-09-25', 12]] as [$period, $date, $count]) {
            $report = $service->report(['period' => $period, 'date' => $date, 'timezone' => 'America/New_York']);
            $this->assertCount($count, $report['buckets']);
            $this->assertSame(['visits' => 0, 'steps' => 0], $report['totals']);
            $this->assertNull($report['changes']['visits']);
            $this->assertSame($count, count(array_unique(array_column($report['buckets'], 'start'))));
        }
        $this->event($site, '2026-11-01 05:30:00', 'before-fallback');
        $this->event($site, '2026-11-01 06:30:00', 'after-fallback');
        $report = $service->report(['period' => 'day', 'date' => '2026-11-01', 'timezone' => 'America/New_York']);
        $this->assertSame(1, $report['buckets'][1]['visits']);
        $this->assertSame(1, $report['buckets'][2]['visits']);
        $this->assertNotSame($report['buckets'][1]['full_label'], $report['buckets'][2]['full_label']);
    }

    public function test_reports_validate_inputs_and_require_site_ownership(): void
    {
        $owner = User::factory()->create();
        $site = $owner->sites()->create(['name' => 'Shop', 'domain' => 'shop.test']);
        $url = '/panel/sites/'.$site->id;
        $this->get($url.'/trends')->assertRedirect('/panel/login');
        $this->actingAs(User::factory()->create())->get($url.'/trends')->assertNotFound();
        $this->get($url.'/reset')->assertNotFound();
        $this->delete($url.'/events', [])->assertNotFound();
        $this->actingAs($owner)->getJson($url.'/trends?timezone=Invalid&date=2026-02-31&period=all&metric=invalid')
            ->assertUnprocessable()->assertJsonValidationErrors(['timezone', 'date', 'period', 'metric']);
        foreach (['day', 'week', 'month', 'year'] as $period) {
            $this->get($url.'/trends?period='.$period)->assertOk()->assertSee('No visits started in this period.');
        }
    }

    public function test_reset_requires_confirmation_and_preserves_site_key_and_other_sites(): void
    {
        $owner = User::factory()->create(['password' => 'test-password']);
        $site = $owner->sites()->create(['name' => 'Shop', 'domain' => 'shop.test']);
        $other = $owner->sites()->create(['name' => 'Docs', 'domain' => 'docs.test']);
        $event = $this->event($site, '2026-09-25 09:00:00', 'visit');
        $otherEvent = $this->event($other, '2026-09-25 09:00:00', 'other');
        $key = $site->api_key;
        $url = '/panel/sites/'.$site->id;
        $this->actingAs($owner)->get($url.'/reset')->assertOk()->assertSee('Permanently reset data');
        $this->delete($url.'/events', ['domain' => 'wrong.test', 'password' => 'test-password'])->assertSessionHasErrors('domain');
        $this->delete($url.'/events', ['domain' => 'shop.test', 'password' => 'wrong-password'])->assertSessionHasErrors('password');
        $this->assertModelExists($event);
        $this->delete($url.'/events', ['domain' => 'shop.test', 'password' => 'test-password'])->assertRedirect($url.'/trends');
        $this->assertModelMissing($event);
        $this->assertModelExists($otherEvent);
        $this->assertSame($key, $site->fresh()->api_key);
        $this->assertSame(['visits' => 0, 'steps' => 0], (new SiteTrends($site))->report(['period' => 'year', 'date' => '2026-01-01'])['totals']);
        $this->assertModelExists($this->event($site, '2026-09-26 09:00:00', 'after-reset'));
    }
}
