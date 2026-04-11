<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date', 'after:now'],
            'max_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'submission_type' => ['required', 'in:file,text,both'],
            'file' => ['nullable', 'file', 'max:5120'], // 5MB for assignment file
        ];
    }
}
