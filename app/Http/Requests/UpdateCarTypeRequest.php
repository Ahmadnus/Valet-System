<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type.en' => 'sometimes|string|max:255',
            'type.ar' => 'sometimes|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'type.en.string' => trans('English type must be a string.'),
            'type.en.max' => trans('English type must not exceed 255 characters.'),

            'type.ar.string' => trans('Arabic type must be a string.'),
            'type.ar.max' => trans('Arabic type must not exceed 255 characters.'),

            'logo.image' => trans('Logo must be an image.'),
            'logo.mimes' => trans('Logo must be a file of type: jpg, jpeg, png, webp.'),
            'logo.max' => trans('Logo must not be greater than 2MB.'),
        ];
    }
}
