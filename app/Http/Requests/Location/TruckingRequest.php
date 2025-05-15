<?php

namespace App\Http\Requests\location;
use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class TruckingRequest extends FormRequest
{use ValidationTrait;

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
            'agent_id' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
    }
    public function messages(): array
{
    return [
        'agent_id.required' => trans('Agent ID is required.'),
        'agent_id.integer' => trans('Agent ID must be an integer.'),

        'latitude.required' => trans('Latitude is required.'),
        'latitude.numeric' => trans('Latitude must be a number.'),

        'longitude.required' => trans('Longitude is required.'),
        'longitude.numeric' => trans('Longitude must be a number.'),
    ];
}
}
