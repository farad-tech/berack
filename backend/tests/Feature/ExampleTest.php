<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('lang="en" dir="ltr"', false)
            ->assertSee('Create free account')
            ->assertSee('Berack')
            ->assertSee('/panel/register')
            ->assertSee('/guide')
            ->assertSee('vazirmatn')
            ->assertSee('https://github.com/farad-tech/berack')
            ->assertSee('journey-canvas')
            ->assertSee('Last recorded', false)
            ->assertSee('contact[at]berack.com');
    }

    public function test_the_english_landing_page_returns_a_successful_response(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(301)->assertRedirect('/');
    }

    public function test_the_guide_page_is_english_despite_a_persian_session(): void
    {
        $response = $this->withSession(['locale' => 'fa'])->get('/guide');

        $response
            ->assertStatus(200)
            ->assertSee('lang="en" dir="ltr"', false)
            ->assertSee('Berack getting started guide')
            ->assertSee('vazirmatn')
            ->assertSee('trk_your_site_api_key')
            ->assertSee('https://github.com/farad-tech/berack')
            ->assertSee('role="tablist"', false)
            ->assertSee('id="copy-tag"', false)
            ->assertSee('Ended by inactivity')
            ->assertSee('End undetermined')
            ->assertSee('local timezone')
            ->assertSee('mailto:contact@berack.com')
            ->assertDontSee('<script async src=', false);

        foreach (['account', 'site', 'tag', 'verify', 'analytics', 'troubleshooting'] as $section) {
            $response->assertSee('href="#'.$section.'"', false)
                ->assertSee('id="'.$section.'"', false);
        }
    }

    public function test_the_english_guide_page_returns_a_successful_response(): void
    {
        $response = $this->get('/en/guide');

        $response->assertStatus(301)->assertRedirect('/guide');
    }

    public function test_sitemap_contains_only_public_canonical_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.url('/').'</loc>', false)
            ->assertSee('<loc>'.url('/guide').'</loc>', false)
            ->assertDontSee('/panel')
            ->assertDontSee('/admin')
            ->assertDontSee('/login')
            ->assertDontSee('/register')
            ->assertDontSee('/en');
    }

    public function test_robots_points_to_sitemap_and_excludes_private_routes(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: '.url('/sitemap.xml'))
            ->assertSee('Disallow: /panel')
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /sdk/');
    }
}
