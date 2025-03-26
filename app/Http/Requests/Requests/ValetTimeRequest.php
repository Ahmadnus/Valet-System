<?php

namespace App\Http\Requests\Requests;

use Illuminate\Http\Request;
use App\Models\Request as UserRequest;
use App\Models\ValetTime ;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ValetTimeRequest extends FormRequest
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
            'request_id' =>  'integer', 'exists:user_requests,id',
            'hours' => 'numeric', 'min:0',
            'user_id' =>  'integer', 'exists:users,id',
            'price' => 'numeric', 'min:0'

        ];

    }
    }
