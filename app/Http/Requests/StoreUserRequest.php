<?php

namespace App\Http\Requests;
use App\Rules\SyrianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',

            'password' => 'sometimes|string|min:6',
        'phone_number' => ['required', new SyrianPhoneNumber],
            'role' => 'sometimes|string|exists:roles,name'
        ];
    }
}
