@extends('panel.layout')
@section('title', $site ? __('panel.install') : __('panel.add_site'))
@section('content')
<div class="page-heading"><div><div class="eyebrow">{{ $site?->domain ?? 'BERACK' }}</div><h1>{{ $site ? __('panel.install') : __('panel.new_site') }}</h1></div>@if ($site)<a class="button secondary" href="{{ route('panel.sites.reports', $site) }}"><x-panel.icon name="arrow-trending-up" />{{ __('panel.view_journeys') }}</a>@endif</div>
<div class="settings-layout">
    <section class="settings-section"><h2><x-panel.icon name="globe-alt" />{{ __('panel.settings') }}</h2>
        <form class="form-stack" method="post" action="{{ $site ? route('panel.sites.update', $site) : route('panel.sites.store') }}">
            @csrf @if ($site) @method('put') @endif
            <label>{{ __('panel.site_name') }}<input name="name" value="{{ old('name', $site?->name) }}" placeholder="{{ __('panel.site_placeholder') }}" required maxlength="255" autofocus></label>
            <label>{{ __('panel.domain') }}<input name="domain" value="{{ old('domain', $site?->domain) }}" placeholder="example.com" dir="ltr" required maxlength="253"></label>
            <div class="form-actions"><button class="button primary"><x-panel.icon :name="$site ? 'check' : 'plus'" />{{ $site ? __('panel.save') : __('panel.add_site') }}</button><a class="button text-button" href="{{ route('panel.sites.index') }}">{{ __('panel.cancel') }}</a></div>
        </form>
    </section>
    @if ($site)
    <section class="settings-section"><h2><x-panel.icon name="code-bracket" />{{ __('panel.install_tag') }}</h2>
        <div class="code-toolbar"><span>HTML</span><button type="button" class="icon-button" data-copy="install-code" title="{{ __('panel.copy') }}" aria-label="{{ __('panel.copy') }}"><x-panel.icon name="clipboard-document" /></button></div>
        <pre class="install-code" dir="ltr"><code id="install-code">{{ $site->sdkSnippet() }}</code></pre>
        <div class="key-label">{{ __('panel.api_key') }}</div><div class="api-key"><code id="api-key" dir="ltr">{{ $site->api_key }}</code><button type="button" class="icon-button" data-copy="api-key" title="{{ __('panel.copy') }}" aria-label="{{ __('panel.copy') }}"><x-panel.icon name="clipboard-document" /></button></div>
        <a class="inline-link" href="{{ url('/guide') }}"><x-panel.icon name="book-open" />{{ __('panel.installation_guide') }}<x-panel.icon name="arrow-up-right" /></a>
    </section>
    @endif
</div>
@if ($site)
<section class="danger-section"><div><h2>{{ __('panel.danger') }}</h2><p class="muted">{{ __('panel.delete_description') }}</p></div><form method="post" action="{{ route('panel.sites.destroy', $site) }}" data-confirm="{{ __('panel.delete_warning') }}">@csrf @method('delete')<button class="button danger"><x-panel.icon name="trash" />{{ __('panel.delete_site') }}</button></form></section>
@endif
@endsection
