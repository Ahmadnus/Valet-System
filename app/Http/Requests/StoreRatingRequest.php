<?php

namespace App\Http\Requests;

use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
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
            'customer_id' => [ 'exists:users,id'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
    public function messages(): array
{
    return [
        'customer_id.exists' => trans('The selected customer is invalid.'),

        'rating.required' => trans('Rating is required.'),
        'rating.numeric' => trans('Rating must be a number.'),
        'rating.min' => trans('Rating must be at least 1.'),
        'rating.max' => trans('Rating may not be greater than 5.'),

        'comment.string' => trans('Comment must be a string.'),
        'comment.max' => trans('Comment may not be greater than 1000 characters.'),
    ];
}
}
