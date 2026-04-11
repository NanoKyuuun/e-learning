<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load(['roles']);
        
        $academicProfile = null;
        if ($user->hasRole('guru')) {
            $academicProfile = $user->teacher->load('department');
        } elseif ($user->hasRole('siswa')) {
            $academicProfile = $user->student->load('enrollments.classGroup');
        }

        return Inertia::render('Shared/Profile/Edit', [
            'mustVerifyEmail' => false,
            'status' => session('status'),
            'academicProfile' => $academicProfile,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username,' . $request->user()->id],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $request->user()->id],
        ]);

        $request->user()->fill($request->only('full_name', 'username', 'email'));

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diubah.');
    }
}
