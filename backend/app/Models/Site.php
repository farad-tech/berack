<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'name', 'domain', 'api_key'])]
class Site extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Site $site): void {
            if ($site->api_key === null) {
                $site->api_key = self::generateApiKey();
            }
        });
    }

    public static function generateApiKey(): string
    {
        do {
            $apiKey = 'trk_' . Str::random(48);
        } while (self::where('api_key', $apiKey)->exists());

        return $apiKey;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trackerEvents(): HasMany
    {
        return $this->hasMany(TrackerEvent::class);
    }

    public function sdkUrl(): string
    {
        return url("/sdk/{$this->api_key}.js");
    }

    public function sdkSnippet(): string
    {
        return '<script async src="' . e($this->sdkUrl()) . '"></script>';
    }
}
