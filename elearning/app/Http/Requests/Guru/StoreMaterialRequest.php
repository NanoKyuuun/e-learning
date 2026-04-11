<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'], // 10MB
            'file_type' => ['nullable', 'string'],
        ];
    }
}
