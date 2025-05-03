<?php

namespace App\Http\Requests;
use App\Rules\SyrianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'fcm_token' => [],
            'phone_number' => ['required', new SyrianPhoneNumber],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

    }
    public function messages()
{
    return [
        'name.required' => trans('validation.name_required'),
        'name.string' => trans('validation.name_string'),
        'name.regex' => trans('validation.name_regex'),
        'phone_number.required' => trans('validation.phone_number_required'),
        'phone_number.regex' => trans('validation.phone_number_invalid'),
        'password.required' => trans('validation.password_required'),
        'password.string' => trans('validation.password_string'),
        'password.min' => trans('validation.password_min'),
        'password.confirmed' => trans('validation.password_confirmed'),
    ];
}
}
