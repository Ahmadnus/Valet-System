<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
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
    $carId = $this->route('car')?->id; // أو $this->car->id لو عامل route model binding

    return [
        'plate_number' => [
            'sometimes',
            'string',
            Rule::unique('cars', 'plate_number')->ignore($carId),
        ],
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'cartypeable_id' => [
            'sometimes',
            'integer',
            'exists:cartypes,id'
        ],
    ];
}    public function messages(): array
{
    return [
        'plate_number.string' => trans('Plate number must be a string.'),
        'plate_number.unique' => trans('Plate number has already been taken.'),

        'image.image' => trans('Image must be a valid image.'),
        'image.mimes' => trans('Image must be of type: jpg, jpeg, png, webp.'),
        'image.max' => trans('Image must not exceed 2MB.'),

        'cartypeable_id.integer' => trans('Cartype ID must be an integer.'),
    ];
}
}
