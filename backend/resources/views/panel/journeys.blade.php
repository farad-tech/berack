@extends('panel.layout')
@section('title', __('panel.journeys').' · '.$site->name)
@section('content')
@php
    $view = request('view', 'journeys');
    $filtered = request('q') || request('status', 'all') !== 'all' || request('range', 'all') !== 'all';
    $ended = $last && $last->occurred_at->lte(now()->subMinutes(\App\Services\JourneyReport::INACTIVITY_MINUTES));
    $span = $first && $last ? max(0, (int) $first->occurred_at->diffInSeconds($last->occurred_at)) : 0;
    $legacy = $site->trackerEvents()->whereNull('tab_id')->count();
@endphp
<div class="page-heading">
    <div><div class="eyebrow"><span class="small-dot"></span><bdi>{{ $site->domain }}</bdi></div><h1>{{ __('panel.journeys') }}</h1></div>
    <div class="heading-actions"><a class="icon-button bordered" href="{{ request()->fullUrl() }}" title="{{ __('panel.refresh') }}" aria-label="{{ __('panel.refresh') }}"><x-panel.icon name="arrow-path" /></a><a class="button secondary" href="{{ route('panel.sites.show', $site) }}" title="{{ __('panel.install') }}"><x-panel.icon name="code-bracket" />{{ __('panel.install') }}</a></div>
</div>
<section class="metrics" aria-label="{{ __('panel.reports') }}">
    <div class="metric"><span class="metric-icon green"><x-panel.icon name="arrow-trending-up" /></span><div><span>{{ __('panel.visits') }}</span><strong>{{ number_format($stats->visits) }}</strong></div></div>
    <div class="metric"><span class="metric-icon cyan"><x-panel.icon name="squares-2x2" /></span><div><span>{{ __('panel.recorded_steps') }}</span><strong>{{ number_format($stats->steps) }}</strong></div></div>
    <div class="metric"><span class="metric-icon amber"><x-panel.icon name="finger-print" /></span><div><span title="{{ __('panel.privacy_note') }}">{{ __('panel.browsers') }}</span><strong>{{ number_format($stats->browsers) }}</strong></div></div>
    <div class="metric"><span class="metric-icon neutral"><x-panel.icon name="queue-list" /></span><div><span>{{ __('panel.average_steps') }}</span><strong>{{ number_format($stats->average_steps, 1) }}</strong></div></div>
</section>
<div class="view-tabs" role="navigation" aria-label="{{ __('panel.reports') }}">
    <a @class(['view-tab', 'selected' => $view === 'journeys']) href="{{ route('panel.sites.reports', $site).'?'.http_build_query(array_merge(request()->except(['page', 'stepsPage', 'journeyId']), ['view' => 'journeys'])) }}"><x-panel.icon name="arrow-trending-up" />{{ __('panel.journeys') }}<span class="tab-count">{{ number_format($journeys->total()) }}</span></a>
    <a @class(['view-tab', 'selected' => $view === 'last-pages']) href="{{ route('panel.sites.reports', $site).'?'.http_build_query(array_merge(request()->except(['page', 'stepsPage', 'journeyId']), ['view' => 'last-pages'])) }}"><x-panel.icon name="flag" />{{ __('panel.last_pages') }}</a>
</div>
<form class="filters" method="get" action="{{ route('panel.sites.reports', $site) }}">
    <input type="hidden" name="view" value="{{ $view }}">
    <label class="search-field"><x-panel.icon name="magnifying-glass" /><input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('panel.search') }}" aria-label="{{ __('panel.search') }}" maxlength="100"></label>
    <label class="select-field"><x-panel.icon name="calendar-days" /><select name="range" aria-label="{{ __('panel.time') }}">@foreach (['all' => 'all_time', '1' => 'today', '7' => 'week', '30' => 'month'] as $value => $label)<option value="{{ $value }}" @selected(request('range', 'all') == $value)>{{ __('panel.'.$label) }}</option>@endforeach</select></label>
    <label class="select-field"><x-panel.icon name="adjustments-horizontal" /><select name="status" aria-label="{{ __('panel.all_statuses') }}">@foreach (['all' => 'all_statuses', 'ended' => 'ended', 'pending' => 'pending'] as $value => $label)<option value="{{ $value }}" @selected(request('status', 'all') === $value)>{{ __('panel.'.$label) }}</option>@endforeach</select></label>
    <button class="icon-button bordered filter-submit" title="{{ __('panel.filter') }}" aria-label="{{ __('panel.filter') }}"><x-panel.icon name="funnel" /></button>
    @if ($filtered)<a class="icon-button" href="{{ route('panel.sites.reports', $site) }}" aria-label="{{ __('panel.clear') }}" title="{{ __('panel.clear') }}"><x-panel.icon name="x-mark" /></a>@endif
</form>
@if ($view === 'last-pages')
    <section class="last-pages-view"><div class="section-heading"><h2>{{ __('panel.last_pages') }}</h2><span class="muted">{{ __('panel.last_pages_subtitle') }}</span></div>
        @php $totalListed = max(1, array_sum(array_column($lastPages, 'visits'))); @endphp
        @forelse ($lastPages as $lastPage)
            <div class="last-page-row"><span class="rank">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div class="last-page-data"><div class="bar-label"><strong dir="ltr">{{ $lastPage['path'] ?: '/' }}</strong><span>{{ $lastPage['visits'] }} {{ __('panel.results') }}</span></div><div class="data-bar" role="meter" aria-label="{{ $lastPage['path'] }}" aria-valuenow="{{ $lastPage['visits'] }}" aria-valuemin="0" aria-valuemax="{{ $totalListed }}"><span style="width: {{ $lastPage['visits'] / $totalListed * 100 }}%"></span></div></div><span class="percentage" title="{{ __('panel.share') }}">{{ round($lastPage['visits'] / $totalListed * 100) }}%</span></div>
        @empty<div class="empty-state compact"><x-panel.icon name="flag" /><h2>{{ __('panel.no_last_pages') }}</h2></div>@endforelse
    </section>
@elseif ($journeys->isEmpty() && !$first)
    <div class="empty-state"><div class="empty-symbol"><x-panel.icon name="arrow-trending-up" /></div><h2>{{ $filtered ? __('panel.no_results') : __('panel.no_journeys') }}</h2><p>{{ $filtered ? '' : __('panel.no_journeys_subtitle') }}</p><a class="button secondary" href="{{ $filtered ? route('panel.sites.reports', $site) : route('panel.sites.show', $site) }}"><x-panel.icon :name="$filtered ? 'x-mark' : 'code-bracket'" />{{ $filtered ? __('panel.clear') : __('panel.install') }}</a></div>
@else
<div class="journey-workbench">
    <section class="visit-list" aria-label="{{ __('panel.visits') }}">
        <div class="list-heading"><h2>{{ __('panel.visits') }}</h2><span>{{ number_format($journeys->total()) }} {{ __('panel.results') }}</span></div>
        @foreach ($journeys as $journey)
        @php $isEnded = \Illuminate\Support\Carbon::parse($journey->last_at)->lte(now()->subMinutes(30)); @endphp
        <a @class(['visit-row', 'is-selected' => $selectedId == $journey->id]) aria-current="{{ $selectedId == $journey->id ? 'true' : 'false' }}" href="{{ route('panel.sites.reports', $site).'?'.http_build_query(array_merge(request()->except(['stepsPage']), ['journeyId' => $journey->id])).'#journey-detail' }}">
            <div class="visit-top"><span class="browser-token"><x-panel.icon name="finger-print" /><bdi title="{{ $journey->anonymous_id }}">{{ substr(hash('sha256', $journey->anonymous_id), 0, 8) }}</bdi></span><time class="visit-time local-time" dir="ltr" datetime="{{ \Illuminate\Support\Carbon::parse($journey->started_at)->toIso8601String() }}">{{ \Illuminate\Support\Carbon::parse($journey->started_at)->format('m/d · H:i') }} UTC</time></div>
            <div class="mini-route"><div><span class="route-dot start"></span><span dir="ltr">{{ $journey->first_path ?: '/' }}</span></div><div><span class="route-dot end"></span><span dir="ltr">{{ $journey->last_path ?: '/' }}</span></div></div>
            <div class="visit-bottom"><span @class(['status', 'ended' => $isEnded, 'pending' => !$isEnded])><i></i>{{ $isEnded ? __('panel.ended') : __('panel.pending') }}</span><span class="step-count">{{ $journey->steps }} {{ __('panel.step_count') }}<x-panel.icon name="chevron-left" class="direction-icon" /></span></div>
        </a>
        @endforeach
        <x-panel.pagination :paginator="$journeys" />
    </section>
    <section class="journey-detail" id="journey-detail" aria-label="{{ __('panel.journey_detail') }}">
    @if ($first)
        <div class="detail-header"><div><div class="eyebrow">{{ __('panel.journey_detail') }}</div><h2>{{ __('panel.visit') }} <bdi class="visit-number">#{{ $selectedId }}</bdi></h2></div><span @class(['status', 'ended' => $ended, 'pending' => !$ended])><i></i>{{ $ended ? __('panel.ended') : __('panel.pending') }}</span></div>
        <div class="detail-facts"><span><x-panel.icon name="calendar-days" /><time class="local-time" dir="ltr" datetime="{{ $first->occurred_at->toIso8601String() }}">{{ $first->occurred_at->format('Y/m/d · H:i') }} UTC</time></span><span><x-panel.icon name="clock" /><span>{{ __('panel.time') }}: <bdi class="timezone-label" data-timezone-label>UTC</bdi></span></span><span><x-panel.icon name="queue-list" />{{ $steps->total() }} {{ __('panel.step_count') }}</span><span><x-panel.icon name="clock" />{{ __('panel.recorded_span') }}: {{ $span >= 60 ? floor($span / 60).' '.__('panel.minutes').' '.($span % 60).' '.__('panel.seconds') : $span.' '.__('panel.seconds') }}</span></div>
        <div class="route-overview"><div><span class="overview-icon entry-icon"><x-panel.icon name="arrow-right-on-rectangle" /></span><span class="muted">{{ __('panel.entry') }}</span><strong dir="ltr">{{ $first->path ?: '/' }}</strong></div><span class="overview-connector"><span></span><x-panel.icon name="chevron-left" class="direction-icon" /><span></span></span><div><span class="overview-icon last-icon"><x-panel.icon name="flag" /></span><span class="muted">{{ __('panel.last_recorded') }}</span><strong dir="ltr">{{ $last->path ?: '/' }}</strong></div></div>
        <div class="timeline-heading"><h3>{{ __('panel.journeys') }}</h3><span>{{ __('panel.time') }}</span></div>
        <ol class="timeline">
        @foreach ($steps as $step)
            @php
                $isFirst = $step->id === $first->id;
                $isLast = $step->id === $last->id;
                $isReturn = (bool) $step->is_return;
            @endphp
            @if ($previousSequence !== null && $step->sequence > $previousSequence + 1)<li class="timeline-gap"><x-panel.icon name="ellipsis-vertical" />{{ __('panel.gap') }} ({{ $step->sequence - $previousSequence - 1 }})</li>@endif
            <li @class(['timeline-step', 'is-entry' => $isFirst, 'is-last' => $isLast, 'is-return' => $isReturn]) value="{{ $step->sequence }}">
                <span class="step-node">@if($isFirst)<x-panel.icon name="arrow-right-on-rectangle" />@elseif($isLast)<x-panel.icon name="flag" />@elseif($isReturn)<x-panel.icon name="arrow-uturn-left" />@else{{ str_pad($step->sequence, 2, '0', STR_PAD_LEFT) }}@endif</span>
                <div class="step-body"><div class="step-head"><strong dir="ltr">{{ $step->path ?: '/' }}{{ parse_url($step->url, PHP_URL_QUERY) ? '?'.parse_url($step->url, PHP_URL_QUERY) : '' }}{{ parse_url($step->url, PHP_URL_FRAGMENT) ? '#'.parse_url($step->url, PHP_URL_FRAGMENT) : '' }}</strong><time class="local-time" dir="ltr" datetime="{{ $step->occurred_at->toIso8601String() }}">{{ $step->occurred_at->format('H:i:s') }} UTC</time></div><div class="step-title">{{ $step->title }}</div><div class="step-tags">@if($isFirst)<span class="step-tag entry">{{ __('panel.first_step') }}</span>@endif @if($isReturn)<span class="step-tag return"><x-panel.icon name="arrow-uturn-left" />{{ __('panel.returned') }}</span>@endif @if($isLast)<span class="step-tag last">{{ __('panel.last_step') }}</span>@endif<span class="sequence-label">#{{ str_pad($step->sequence, 2, '0', STR_PAD_LEFT) }}</span></div></div>
            </li>
            @php $previousSequence = $step->sequence; @endphp
        @endforeach
        </ol>
        <x-panel.pagination :paginator="$steps" />
        <details class="visit-metadata"><summary><x-panel.icon name="information-circle" />{{ __('panel.journey_detail') }}<x-panel.icon name="chevron-down" /></summary><dl><div><dt>{{ __('panel.browser') }}</dt><dd dir="ltr">{{ $first->anonymous_id }}</dd></div><div><dt>{{ __('panel.tab') }}</dt><dd dir="ltr">{{ $first->tab_id }}</dd></div><div><dt>{{ __('panel.referrer') }}</dt><dd dir="ltr">{{ $first->referrer ?: __('panel.not_recorded') }}</dd></div><div><dt>{{ __('panel.last_recorded') }}</dt><dd dir="ltr">{{ $last->url }}</dd></div></dl><p class="muted">{{ __('panel.privacy_note') }}</p><p class="muted">{{ __('panel.exit_note') }}</p></details>
    @else<div class="empty-state compact"><x-panel.icon name="cursor-arrow-rays" /><h2>{{ __('panel.select_journey') }}</h2></div>@endif
    </section>
</div>
@endif
@if ($legacy)<p class="legacy-note"><x-panel.icon name="information-circle" />{{ __('panel.legacy', ['count' => number_format($legacy)]) }}</p>@endif
@endsection
