<?php

namespace App\Http\Requests\Kajur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('kajur');
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:200'],
            'body'        => ['required', 'string'],
            'target_role' => [
                'required',
                'string',
                Rule::in(['all', 'siswa', 'guru', 'admin-sistem', 'kajur']),
            ],
            'status'   => ['required', 'string', Rule::in(['draft', 'published'])],
            'start_at' => ['nullable', 'date'],
            'end_at'   => ['nullable', 'date', 'after_or_equal:start_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'        => 'Judul pengumuman wajib diisi.',
            'title.max'             => 'Judul maksimal 200 karakter.',
            'body.required'         => 'Isi pengumuman wajib diisi.',
            'target_role.required'  => 'Target penerima wajib dipilih.',
            'target_role.in'        => 'Target penerima tidak valid.',
            'status.required'       => 'Status wajib dipilih.',
            'status.in'             => 'Status hanya boleh draft atau published.',
            'end_at.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ];
    }
}
