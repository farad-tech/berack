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
}
