@extends('panel.layout')
@section('title', 'Trends · '.$site->name)
@push('head')
<link rel="stylesheet" href="{{ asset('css/trends.css') }}?v={{ filemtime(public_path('css/trends.css')) }}">
<script src="{{ asset('js/trends.js') }}?v={{ filemtime(public_path('js/trends.js')) }}" defer></script>
@endpush
@section('content')
@php
    $metric = $trends['metric'];
    $metricName = $metric === 'visits' ? 'Visits started' : 'Recorded steps';
    $maximum = max(1, max(array_column($trends['buckets'], $metric)));
    $query = ['period' => $trends['period'], 'date' => $trends['start']->toDateString(), 'timezone' => $trends['timezone'], 'metric' => $metric];
    $chartUrl = fn (array $changes) => route('panel.sites.trends', $site).'?'.http_build_query(array_replace($query, $changes));
@endphp
<div class="page-heading"><div><div class="eyebrow"><span class="small-dot"></span>{{ $site->domain }}</div><h1>Visit trends</h1></div><div class="heading-actions"><a class="icon-button bordered" href="{{ request()->fullUrl() }}" title="Refresh" aria-label="Refresh"><x-panel.icon name="arrow-path" /></a><a class="button danger" href="{{ route('panel.sites.reset', $site) }}" title="Reset data"><x-panel.icon name="trash" />Reset data</a></div></div>
<nav class="view-tabs" aria-label="Reports"><a class="view-tab selected" href="{{ route('panel.sites.trends', $site) }}" aria-current="page"><x-panel.icon name="chart-bar" />Trends</a><a class="view-tab" href="{{ route('panel.sites.reports', $site) }}"><x-panel.icon name="arrow-trending-up" />Visit journeys</a><a class="view-tab" href="{{ route('panel.sites.reports', ['site' => $site, 'view' => 'last-pages']) }}"><x-panel.icon name="flag" />Last recorded pages</a></nav>
<div class="trend-toolbar">
    <nav class="period-switch" aria-label="Chart period">@foreach (['day' => 'Daily', 'week' => 'Weekly', 'month' => 'Monthly', 'year' => 'Yearly'] as $value => $label)<a href="{{ $chartUrl(['period' => $value]) }}" @if ($trends['period'] === $value) aria-current="true" @endif>{{ $label }}</a>@endforeach</nav>
    <form class="trend-date" method="get" action="{{ route('panel.sites.trends', $site) }}">
        <input type="hidden" name="period" value="{{ $trends['period'] }}"><input type="hidden" name="metric" value="{{ $metric }}">
        <label>Date<input type="date" name="date" value="{{ $trends['start']->toDateString() }}" min="2000-01-01" max="2099-12-31" required></label>
        <label>Timezone<select name="timezone">@foreach (array_unique([$trends['timezone'], 'UTC', 'Asia/Tehran', 'Europe/London', 'America/New_York', 'Asia/Tokyo']) as $zone)<option value="{{ $zone }}" @selected($zone === $trends['timezone'])>{{ $zone }}</option>@endforeach</select></label>
        <button type="submit" class="icon-button bordered" title="Apply period" aria-label="Apply period"><x-panel.icon name="funnel" /></button>
    </form>
</div>
<div class="trend-period-heading"><div><h2>{{ $trends['start']->format('M j, Y') }}@if ($trends['period'] !== 'day') &ndash; {{ $trends['end']->subDay()->format('M j, Y') }}@endif</h2><p class="muted">{{ $trends['timezone'] }} @if ($trends['partial']) &middot; In progress @endif</p></div><div class="heading-actions"><a class="icon-button bordered" href="{{ $chartUrl(['date' => $trends['previousStart']->toDateString()]) }}" title="Previous period" aria-label="Previous period"><x-panel.icon name="chevron-left" /></a><a class="button secondary" href="{{ $chartUrl(['date' => now($trends['timezone'])->toDateString()]) }}">Current period</a><a class="icon-button bordered" href="{{ $chartUrl(['date' => $trends['end']->toDateString()]) }}" title="Next period" aria-label="Next period"><x-panel.icon name="chevron-right" /></a></div></div>
<section class="trend-metrics" aria-label="Period summary">
    @foreach (['visits' => 'Visits started', 'steps' => 'Recorded steps'] as $key => $label)
    <div><span>{{ $label }}</span><strong>{{ number_format($trends['totals'][$key]) }}</strong><small>@if ($trends['changes'][$key] !== null){{ $trends['changes'][$key] > 0 ? '+' : '' }}{{ $trends['changes'][$key] }}% @else &mdash; @endif<span class="muted"> vs previous {{ $trends['period'] }} ({{ number_format($trends['previous'][$key]) }})</span></small></div>
    @endforeach
    <div><span>Peak {{ $trends['period'] === 'day' ? 'hour' : ($trends['period'] === 'year' ? 'month' : 'day') }}</span><strong class="peak-label">{{ $trends['peak'][$metric] ? $trends['peak']['label'] : 'No activity' }}</strong><small class="muted">{{ number_format($trends['peak'][$metric]) }} {{ strtolower($metricName) }}</small></div>
</section>
@if ($trends['partial'])<p class="trend-note">This period is still in progress. Comparisons use the full previous {{ $trends['period'] }}.</p>@endif
<section class="trend-chart" aria-labelledby="chart-title">
    <div class="section-heading"><h2 id="chart-title">{{ $metricName }}</h2><nav class="period-switch" aria-label="Chart metric"><a href="{{ $chartUrl(['metric' => 'visits']) }}" @if($metric === 'visits') aria-current="true" @endif>Visits</a><a href="{{ $chartUrl(['metric' => 'steps']) }}" @if($metric === 'steps') aria-current="true" @endif>Steps</a></nav></div>
    @if (!$trends['totals'][$metric])<p class="trend-empty">No {{ strtolower($metricName) }} in this period.</p>@endif
    <div class="chart-scroll" tabindex="0" role="region" aria-label="{{ $metricName }} chart">
        <div class="bar-chart" style="--buckets: {{ count($trends['buckets']) }}">
            @foreach ($trends['buckets'] as $bucket)
            <div class="chart-column" tabindex="0" aria-label="{{ $bucket['full_label'] }}: {{ $bucket[$metric] }} {{ strtolower($metricName) }}">
                <span class="chart-value">{{ number_format($bucket[$metric]) }}</span>
                <div class="bar-track"><span class="trend-bar {{ $metric }}" style="height: {{ $bucket[$metric] / $maximum * 100 }}%"></span></div>
                <span class="bucket-label">{{ $bucket['label'] }}</span>
                <span class="chart-tooltip" role="tooltip">{{ $bucket['full_label'] }}<br>{{ $bucket['visits'] }} visits &middot; {{ $bucket['steps'] }} steps</span>
            </div>
            @endforeach
        </div>
    </div>
    <p class="trend-note">Visits are counted at their first recorded step. Steps are counted when they occurred. Older events without journey identifiers are excluded.</p>
</section>
<details class="trend-table"><summary>Period breakdown <x-panel.icon name="chevron-down" /></summary><div class="table-scroll"><table><caption>{{ $metricName }} &middot; {{ $trends['timezone'] }}</caption><thead><tr><th scope="col">Period start</th><th scope="col">Visits started</th><th scope="col">Recorded steps</th></tr></thead><tbody>@foreach ($trends['buckets'] as $bucket)<tr><th scope="row">{{ $bucket['full_label'] }}</th><td>{{ number_format($bucket['visits']) }}</td><td>{{ number_format($bucket['steps']) }}</td></tr>@endforeach</tbody></table></div></details>
@endsection
