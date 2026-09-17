@extends('panel.layout')
@section('title', __($installation ?? false ? 'panel.install' : 'panel.sites'))
@section('content')
<div class="page-heading"><div><div class="eyebrow">BERACK / {{ __('panel.workspace') }}</div><h1>{{ __($installation ?? false ? 'panel.install' : 'panel.sites') }}</h1></div><a class="button primary" href="{{ route('panel.sites.create') }}"><x-panel.icon name="plus" />{{ __('panel.add_site') }}</a></div>
@if ($sites->isEmpty())
<div class="empty-state"><div class="empty-symbol"><x-panel.icon name="globe-alt" /></div><h2>{{ __('panel.no_sites') }}</h2><p>{{ __('panel.no_sites_subtitle') }}</p><a class="button primary" href="{{ route('panel.sites.create') }}"><x-panel.icon name="plus" />{{ __('panel.add_site') }}</a></div>
@else
<div class="site-list">
@foreach ($sites as $website)
    @if ($installation ?? false)
    <article class="website-row"><span class="site-avatar">{{ mb_substr($website->name, 0, 1) }}</span><div class="website-identity"><h2>{{ $website->name }}</h2><span class="muted" dir="ltr">{{ $website->domain }}</span></div><div class="website-actions"><a class="button secondary" href="{{ route('panel.sites.show', $website) }}"><x-panel.icon name="code-bracket" />{{ __('panel.install') }}</a></div></article>
    @else
    <article class="website-row"><span class="site-avatar">{{ mb_substr($website->name, 0, 1) }}</span><div class="website-identity"><h2>{{ $website->name }}</h2><span class="muted" dir="ltr">{{ $website->domain }}</span></div><div class="website-count"><strong>{{ number_format($website->tracker_events_count) }}</strong><span>{{ __('panel.recorded_steps') }}</span></div><div class="website-actions"><a class="button secondary" href="{{ route('panel.sites.reports', $website) }}"><x-panel.icon name="arrow-trending-up" />{{ __('panel.view_journeys') }}</a><a class="icon-button" href="{{ route('panel.sites.show', $website) }}" title="{{ __('panel.settings') }}" aria-label="{{ __('panel.settings') }}"><x-panel.icon name="cog-6-tooth" /></a></div></article>
    @endif
@endforeach
</div>
<x-panel.pagination :paginator="$sites" />
@endif
@endsection
