<?php

namespace App\Http\Requests;

use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class FindNearestLocationRequest extends FormRequest
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

        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ];
    }


    public function messages(): array
    {
        return [
            'latitude.required' => trans('Latitude is required.'),
            'latitude.numeric' => trans('Latitude must be a number.'),
            'latitude.between' => trans('Latitude must be between -90 and 90.'),
            'longitude.required' => trans('Longitude is required.'),
            'longitude.numeric' => trans('Longitude must be a number.'),
            'longitude.between' => trans('Longitude must be between -180 and 180.'),
        ];
    }



}
