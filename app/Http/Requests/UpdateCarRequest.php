<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
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
            'plate_number' => 'sometimes|string|unique:cars,plate_number,' ,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cartypeable_id' => 'sometimes|integer',

        ];
    }
}
