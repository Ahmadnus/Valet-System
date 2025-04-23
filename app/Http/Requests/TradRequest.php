<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Car;
use App\Models\Request as userRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
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
            'phone_number' => 'required|string',
            'plate_number' => 'required|string|exists:cars,plate_number',
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

        if ($user) {
            $hasActiveRequest = UserRequest::where('user_id', $user->id)
                ->whereIn('status', [
                    UserRequest::STATUS_PENDING,
                    UserRequest::STATUS_IN_PROGRESS
                ])
                ->exists();

            if ($hasActiveRequest) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => [
                        'phone_number' => ['This guest already has an active parking request.']
                    ]
                ], 422));
            }
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $plateNumber = $this->input('plate_number');
            $requestId = $this->route('id');

            $car = Car::where('plate_number', $plateNumber)->first();

            if (!$car) return;


        });
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
