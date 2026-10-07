<?php

namespace App\Http\Requests;

use App\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreInstructorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::User;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'biography' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'display_name.required' => 'Escribe tu nombre público.',
            'display_name.max' => 'El nombre público no puede exceder los 255 caracteres.',
            'biography.max' => 'La presentación no puede exceder los 5000 caracteres.',
        ];
    }
}
