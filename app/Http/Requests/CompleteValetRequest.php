<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteValetRequest extends FormRequest
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
            'request_id' => 'integer|exists:parking_requests,id',
            'plate_number' => 'max:20',
        ];
    }
    public function messages(): array
{
    return [
        'request_id.integer' => trans('Request ID must be an integer.'),
        'request_id.exists' => trans('The selected request ID is invalid.'),

        'plate_number.max' => trans('Plate number must not exceed 20 characters.'),
    ];
}
}
