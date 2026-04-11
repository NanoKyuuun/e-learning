<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('siswa');
    }

    public function rules(): array
    {
        return [
            'submission_text' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'], // 10MB
            'student_notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
