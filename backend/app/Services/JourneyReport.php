<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class JourneyReport
{
    public const INACTIVITY_MINUTES = 30;

    public function __construct(private Site $site) {}

    public function events(): Builder
    {
        return $this->site->trackerEvents()->getQuery()
            ->where('event_name', 'page_view')->whereNotNull('tab_id')
            ->whereNotNull('session_id')->whereNotNull('anonymous_id')->whereNotNull('sequence');
    }

    public function journeys(): LengthAwarePaginator
    {
        return $this->events()
            ->selectRaw('MIN(id) as id, anonymous_id, session_id, tab_id, MIN(occurred_at) as started_at, MAX(occurred_at) as last_at, COUNT(*) as steps')
            ->groupBy('anonymous_id', 'session_id', 'tab_id')
            ->orderByDesc('last_at')->orderByDesc('id')
            ->paginate(15, ['*'], 'journeysPage');
    }

    public function journey(int $eventId): Builder
    {
        $event = $this->events()->findOrFail($eventId);

        return $this->events()->where('anonymous_id', $event->anonymous_id)
            ->where('session_id', $event->session_id)->where('tab_id', $event->tab_id);
    }

    public function filteredJourneys(array $filters = []): Builder
    {
        $query = $this->events()
            ->selectRaw('MIN(id) as id, anonymous_id, session_id, tab_id, MIN(occurred_at) as started_at, MAX(occurred_at) as last_at, COUNT(*) as steps')
            ->groupBy('anonymous_id', 'session_id', 'tab_id');

        foreach (['first_path' => 'asc', 'last_path' => 'desc'] as $alias => $direction) {
            $query->selectSub(DB::table('tracker_events as boundary')
                ->select('path')->where('boundary.site_id', $this->site->id)
                ->where('boundary.event_name', 'page_view')->whereNotNull('boundary.sequence')
                ->whereColumn('boundary.session_id', 'tracker_events.session_id')
                ->whereColumn('boundary.tab_id', 'tracker_events.tab_id')
                ->whereColumn('boundary.anonymous_id', 'tracker_events.anonymous_id')
                ->orderBy('boundary.sequence', $direction)->orderBy('boundary.id', $direction)->limit(1), $alias);
        }

        $range = $filters['range'] ?? 'all';
        if ($range !== 'all') {
            $query->havingRaw('MAX(occurred_at) >= ?', [now()->subDays((int) $range)]);
        }
        $cutoff = now()->subMinutes(self::INACTIVITY_MINUTES);
        if (($filters['status'] ?? 'all') === 'ended') {
            $query->havingRaw('MAX(occurred_at) <= ?', [$cutoff]);
        }
        if (($filters['status'] ?? 'all') === 'pending') {
            $query->havingRaw('MAX(occurred_at) > ?', [$cutoff]);
        }
        if ($search = trim($filters['q'] ?? '')) {
            // Match whole visits so a page search never trims their history or step count.
            $query->whereExists(function ($match) use ($search) {
                $match->selectRaw('1')->from('tracker_events as matched')
                    ->whereColumn('matched.site_id', 'tracker_events.site_id')
                    ->whereColumn('matched.session_id', 'tracker_events.session_id')
                    ->whereColumn('matched.tab_id', 'tracker_events.tab_id')
                    ->whereColumn('matched.anonymous_id', 'tracker_events.anonymous_id')
                    ->where(function ($match) use ($search) {
                        $match->where('matched.path', 'like', '%'.$search.'%')->orWhere('matched.anonymous_id', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->orderByDesc('last_at')->orderByDesc('id');
    }

    public function orderedSteps(Builder $journey): Builder
    {
        return (clone $journey)->select('tracker_events.*')
            ->selectSub(DB::table('tracker_events as previous_step')->selectRaw('1')
                ->whereColumn('previous_step.site_id', 'tracker_events.site_id')
                ->whereColumn('previous_step.session_id', 'tracker_events.session_id')
                ->whereColumn('previous_step.tab_id', 'tracker_events.tab_id')
                ->whereColumn('previous_step.anonymous_id', 'tracker_events.anonymous_id')
                ->whereColumn('previous_step.url', 'tracker_events.url')
                ->whereColumn('previous_step.sequence', '<', 'tracker_events.sequence')
                ->where('previous_step.event_name', 'page_view')->limit(1), 'is_return')
            ->orderBy('sequence')->orderBy('id');
    }

    public function overview(array $filters): object
    {
        return DB::query()->fromSub($this->filteredJourneys($filters)->reorder(), 'visits')
            ->selectRaw('COUNT(*) as visits, COALESCE(SUM(steps), 0) as steps, COALESCE(AVG(steps), 0) as average_steps, COUNT(DISTINCT anonymous_id) as browsers')
            ->first();
    }

    public function lastPagesForFilters(array $filters): array
    {
        return DB::query()->fromSub($this->filteredJourneys($filters)->reorder(), 'visits')
            ->where('last_at', '<=', now()->subMinutes(self::INACTIVITY_MINUTES))
            ->selectRaw('last_path as path, COUNT(*) as visits')->groupBy('last_path')
            ->orderByDesc('visits')->orderBy('last_path')->limit(10)->get()->map(fn ($row) => (array) $row)->all();
    }

    public function lastPages(): array
    {
        // Pick the last sequenced event, independently of ingestion order.
        return $this->events()->where('occurred_at', '<=', now()->subMinutes(self::INACTIVITY_MINUTES))
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('tracker_events as later')
                    ->whereColumn('later.site_id', 'tracker_events.site_id')
                    ->whereColumn('later.anonymous_id', 'tracker_events.anonymous_id')
                    ->whereColumn('later.session_id', 'tracker_events.session_id')
                    ->whereColumn('later.tab_id', 'tracker_events.tab_id')
                    ->where('later.event_name', 'page_view')
                    ->where(function ($query) {
                        $query->whereColumn('later.sequence', '>', 'tracker_events.sequence')
                            ->orWhere(function ($query) {
                                $query->whereColumn('later.sequence', 'tracker_events.sequence')
                                    ->whereColumn('later.id', '>', 'tracker_events.id');
                            });
                    });
            })
            ->selectRaw('path, COUNT(*) as visits')->groupBy('path')
            ->orderByDesc('visits')->orderBy('path')->limit(10)->get()->toArray();
    }
}
