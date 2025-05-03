<?php

namespace App\Http\Requests;
use App\Rules\SyrianPhoneNumber;
use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',

            'password' => 'sometimes|string|min:6',
        'phone_number' => ['required', new SyrianPhoneNumber],
            'role' => 'sometimes|string|exists:roles,name'
        ];
    }
    public function messages()
{
    return [
        'name.sometimes' => trans('The name field is optional, but if provided, it should be a string and max 255 characters.'),
        'name.string' => trans('The name must be a string.'),
        'name.max' => trans('The name may not be greater than 255 characters.'),

        'password.sometimes' => trans('The password field is optional, but if provided, it should be a string and at least 6 characters long.'),
        'password.string' => trans('The password must be a string.'),
        'password.min' => trans('The password must be at least 6 characters long.'),

        'phone_number.required' => trans('The phone number is required.'),
        'phone_number.syrian_phone_number' => trans('The phone number must be a valid Syrian phone number.'),  // Custom message for SyrianPhoneNumber rule.

        'role.sometimes' => trans('The role field is optional, but if provided, it should be a string and must exist in the roles table.'),
        'role.string' => trans('The role must be a string.'),
        'role.exists' => trans('The selected role is invalid.'),
    ];
}
}
