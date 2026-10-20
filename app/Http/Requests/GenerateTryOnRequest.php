<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTryOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'sesi_id' => 'required|uuid|exists:tryon_sesi,id',
            'produk_id' => 'required|exists:produk,produk_id',
        ];
    }
}
