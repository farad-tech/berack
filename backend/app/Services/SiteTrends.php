<?php

namespace App\Services;

use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SiteTrends
{
    public function __construct(private Site $site) {}

    public function report(array $filters): array
    {
        $period = $filters['period'] ?? 'week';
        $timezone = $filters['timezone'] ?? 'UTC';
        $anchor = isset($filters['date'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $filters['date'], $timezone)
            : CarbonImmutable::now($timezone);
        $start = match ($period) {
            'day' => $anchor->startOfDay(),
            'week' => $anchor->startOfWeek(CarbonImmutable::MONDAY),
            'month' => $anchor->startOfMonth(),
            'year' => $anchor->startOfYear(),
        };
        $end = $this->shift($start, $period, 1);
        $previousStart = $this->shift($start, $period, -1);
        $buckets = [];
        // Step through real UTC hours so DST days have 23 or 25 distinct buckets.
        for ($cursor = $start; $cursor->lt($end); $cursor = $next) {
            $next = match ($period) {
                'day' => $cursor->utc()->addHour()->setTimezone($timezone)->min($end),
                'year' => $cursor->addMonth()->min($end),
                default => $cursor->addDay()->min($end),
            };
            $buckets[] = [
                'start' => $cursor->utc()->toDateTimeString(),
                'end' => $next->utc()->toDateTimeString(),
                'label' => $cursor->format(match ($period) { 'day' => 'H:i', 'year' => 'M', default => 'M j' }),
                'full_label' => $cursor->format('Y-m-d H:i P'),
            ];
        }
        $events = (new JourneyReport($this->site))->events()->toBase();
        $journeys = (clone $events)->selectRaw('MIN(occurred_at) as started_at')
            ->groupBy('anonymous_id', 'session_id', 'tab_id');
        $stepQuery = (clone $events)->where('occurred_at', '>=', $start->utc())
            ->where('occurred_at', '<', $end->utc());
        $visitQuery = DB::query()->fromSub($journeys, 'journeys')
            ->where('started_at', '>=', $start->utc())->where('started_at', '<', $end->utc());
        $steps = $this->counts($stepQuery, 'occurred_at', $buckets);
        $visits = $this->counts($visitQuery, 'started_at', $buckets);
        foreach ($buckets as $index => &$bucket) {
            $bucket['visits'] = (int) ($visits->{'bucket_'.$index} ?? 0);
            $bucket['steps'] = (int) ($steps->{'bucket_'.$index} ?? 0);
        }
        unset($bucket);
        $previous = [
            'visits' => DB::query()->fromSub($journeys, 'journeys')->where('started_at', '>=', $previousStart->utc())
                ->where('started_at', '<', $start->utc())->count(),
            'steps' => (clone $events)->where('occurred_at', '>=', $previousStart->utc())->where('occurred_at', '<', $start->utc())->count(),
        ];
        $totals = ['visits' => array_sum(array_column($buckets, 'visits')), 'steps' => array_sum(array_column($buckets, 'steps'))];
        $changes = [];
        foreach ($totals as $key => $total) {
            $changes[$key] = $previous[$key] ? round(($total - $previous[$key]) / $previous[$key] * 100, 1) : null;
        }
        $metric = $filters['metric'] ?? 'visits';
        $peak = collect($buckets)->sortByDesc($metric)->first();

        return compact('period', 'timezone', 'start', 'end', 'previousStart', 'buckets', 'totals', 'previous', 'changes', 'metric', 'peak') + [
            'partial' => CarbonImmutable::now($timezone)->betweenIncluded($start, $end->subSecond()),
        ];
    }

    private function shift(CarbonImmutable $date, string $period, int $amount): CarbonImmutable
    {
        return match ($period) {
            'day' => $date->addDays($amount),
            'week' => $date->addWeeks($amount),
            'month' => $date->addMonthsNoOverflow($amount),
            'year' => $date->addYearsNoOverflow($amount),
        };
    }

    private function counts(Builder $query, string $column, array $buckets): object
    {
        foreach ($buckets as $index => $bucket) {
            $query->selectRaw("SUM(CASE WHEN {$column} >= ? AND {$column} < ? THEN 1 ELSE 0 END) as bucket_{$index}", [$bucket['start'], $bucket['end']]);
        }

        return $query->first();
    }
}
