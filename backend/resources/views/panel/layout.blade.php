@php
    $navigationSites = auth()->check() ? auth()->user()->sites()->orderBy('name')->get(['id', 'name', 'domain']) : collect();
    $currentSite = $site ?? null;
    $isAuthPage = isset($mode);
@endphp
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', __('panel.journeys')) · Berack</title>
    <link rel="stylesheet" href="{{ route('assets.fonts.vazirmatn.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}?v={{ filemtime(public_path('css/panel.css')) }}">
    <script src="{{ asset('js/panel.js') }}?v={{ filemtime(public_path('js/panel.js')) }}"></script>
</head>
<body class="{{ $isAuthPage ? 'auth-page' : 'workspace-page' }}">
@if (!$isAuthPage)
<aside class="sidebar" id="sidebar">
    <a class="brand" href="{{ route('panel.home') }}"><span class="brand-symbol"><x-panel.icon name="arrows-right-left" /></span><span>berack<span class="brand-dot">.</span></span></a>
    <div class="workspace-label">{{ __('panel.workspace') }} <span class="small-dot"></span></div>
    @if ($navigationSites->isNotEmpty())
        <label class="site-switch"><x-panel.icon name="globe-alt" /><select aria-label="{{ __('panel.sites') }}" data-site-switch>
            <option value="{{ route('panel.sites.index') }}" @selected(!$currentSite)>{{ __('panel.all_sites') }}</option>
            @foreach ($navigationSites as $navSite)<option value="{{ route('panel.sites.reports', $navSite) }}" @selected($currentSite?->id === $navSite->id)>{{ $navSite->name }}</option>@endforeach
        </select></label>
    @endif
    <nav class="primary-nav">
        <a href="{{ $currentSite ? route('panel.sites.reports', $currentSite) : route('panel.home') }}" @class(['nav-link', 'is-current' => request()->routeIs('panel.sites.reports')])><x-panel.icon name="arrow-trending-up" />{{ __('panel.journeys') }}</a>
        <a href="{{ route('panel.sites.index') }}" @class(['nav-link', 'is-current' => request()->routeIs('panel.sites.index', 'panel.sites.create')])><x-panel.icon name="squares-2x2" />{{ __('panel.sites') }}</a>
        <a href="{{ $currentSite ? route('panel.sites.show', $currentSite) : route('panel.installation') }}" @class(['nav-link', 'is-current' => request()->routeIs('panel.installation', 'panel.sites.show', 'panel.sites.edit')])><x-panel.icon name="code-bracket" />{{ __('panel.install') }}</a>
    </nav>
    <div class="sidebar-bottom">
        @if (auth()->user()?->is_admin)<a class="nav-link" href="{{ url('/admin') }}"><x-panel.icon name="shield-check" />{{ __('panel.admin') }}<x-panel.icon name="arrow-up-right" class="nav-tail" /></a>@endif
        <a class="nav-link" href="{{ url('/guide') }}"><x-panel.icon name="book-open" />{{ __('panel.guide') }}<x-panel.icon name="arrow-up-right" class="nav-tail" /></a>
        <div class="account"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="account-text"><strong>{{ auth()->user()->name }}</strong><small dir="ltr">{{ auth()->user()->email }}</small></span><form action="{{ route('panel.logout') }}" method="post">@csrf<button class="icon-button" title="{{ __('panel.logout') }}" aria-label="{{ __('panel.logout') }}"><x-panel.icon name="arrow-right-on-rectangle" /></button></form></div>
    </div>
</aside>
<button type="button" class="nav-overlay" data-menu-close aria-label="{{ __('panel.cancel') }}"></button>
@endif
<div class="{{ $isAuthPage ? 'auth-shell' : 'app-shell' }}">
    <header class="topbar">
        @if ($isAuthPage)
            <a class="brand" href="{{ url('/') }}"><span class="brand-symbol"><x-panel.icon name="arrows-right-left" /></span><span>berack<span class="brand-dot">.</span></span></a>
        @else
            <button type="button" class="icon-button mobile-menu" data-menu-toggle aria-controls="sidebar" aria-expanded="false" aria-label="{{ __('panel.menu') }}"><x-panel.icon name="bars-3" /></button>
            <div class="breadcrumb"><span>{{ __('panel.workspace') }}</span><x-panel.icon name="chevron-left" class="direction-icon" /><strong>{{ $currentSite?->name ?? __('panel.sites') }}</strong></div>
        @endif
        <div class="topbar-tools">
            <button type="button" class="icon-button" data-theme-toggle title="{{ __('panel.theme') }}" aria-label="{{ __('panel.theme') }}"><x-panel.icon name="moon" class="theme-moon" /><x-panel.icon name="sun" class="theme-sun" /></button>
        </div>
    </header>
    <main id="main" class="{{ $isAuthPage ? 'auth-main' : 'main-content' }}">
        @if (session('status'))<div class="notice success" role="status"><x-panel.icon name="check-circle" />{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="notice error" role="alert"><strong>{{ __('panel.errors') }}</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
<div class="toast" id="toast" role="status" data-success="{{ __('panel.copied') }}" data-failure="{{ __('panel.copy_failed') }}" hidden></div>
</body>
</html>
