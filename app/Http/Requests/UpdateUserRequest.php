<?php

namespace App\Http\Requests;

use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $userId,
            'password' => 'sometimes|string|min:6',
            'phone_number' => 'sometimes|string|max:20',
            'role' => 'sometimes|string|exists:roles,name'
        ];
    }
    public function messages(): array
{
    return [
        'name.string' => trans('Name must be a string.'),
        'name.max' => trans('Name must not exceed 255 characters.'),

        'email.string' => trans('Email must be a string.'),
        'email.email' => trans('Email must be a valid email address.'),
        'email.unique' => trans('Email has already been taken.'),

        'password.string' => trans('Password must be a string.'),
        'password.min' => trans('Password must be at least 6 characters.'),

        'phone_number.string' => trans('Phone number must be a string.'),
        'phone_number.max' => trans('Phone number must not exceed 20 characters.'),

        'role.string' => trans('Role must be a string.'),
        'role.exists' => trans('Selected role does not exist.'),
    ];
}
}
