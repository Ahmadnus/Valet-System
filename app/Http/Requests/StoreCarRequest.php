<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cartype;
use App\Traits\app\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreCarRequest extends FormRequest
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
    public function rules()
    {
        $carId = $this->route('car'); // فقط عند التحديث
        $carTypes = Cartype::pluck('id')->toArray();

        return [
            'plate_number' => [
                'sometimes',
                'string',
                Rule::unique('cars', 'plate_number')->ignore($carId),
            ],
            'cartypeable_id' => [
                'required',
                'integer',
                Rule::in($carTypes),
            ],
            'cartypeable_type' => [
                'nullable',
                'string',
                Rule::in([\App\Models\CarType::class]), // هذا النوع المسموح به حالياً
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
    public function messages(): array
{
    return [
        'plate_number.string' => trans('Plate number must be a string.'),
        'plate_number.unique' => trans('Plate number has already been taken.'),

        'image.image' => trans('Image must be a valid image.'),
        'image.mimes' => trans('Image must be of type: jpg, jpeg, png, webp.'),
        'image.max' => trans('Image must not exceed 2MB.'),

        'cartypeable_id.integer' => trans('Cartype ID must be an integer.'),
        'cartypeable_id.exists' => trans('The selected cartype ID does not exist.'),
    ];
}
}
