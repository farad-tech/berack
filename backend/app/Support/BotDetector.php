<?php

namespace App\Support;

use Illuminate\Http\Request;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class BotDetector
{
    public function matches(Request $request): bool
    {
        // CrawlerDetect expects PHP-style header names, not Laravel's HeaderBag keys.
        $headers = ['HTTP_USER_AGENT' => ''];
        foreach ($request->headers->all() as $name => $values) {
            $headers['HTTP_'.strtoupper(str_replace('-', '_', $name))] = implode(' ', $values);
        }

        return (new CrawlerDetect($headers))->isCrawler();
    }
}
