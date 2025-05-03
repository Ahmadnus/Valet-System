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
    public function rules(): array
    {


        {
            $carTypes = Cartype::pluck('id')->toArray();

            return [
                'plate_number' => 'sometimes|string|unique:cars,plate_number,' . $this->route('car'),
                'cartypeable_type' => ['sometimes', 'string', Rule::in($carTypes)],
                'cartypeable_id' => 'nullable|integer|exists:cartypes,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ];
        }}


        public function messages()
        {
            return [
                'plate_number.unique' => 'The plate number is already taken.',
                'cartypeable_type.in' => 'The selected car type is invalid.',
                'cartypeable_id.exists' => 'The selected car type ID does not exist.',
                'image.mimes' => 'The image must be a file of type: jpg, jpeg, png.',
                'image.max' => 'The image size must not exceed 2MB.',
            ];
        }
}
