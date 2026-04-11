<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->hasRole('admin-sistem')) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->hasRole('kajur')) {
                return redirect()->intended(route('kajur.dashboard'));
            } elseif ($user->hasRole('guru')) {
                return redirect()->intended(route('guru.dashboard'));
            } elseif ($user->hasRole('siswa')) {
                return redirect()->intended(route('siswa.dashboard'));
            }

            return redirect()->intended(route('login'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak cocok.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
