<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaceProfileRequest extends FormRequest
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
                'mimes:jpg,jpeg,png',
                'max:5120', // Maks 5 MB
                'dimensions:min_width=100,min_height=100', // Minimal 100x100 px
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required'   => 'File foto wajah wajib diupload.',
            'image.image'      => 'File harus berupa gambar.',
            'image.mimes'      => 'Format gambar harus JPG atau PNG.',
            'image.max'        => 'Ukuran foto tidak boleh lebih dari 5 MB.',
            'image.dimensions' => 'Ukuran foto minimal 100x100 pixel.',
        ];
    }
}
