<?php

namespace App\Http\Requests\Location;

use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
class LocationRequest extends FormRequest
{
    use ValidationTrait;
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
    $rules = [
        'name.ar' => 'string|max:255',
        'name.en' => 'string|max:255',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'cost' => 'required|numeric|min:0',
        'number_of_slots' => 'nullable|integer|min:1',
        'host_id' => 'nullable|exists:users,id',
        'agent_ids' => 'nullable|array',
        'agent_ids.*' => 'integer|exists:users,id',
    ];

    if ($this->user()?->hasRole('valet_host')) {
        $rules['host_id'] = 'required|exists:users,id';
    }

    if ($this->user()?->hasRole('valet_agent')) {
        $rules['agent_ids'] = 'required|array|min:1';
        $rules['agent_ids.*'] = 'integer|exists:users,id';
    }

    return $rules;
}


    public function messages(): array
{
    return [
        'name.ar.string' => trans('Arabic name must be a string.'),
        'name.ar.max' => trans('Arabic name must not exceed 255 characters.'),

        'name.en.string' => trans('English name must be a string.'),
        'name.en.max' => trans('English name must not exceed 255 characters.'),

        'latitude.required' => trans('Latitude is required.'),
        'latitude.numeric' => trans('Latitude must be a number.'),
        'latitude.between' => trans('Latitude must be between -90 and 90.'),

        'longitude.required' => trans('Longitude is required.'),
        'longitude.numeric' => trans('Longitude must be a number.'),
        'longitude.between' => trans('Longitude must be between -180 and 180.'),

        'cost.required' => trans('Cost is required.'),
        'cost.numeric' => trans('Cost must be a number.'),
        'cost.min' => trans('Cost must be at least 0.'),

        'agent_ids.array' => trans('Agent IDs must be an array.'),
        'agent_ids.*.integer' => trans('Each agent ID must be an integer.'),
        'agent_ids.*.exists' => trans('One or more agent IDs are invalid.'),
    ];
}
}
