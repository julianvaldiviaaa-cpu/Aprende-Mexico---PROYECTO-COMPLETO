<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            ? ($this->user()?->can('update', $course) ?? false)
            : ($this->user()?->can('create', Course::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,png,jpg,jpeg', 'max:20480'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'category_ids' => ['required', 'array', 'min:1', 'max:10'],
            'category_ids.*' => ['required', 'integer', 'distinct', Rule::exists('categories', 'id')],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'El título del curso es obligatorio.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'level.in' => 'Selecciona un nivel válido.',
            'status.in' => 'Selecciona un estado válido.',
            'category_ids.required' => 'Selecciona al menos una categoría.',
            'category_ids.min' => 'Selecciona al menos una categoría.',
            'category_ids.max' => 'Puedes seleccionar hasta 10 categorías.',
            'category_ids.*.distinct' => 'No repitas una categoría.',
            'category_ids.*.exists' => 'Una de las categorías seleccionadas no existe.',
        ];
    }
}
