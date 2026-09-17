<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackerEvent extends Model
{
    protected $fillable = [
        'site_id',
        'event_id',
        'event_name',
        'anonymous_id',
        'session_id',
        'tab_id',
        'sequence',
        'url',
        'path',
        'title',
        'previous_url',
        'referrer',
        'occurred_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('site', fn (Builder $siteQuery): Builder => $siteQuery->where('user_id', $userId));
    }
}
