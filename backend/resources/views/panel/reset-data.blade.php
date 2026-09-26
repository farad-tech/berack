@extends('panel.layout')
@section('title', 'Reset data · '.$site->name)
@section('content')
<div class="page-heading"><div><div class="eyebrow">{{ $site->domain }}</div><h1>Reset recorded data</h1></div></div>
<section class="settings-section" style="max-width: 560px">
    <h2><x-panel.icon name="exclamation-triangle" />{{ number_format($eventCount) }} recorded events</h2>
    <p class="muted">All recorded visits for {{ $site->name }} will be permanently removed. This cannot be undone. Your site, API key, and installation tag stay active; new events can arrive after the reset.</p>
    <form class="form-stack" style="margin-top: 24px" method="post" action="{{ route('panel.sites.reset-data', $site) }}">
        @csrf @method('delete')
        <label>Type {{ $site->domain }} to confirm<input name="domain" value="{{ old('domain') }}" required autocomplete="off" spellcheck="false"></label>
        <label>Current password<input type="password" name="password" required autocomplete="current-password"></label>
        <div class="form-actions"><button class="button danger" type="submit"><x-panel.icon name="trash" />Permanently reset data</button><a class="button secondary" href="{{ route('panel.sites.trends', $site) }}">Cancel</a></div>
    </form>
</section>
@endsection
