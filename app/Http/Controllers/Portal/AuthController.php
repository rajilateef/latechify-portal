<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->is_active) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Those credentials do not match our records.']);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages(['email' => 'Your account is awaiting activation by an administrator.']);
        }

        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
