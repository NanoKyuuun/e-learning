<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin-sistem');
    }

    public function rules(): array
    {
        $ayId = $this->route('academic_year')->id;

        return [
            'name' => ['required', 'string', 'max:20', 'unique:academic_years,name,' . $ayId],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'in:active,inactive,archived'],
        ];
    }
}
