<?php

namespace App\Http\Requests\Kajur;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('kajur');
    }

    public function rules(): array
    {
        $classId = $this->route('class_group')->id;

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'code' => ['required', 'string', 'max:50', 'unique:class_groups,code,' . $classId],
            'name' => ['required', 'string', 'max:100'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:13'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
