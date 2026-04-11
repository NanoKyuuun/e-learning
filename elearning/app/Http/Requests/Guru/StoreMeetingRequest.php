<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru');
    }

    public function rules(): array
    {
        return [
            'meeting_number' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:150'],
            'topic' => ['nullable', 'string'],
            'meeting_date' => ['nullable', 'date'],
        ];
    }
}
