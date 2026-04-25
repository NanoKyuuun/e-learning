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
            $user = Auth::user();

            // Cek status user
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi admin.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Tentukan dashboard default berdasarkan role
            $defaultDashboard = route('login');
            $rolePrefix = '';

            if ($user->hasRole('admin-sistem')) {
                $defaultDashboard = route('admin.dashboard');
                $rolePrefix = '/admin';
            } elseif ($user->hasRole('kajur')) {
                $defaultDashboard = route('kajur.dashboard');
                $rolePrefix = '/kajur';
            } elseif ($user->hasRole('guru')) {
                $defaultDashboard = route('guru.dashboard');
                $rolePrefix = '/guru';
            } elseif ($user->hasRole('siswa')) {
                $defaultDashboard = route('siswa.dashboard');
                $rolePrefix = '/siswa';
            }

            // Validasi intended URL agar tidak nyasar ke area role lain (mencegah 403)
            $intended = session()->get('url.intended');
            if ($intended && $rolePrefix && !str_contains($intended, $rolePrefix)) {
                session()->forget('url.intended');
            }

            return redirect()->intended($defaultDashboard);
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
