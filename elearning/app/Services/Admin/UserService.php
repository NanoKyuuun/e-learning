<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers($perPage = 10)
    {
        return User::with('roles')
            ->latest()
            ->paginate($perPage);
    }

    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name' => $data['full_name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => $data['status'] ?? 'active',
            ]);

            if (isset($data['roles'])) {
                $user->assignRole($data['roles']);
                $this->createRelatedProfile($user, $data['roles']);
            }

            return $user;
        });
    }

    public function updateUser(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'full_name' => $data['full_name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'status' => $data['status'] ?? 'active',
            ]);

            if (!empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
                $this->createRelatedProfile($user, $data['roles']);
            }

            return $user;
        });
    }

    protected function createRelatedProfile(User $user, array $roles)
    {
        if (in_array('guru', $roles)) {
            Teacher::firstOrCreate(['user_id' => $user->id]);
        }

        if (in_array('siswa', $roles)) {
            // student_number (NIS) nullable — Kajur yang akan mengisi sesuai data resmi.
            Student::firstOrCreate(
                ['user_id' => $user->id]
            );
        }
    }

    public function deleteUser(User $user)
    {
        return $user->delete();
    }
}
