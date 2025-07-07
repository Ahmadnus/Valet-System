<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'host_id' => 'nullable|exists:users,id',
            'cost' => 'nullable|numeric|min:0',

            'name.ar' => 'nullable|string|max:255',
            'name.en' => 'nullablec|string|max:255',

            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => trans('The latitude is required.'),
            'latitude.numeric' => trans('Latitude must be a number.'),
            'latitude.between' => trans('Latitude must be between -90 and 90.'),

            'longitude.required' => trans('The longitude is required.'),
            'longitude.numeric' => trans('Longitude must be a number.'),
            'longitude.between' => trans('Longitude must be between -180 and 180.'),

            'host_id.required' => trans('The host is required.'),
            'host_id.exists' => trans('The selected host does not exist.'),

            'cost.required' => trans('The cost is required.'),
            'cost.numeric' => trans('Cost must be a number.'),
            'cost.min' => trans('Cost must be at least 0.'),

            'name.ar.required' => trans('The Arabic name is required.'),
            'name.ar.string' => trans('Arabic name must be a string.'),
            'name.ar.max' => trans('Arabic name must not exceed 255 characters.'),

            'name.en.required' => trans('The English name is required.'),
            'name.en.string' => trans('English name must be a string.'),
            'name.en.max' => trans('English name must not exceed 255 characters.'),

            'agent_ids.array' => trans('Agents must be an array of IDs.'),
            'agent_ids.*.integer' => trans('Each agent ID must be an integer.'),
            'agent_ids.*.exists' => trans('One or more selected agents do not exist.'),
        ];
    }
}
