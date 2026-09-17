@extends('panel.layout')
@section('title', __('panel.'.$mode.'_title') === 'panel.'.$mode.'_title' ? __('panel.'.$mode) : __('panel.'.$mode.'_title'))
@section('content')
<div class="auth-content">
    <div class="auth-mark"><x-panel.icon name="arrow-trending-up" /></div>
    <h1>{{ match($mode) { 'login' => __('panel.welcome'), 'register' => __('panel.register_title'), 'forgot' => __('panel.forgot_title'), default => __('panel.reset_title') } }}</h1>
    @if (in_array($mode, ['login', 'register']))<p class="muted">{{ __('panel.'.$mode.'_subtitle') }}</p>@endif
    <form class="form-stack" method="post" action="{{ match($mode) { 'login' => route('panel.login'), 'register' => route('panel.register'), 'forgot' => route('password.email'), default => route('password.update') } }}">
        @csrf
        @if ($mode === 'reset')<input type="hidden" name="token" value="{{ $token }}">@endif
        @if ($mode === 'register')<label>{{ __('panel.name') }}<input name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus></label>@endif
        <label>{{ __('panel.email') }}<input type="email" name="email" dir="ltr" value="{{ old('email', $email ?? '') }}" required maxlength="255" autocomplete="email" placeholder="you@example.com" @if($mode !== 'register') autofocus @endif></label>
        @if ($mode !== 'forgot')
            <label>{{ __('panel.password') }}<input type="password" name="password" dir="ltr" required @if($mode !== 'login') minlength="8" @endif autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}"></label>
        @endif
        @if (in_array($mode, ['register', 'reset']))<label>{{ __('panel.password_confirmation') }}<input type="password" name="password_confirmation" dir="ltr" required minlength="8" autocomplete="new-password"></label>@endif
        @if ($mode === 'login')<div class="form-between"><label class="checkbox-label"><input type="checkbox" name="remember" value="1">{{ __('panel.remember') }}</label><a href="{{ route('password.request') }}">{{ __('panel.forgot') }}</a></div>@endif
        <button class="button primary full-width">{{ match($mode) { 'login' => __('panel.login'), 'register' => __('panel.register'), 'forgot' => __('panel.send_reset'), default => __('panel.reset_password') } }}<x-panel.icon name="arrow-left" class="direction-icon" /></button>
    </form>
    <p class="auth-footer">{{ $mode === 'login' ? __('panel.no_account') : __('panel.has_account') }} <a href="{{ $mode === 'login' ? route('panel.register') : route('login') }}">{{ $mode === 'login' ? __('panel.register') : __('panel.login') }}</a></p>
</div>
@endsection
