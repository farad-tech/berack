<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('panel.auth', ['mode' => 'login']);
    }

    public function registerForm()
    {
        return view('panel.auth', ['mode' => 'register']);
    }

    public function forgotForm()
    {
        return view('panel.auth', ['mode' => 'forgot']);
    }

    public function resetForm(Request $request, string $token)
    {
        return view('panel.auth', ['mode' => 'reset', 'token' => $token, 'email' => $request->query('email')]);
    }

    public function login(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => 'required|email|max:255', 'password' => 'required|string']);
        if (! Auth::attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('panel.invalid_credentials')]);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('panel.home'));
    }

    public function register(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        $user = User::create($data);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel.sites.create');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('panel.reset_sent'));
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => 'required', 'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => __('panel.reset_invalid')]);
        }

        return redirect()->route('login')->with('status', __('panel.password_updated'));
    }
}
