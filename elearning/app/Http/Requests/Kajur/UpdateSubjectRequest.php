<?php

namespace App\Http\Requests\Kajur;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('kajur');
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')->id;

        return [
            'department_id' => ['nullable', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code,' . $subjectId],
            'name' => ['required', 'string', 'max:150'],
            'grade_level' => ['nullable', 'integer', 'min:1', 'max:13'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
