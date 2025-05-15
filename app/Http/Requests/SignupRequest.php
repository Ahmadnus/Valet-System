<?php

namespace App\Http\Requests;
use App\Rules\SyrianPhoneNumber;
use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'fcm_token' => [],
            'phone_number' => ['required', new SyrianPhoneNumber],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

    }
    public function messages(): array
    {
        return [
            'name.required' => trans('Name is required.'),
            'name.string' => trans('Name must be a string.'),
            'name.regex' => trans('Name may only contain letters and spaces.'),

            'phone_number.required' => trans('Phone number is required.'),

            'password.required' => trans('Password is required.'),
            'password.string' => trans('Password must be a string.'),
            'password.min' => trans('Password must be at least 8 characters.'),
            'password.confirmed' => trans('Password confirmation does not match.'),
        ];
    }
}
