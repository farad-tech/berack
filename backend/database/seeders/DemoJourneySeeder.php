<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\TrackerEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoJourneySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->when(env('BERACK_DEMO_USER_ID'), fn ($query, $id) => $query->whereKey($id))
            ->orderBy('id')->firstOrFail();
        $now = now()->utc();

        $sites = [
            [
                'name' => 'Demo Shop',
                'domain' => 'shop.berack-demo.test',
                'journeys' => [
                    ['/'],
                    ['/', '/products', '/products/notebook'],
                    ['/', '/products', '/products/notebook', '/products', '/contact'],
                    ['/offers', '/products/headphones', '/cart', '/checkout', '/thank-you'],
                    ['/', '/products', '/products?category=books', '/products/book', '/cart', '/checkout'],
                    ['/blog', '/blog/buying-guide', '/products', '/contact'],
                ],
            ],
            [
                'name' => 'Demo Docs',
                'domain' => 'docs.berack-demo.test',
                'journeys' => [
                    ['/'],
                    ['/', '/getting-started', '/installation'],
                    ['/installation', '/configuration', '/installation', '/faq'],
                    ['/', '/guide', '/guide#setup', '/guide#examples', '/api'],
                    ['/search?q=install', '/installation', '/troubleshooting', '/contact'],
                    ['/changelog', '/upgrade', '/migration'],
                ],
            ],
        ];

        DB::transaction(function () use ($user, $now, $sites): void {
            foreach ($sites as $definition) {
                $site = Site::firstOrCreate([
                    'user_id' => $user->id,
                    'name' => $definition['name'],
                    'domain' => $definition['domain'],
                ]);

                for ($visit = 0; $visit < 24; $visit++) {
                    $browser = intdiv($visit, 2) % 4;
                    $anonymousId = "demo:{$site->id}:browser:{$browser}";
                    $sessionId = "demo:{$site->id}:visit:{$visit}";
                    $tabId = "demo:{$site->id}:browser:{$browser}:tab:".($visit % 2);
                    $startedAt = $visit >= 22
                        ? $now->copy()->subMinutes($visit === 22 ? 8 : 5)
                        : $now->copy()->subDays(intdiv(23 - $visit, 4))->subHours(2 + $visit % 4);
                    $paths = $definition['journeys'][$visit % count($definition['journeys'])];
                    $referrer = ['https://www.google.com/', '', 'https://example.test/article'][$visit % 3];
                    $previousUrl = null;

                    foreach ($paths as $index => $path) {
                        $occurredAt = $startedAt->copy()->addSeconds($index * 45);
                        $url = 'https://'.$site->domain.$path;
                        $eventId = "demo:{$site->id}:visit:{$visit}:step:".($index + 1);
                        $event = [
                            'event_id' => $eventId,
                            'event_name' => 'page_view',
                            'anonymous_id' => $anonymousId,
                            'session_id' => $sessionId,
                            'tab_id' => $tabId,
                            'sequence' => $index + 1,
                            'url' => $url,
                            'path' => parse_url($url, PHP_URL_PATH),
                            'title' => $definition['name'].' - '.$path,
                            'previous_url' => $previousUrl,
                            'referrer' => $referrer,
                            'timestamp' => $occurredAt->toISOString(),
                            'sdk_version' => '1.0.0',
                        ];

                        // Stable IDs let a rerun refresh only this seeder's sample events.
                        TrackerEvent::updateOrCreate(
                            ['site_id' => $site->id, 'event_id' => $eventId],
                            array_merge(collect($event)->except(['timestamp', 'sdk_version'])->all(), [
                                'occurred_at' => $occurredAt,
                                'payload' => $event,
                            ]),
                        );
                        $previousUrl = $url;
                    }
                }
            }
        });

        $this->command?->info("Demo journeys seeded for {$user->email}: 2 sites, 48 visits, 176 page views.");
    }
}
