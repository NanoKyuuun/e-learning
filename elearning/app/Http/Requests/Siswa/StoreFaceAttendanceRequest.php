<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaceAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani oleh middleware role
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // Maks 2 MB — foto dari kamera biasanya lebih kecil
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Foto wajah dari kamera wajib disertakan.',
            'image.image'    => 'File harus berupa gambar.',
            'image.mimes'    => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max'      => 'Ukuran foto tidak boleh lebih dari 2 MB.',
        ];
    }
}
