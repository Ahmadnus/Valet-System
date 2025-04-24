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
class TradRequest extends FormRequest
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
        'phone_number' => ['required', new SyrianPhoneNumber],
            'plate_number' => 'required|string',
            'location_id' => 'required|exists:locations,id',
            'parking_slot_id' => 'required|exists:parking_slots,id',
            'cartypeable_id' => 'required|integer',
            'requested_by_user' => 'required',
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
