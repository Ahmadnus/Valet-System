<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'host_id' => '',
            'cost' => 'required|numeric|min:0',
            'agent_ids' => 'nullable|array',
    'agent_ids.*' => '',
    'number_of_slots' => [''],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The location name is required.',
            'latitude.required' => 'Latitude is required.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.required' => 'Longitude is required.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'host_id.exists' => 'The selected host does not exist.',
            'cost.required' => 'Cost is required.',
            'cost.min' => 'Cost must be a positive value.',
            'agent_ids.*.exists' => 'One or more selected agents do not exist.',
        ];
    }
}
