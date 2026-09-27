<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BotFilteringTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

    private function payload(): array
    {
        $site = Site::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Docs',
            'domain' => 'docs.test',
        ]);

        return [
            'api_key' => $site->api_key,
            'events' => [
                ['event_id' => 'first', 'event_name' => 'page_view', 'path' => '/'],
                ['event_id' => 'second', 'event_name' => 'page_view', 'path' => '/pricing'],
            ],
        ];
    }

    public static function bots(): array
    {
        return [
            'google' => [['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']],
            'bing' => [['User-Agent' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)']],
            'ai crawler' => [['User-Agent' => 'GPTBot/1.2']],
            'preview' => [['User-Agent' => 'facebookexternalhit/1.1']],
            'seo' => [['User-Agent' => 'AhrefsBot/7.0']],
            'headless' => [['User-Agent' => str_replace('Chrome/', 'HeadlessChrome/', self::BROWSER)]],
            'client hints' => [['User-Agent' => self::BROWSER, 'Sec-CH-UA' => '"HeadlessChrome";v="130"']],
            'from' => [['User-Agent' => self::BROWSER, 'From' => 'googlebot(at)googlebot.com']],
            'alternate agent' => [['User-Agent' => self::BROWSER, 'X-Original-User-Agent' => 'bingbot/2.0']],
        ];
    }

    #[DataProvider('bots')]
    public function test_bot_batches_are_acknowledged_without_storing_events(array $headers): void
    {
        $this->withHeaders(['Origin' => 'https://docs.test', ...$headers])
            ->postJson('/save-tracker', $this->payload())
            ->assertOk()
            ->assertExactJson(['ok' => true, 'saved' => 0, 'ignored' => true, 'reason' => 'bot'])
            ->assertHeader('Access-Control-Allow-Origin', '*');

        $this->assertDatabaseCount('tracker_events', 0);
    }

    public static function browsers(): array
    {
        return [
            'desktop' => [self::BROWSER],
            'iphone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1'],
            'cubot device' => ['Mozilla/5.0 (Linux; Android 13; CUBOT X70) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36'],
            'unknown' => ['CustomBrowser/1.0'],
            'empty' => [''],
        ];
    }

    #[DataProvider('browsers')]
    public function test_normal_browsers_are_still_recorded(string $agent): void
    {
        $this->withHeader('User-Agent', $agent)->postJson('/save-tracker', $this->payload())
            ->assertOk()->assertExactJson(['ok' => true, 'saved' => 2]);

        $this->assertDatabaseCount('tracker_events', 2);
    }

    public function test_bot_detection_does_not_bypass_validation(): void
    {
        $payload = $this->payload();
        $payload['api_key'] = 'invalid';
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1', 'Origin' => 'https://docs.test'])
            ->postJson('/save-tracker', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('api_key')
            ->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertDatabaseCount('tracker_events', 0);
    }

    public function test_bot_preflight_is_not_blocked(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Googlebot/2.1',
            'Origin' => 'https://docs.test',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type',
        ])->options('/save-tracker')->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function test_detection_does_not_leak_between_requests_or_delete_history(): void
    {
        $payload = $this->payload();
        $this->withHeader('User-Agent', self::BROWSER)->postJson('/save-tracker', $payload)
            ->assertJsonPath('saved', 2);
        $payload['events'][0]['event_id'] = 'bot-event';
        $this->withHeader('User-Agent', 'Googlebot/2.1')->postJson('/save-tracker', $payload)
            ->assertJsonPath('saved', 0);
        $payload['events'][0]['event_id'] = 'human-event';
        $this->withHeader('User-Agent', self::BROWSER)->postJson('/save-tracker', $payload)
            ->assertJsonPath('saved', 2);
        $this->assertDatabaseCount('tracker_events', 3);
        $this->assertDatabaseMissing('tracker_events', ['event_id' => 'bot-event']);
    }
}
