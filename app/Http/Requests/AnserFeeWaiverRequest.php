<?php

namespace App\Http\Requests;
use App\Models\FeeWaiverRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as UserRequest;
use Illuminate\Validation\Rule;

class AnserFeeWaiverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(['approve', 'reject'])],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => trans('Action is required.'),
            'action.string' => trans('Action must be a string.'),
            'action.in' => trans('Action must be either approve or reject.'),
        ];
    }
}
