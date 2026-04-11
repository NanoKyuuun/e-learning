<?php

namespace App\Services\Admin\Akademik;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function getAllDepartments($search = null)
    {
        return Department::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createDepartment(array $data)
    {
        return Department::create($data);
    }

    public function updateDepartment(Department $department, array $data)
    {
        return $department->update($data);
    }

    public function deleteDepartment(Department $department)
    {
        return $department->delete();
    }
}
