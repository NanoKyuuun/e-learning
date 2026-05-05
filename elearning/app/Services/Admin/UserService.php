<?php

namespace App\Services\Admin;

use App\Models\DepartmentHeadAssignment;
use App\Models\Student;
use App\Models\User;
use App\Models\Teacher;
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
                $this->syncKajurDepartmentAssignment($user, $data['roles'], $data['kajur_department_id'] ?? null);
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
                $this->syncKajurDepartmentAssignment($user, $data['roles'], $data['kajur_department_id'] ?? null);
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

    protected function syncKajurDepartmentAssignment(User $user, array $roles, ?string $departmentId): void
    {
        $activeAssignments = DepartmentHeadAssignment::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        if (! in_array('kajur', $roles, true)) {
            foreach ($activeAssignments as $assignment) {
                $assignment->update([
                    'is_active' => false,
                    'end_date'  => now()->toDateString(),
                ]);
            }

            return;
        }

        foreach ($activeAssignments->where('department_id', '!=', $departmentId) as $assignment) {
            $assignment->update([
                'is_active' => false,
                'end_date'  => now()->toDateString(),
            ]);
        }

        if (! $departmentId || $activeAssignments->firstWhere('department_id', $departmentId)) {
            return;
        }

        $todayAssignment = DepartmentHeadAssignment::where('user_id', $user->id)
            ->where('department_id', $departmentId)
            ->whereDate('start_date', now()->toDateString())
            ->latest('created_at')
            ->first();

        if ($todayAssignment) {
            $todayAssignment->update([
                'is_active'     => true,
                'end_date'      => null,
                'appointed_by'  => auth()->id(),
            ]);

            return;
        }

        DepartmentHeadAssignment::create([
            'department_id' => $departmentId,
            'user_id'       => $user->id,
            'start_date'    => now()->toDateString(),
            'end_date'      => null,
            'is_active'     => true,
            'appointed_by'  => auth()->id(),
        ]);
    }

    public function deleteUser(User $user)
    {
        return $user->delete();
    }
}
