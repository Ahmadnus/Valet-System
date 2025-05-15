<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Car;
use App\Models\Request as userRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Rules\SyrianPhoneNumber;
use App\Traits\app\Traits\ValidationTrait;

class TradRequest extends FormRequest
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
        'phone_number' => ['required', new SyrianPhoneNumber],
            'plate_number' => 'required|string',
            'location_id' => 'required|exists:locations,id',
            'parking_slot_id' => 'required|exists:parking_slots,id',
            'cartypeable_id' => 'required|integer',
            'requested_by_user' => 'required',
        ];
    }
    public function messages(): array
{
    return [
        'phone_number.required' => trans('Phone number is required.'),

        'plate_number.required' => trans('Plate number is required.'),
        'plate_number.string' => trans('Plate number must be a string.'),

        'location_id.required' => trans('Location is required.'),
        'location_id.exists' => trans('Selected location does not exist.'),

        'parking_slot_id.required' => trans('Parking slot is required.'),
        'parking_slot_id.exists' => trans('Selected parking slot does not exist.'),

        'cartypeable_id.required' => trans('Car type ID is required.'),
        'cartypeable_id.integer' => trans('Car type ID must be an integer.'),

        'requested_by_user.required' => trans('Requested by user is required.'),
    ];
}

    public function prepareForValidation()
    {
        $phoneNumber = $this->input('phone_number');

        $user = User::where('phone_number', $phoneNumber)->first();


    }



    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }

}
