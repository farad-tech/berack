<?php

namespace Tests\Feature;

use App\Models\TrackerEvent;
use App\Models\User;
use App\Services\JourneyReport;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class StandalonePanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_uses_laravel_auth_and_cannot_grant_admin_access(): void
    {
        $this->post('/panel/register', ['name' => 'Reader', 'email' => 'READER@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123', 'is_admin' => true])
            ->assertRedirect('/panel/sites/create');
        $user = User::where('email', 'reader@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->get('/admin')->assertForbidden();
    }

    public function test_login_logout_and_guest_protection(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->get('/panel/sites')->assertRedirect('/panel/login');
        $this->post('/panel/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/panel/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/panel/sites');
        $this->assertAuthenticatedAs($user);
        $this->post('/panel/logout')->assertRedirect('/panel/login');
        $this->assertGuest();
    }

    public function test_password_reset_uses_the_new_panel_routes(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/panel/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $this->get('/panel/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk();
        $this->post('/panel/reset-password', ['token' => $token, 'email' => $user->email,
            'password' => 'updated-pass123', 'password_confirmation' => 'updated-pass123'])->assertRedirect('/panel/login');
        $this->assertTrue(Hash::check('updated-pass123', $user->fresh()->password));
    }

    public function test_installation_navigation_handles_owned_sites_and_admin_visibility(): void
    {
        $this->get('/panel/installation')->assertRedirect('/panel/login');
        $user = User::factory()->create(['is_admin' => false]);
        $foreignSite = User::factory()->create()->sites()->create(['name' => 'Private website', 'domain' => 'private.test']);
        $this->actingAs($user)->get('/panel/sites')->assertOk()
            ->assertSee('href="'.route('panel.installation').'"', false)->assertDontSee('Administration');
        $this->get('/panel/installation')->assertRedirect('/panel/sites/create');
        $first = $user->sites()->create(['name' => 'Docs', 'domain' => 'docs.test']);
        $this->get('/panel/installation')->assertRedirect(route('panel.sites.show', $first));
        $second = $user->sites()->create(['name' => 'Shop', 'domain' => 'shop.test']);
        $this->get('/panel/installation')->assertOk()->assertSee('<h1>Installation</h1>', false)
            ->assertSee('href="'.route('panel.sites.show', $first).'"', false)
            ->assertSee('href="'.route('panel.sites.show', $second).'"', false)
            ->assertDontSee($foreignSite->name);
        $this->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/panel/sites')
            ->assertOk()->assertSee('Administration');
    }

    public function test_site_lifecycle_preserves_ownership_and_generates_tag(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/panel/sites', ['name' => 'Docs', 'domain' => 'https://docs.test/install', 'user_id' => 999])
            ->assertRedirect();
        $site = $user->sites()->firstOrFail();
        $this->assertSame('docs.test', $site->domain);
        $this->get('/panel/sites/'.$site->id)->assertOk()->assertSee($site->api_key)->assertSee('data-copy', false)->assertDontSee('livewire.js');
        $this->put('/panel/sites/'.$site->id, ['name' => 'New name', 'domain' => 'new.test'])->assertRedirect();
        $this->assertSame('New name', $site->fresh()->name);
        $this->actingAs(User::factory()->create())->get('/panel/sites/'.$site->id)->assertNotFound();
        $this->put('/panel/sites/'.$site->id, ['name' => 'Stolen', 'domain' => 'stolen.test'])->assertNotFound();
        $this->delete('/panel/sites/'.$site->id)->assertNotFound();
        $this->actingAs($user)->delete('/panel/sites/'.$site->id)->assertRedirect('/panel/sites');
        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    public function test_page_search_preserves_complete_journeys_and_date_status_filters(): void
    {
        $user = User::factory()->create();
        $site = $user->sites()->create(['name' => 'Docs', 'domain' => 'docs.test']);
        foreach (['/entry', '/needle', '/last'] as $index => $path) {
            TrackerEvent::create(['site_id' => $site->id, 'event_id' => 'step-'.$index, 'event_name' => 'page_view',
                'anonymous_id' => 'browser', 'session_id' => 'visit', 'tab_id' => 'tab', 'sequence' => $index + 1,
                'path' => $path, 'url' => 'https://docs.test'.$path, 'occurred_at' => now()->subHours(2)->addMinutes($index), 'payload' => []]);
        }
        $report = new JourneyReport($site);
        $row = $report->filteredJourneys(['q' => 'needle'])->first();
        $this->assertSame(3, (int) $row->steps);
        $this->assertSame('/entry', $row->first_path);
        $this->assertSame('/last', $row->last_path);
        $this->assertSame(0, $report->filteredJourneys(['status' => 'pending'])->get()->count());
        $this->assertSame(1, $report->filteredJourneys(['status' => 'ended', 'range' => '1'])->get()->count());
        $this->actingAs($user)->withSession(['locale' => 'en'])->get('/panel/sites/'.$site->id.'/reports?q=needle')
            ->assertOk()->assertSee('/entry')->assertSee('/last')->assertSee('Journey details')->assertDontSee('livewire.js');
        $this->get('/panel/sites/'.$site->id.'/reports?view=last-pages')->assertOk()->assertSee('/last');
        $this->get('/panel/sites/'.$site->id.'/reports?q=missing')->assertOk()->assertSee('No visits match');
    }

    public function test_panel_is_english_and_language_switch_is_removed(): void
    {
        $this->withSession(['locale' => 'fa'])->get('/')
            ->assertOk()->assertSee('lang="en" dir="ltr"', false)->assertDontSee('فارسی');
        $this->get('/panel/login')->assertOk()->assertSee('lang="en" dir="ltr"', false)
            ->assertSee('Sign in')->assertDontSee('panel/locale');
        $this->post('/panel/locale', ['locale' => 'fa'])->assertNotFound();
        $this->post('/panel/register', [])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertStringContainsString('required', session('errors')->first('email'));
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
    }

    public function test_return_markers_and_sequence_gaps_survive_step_pagination(): void
    {
        $user = User::factory()->create();
        $site = $user->sites()->create(['name' => 'Long visit', 'domain' => 'long.test']);
        for ($sequence = 1; $sequence <= 52; $sequence++) {
            if ($sequence === 51) {
                continue;
            }
            $path = $sequence === 52 ? '/step-1' : '/step-'.$sequence;
            $event = TrackerEvent::create(['site_id' => $site->id, 'event_id' => 'long-'.$sequence, 'event_name' => 'page_view',
                'anonymous_id' => 'browser', 'session_id' => 'long', 'tab_id' => 'tab', 'sequence' => $sequence,
                'url' => 'https://long.test'.$path, 'path' => $path, 'occurred_at' => now()->subMinutes(10)->addSeconds($sequence), 'payload' => []]);
        }
        $this->actingAs($user)->withSession(['locale' => 'en'])
            ->get('/panel/sites/'.$site->id.'/reports?journeyId='.$event->id.'&stepsPage=2')
            ->assertOk()->assertSee('Revisited page')->assertSee('Missing recorded steps')->assertSee('/step-1');
    }
}
