<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SdkController extends Controller
{
    public function show(string $apiKey): Response
    {
        $apiRecordExists = Cache::remember("sdk:get:$apiKey", 300, function () use ($apiKey) {
            return Site::where('api_key', $apiKey)->exists();
        });
        
        abort_unless($apiRecordExists, 404);

        $endpoint = url('/save-tracker');
        $script = view('sdk.tracker', [
            'apiKey' => $apiKey,
            'endpoint' => $endpoint,
            'assetUrl' => url('/berack/v1.0/berack.min.js'),
        ])->render();

        return response($script, 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
