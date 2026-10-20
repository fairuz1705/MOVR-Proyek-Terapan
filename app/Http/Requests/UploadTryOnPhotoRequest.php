<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadTryOnPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'consent' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' => __('ui.tryon_error_photo_required'),
            'foto.image' => __('ui.tryon_error_photo_invalid'),
            'foto.mimes' => __('ui.tryon_error_photo_format'),
            'foto.max' => __('ui.tryon_error_photo_size'),
            'consent.required' => __('ui.tryon_error_consent_required'),
            'consent.accepted' => __('ui.tryon_error_consent_required'),
        ];
    }
}
