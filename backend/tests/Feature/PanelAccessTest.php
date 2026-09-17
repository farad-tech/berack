<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\TrackerEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_register_through_the_app_panel(): void
    {
        $this->get('/panel/register')
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertDontSee('livewire.js');
    }

    public function test_regular_users_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_users_can_access_the_admin_panel(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_user_dashboard_links_to_site_journeys(): void
    {
        $user = User::factory()->create();
        $site = Site::create([
            'user_id' => $user->id,
            'name' => 'Docs',
            'domain' => 'docs.test',
        ]);

        TrackerEvent::create([
            'site_id' => $site->id,
            'event_id' => 'dashboard-event-1',
            'event_name' => 'page_view',
            'anonymous_id' => 'visitor-1',
            'session_id' => 'session-1',
            'sequence' => 1,
            'url' => 'https://docs.test/install',
            'path' => '/install',
            'title' => 'Install',
            'referrer' => 'https://search.test',
            'occurred_at' => now(),
            'payload' => [
                'language' => 'en-US',
                'sdk_version' => '0.1.0',
                'viewport' => ['width' => 1440, 'height' => 900],
                'screen' => ['width' => 1440, 'height' => 900],
            ],
        ]);

        $this->actingAs($user)
            ->get('/panel')
            ->assertRedirect('/panel/sites/'.$site->id.'/reports');
    }

    public function test_user_can_view_reports_for_each_owned_site(): void
    {
        $user = User::factory()->create();
        $site = Site::create([
            'user_id' => $user->id,
            'name' => 'Docs',
            'domain' => 'docs.test',
        ]);

        TrackerEvent::create([
            'site_id' => $site->id,
            'event_id' => 'site-report-event-1',
            'tab_id' => 'tab-1',
            'event_name' => 'page_view',
            'anonymous_id' => 'visitor-1',
            'session_id' => 'session-1',
            'sequence' => 1,
            'url' => 'https://docs.test/install',
            'path' => '/install',
            'title' => 'Install',
            'referrer' => 'https://search.test',
            'occurred_at' => now(),
            'payload' => [],
        ]);

        $this->actingAs($user)
            ->get("/panel/sites/{$site->id}/reports?journeyId=".TrackerEvent::where('event_id', 'site-report-event-1')->value('id'))
            ->assertOk()
            ->assertSee('Docs')
            ->assertSee('/install')
            ->assertSee('https://search.test');
    }

    public function test_user_cannot_view_reports_for_another_users_site(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::create([
            'user_id' => $otherUser->id,
            'name' => 'Private Docs',
            'domain' => 'private.test',
        ]);

        $this->actingAs($user)
            ->get("/panel/sites/{$site->id}/reports")
            ->assertNotFound();
    }
}
