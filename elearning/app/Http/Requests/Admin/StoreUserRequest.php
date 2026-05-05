<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin-sistem');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'kajur_department_id' => [
                'nullable',
                'exists:departments,id',
                Rule::requiredIf(fn () => in_array('kajur', $this->input('roles', []), true)),
            ],
        ];
    }
}
