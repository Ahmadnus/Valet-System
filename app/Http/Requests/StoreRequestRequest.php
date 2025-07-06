<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequestRequest extends FormRequest
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

     public function prepareForValidation()
     {
         $this->merge([
             'user_id' => Auth::id(),
         ]);
     }
    public function rules(): array
{
    return [
        'car_id' => ['required', 'exists:cars,id'],
        'location_id' => ['required', 'exists:locations,id'],
        'parking_slot_id' => ['required', 'exists:parking_slots,id'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
    ];

}
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $slot = \App\Models\ParkingSlot::where('id', $this->parking_slot_id)
            ->where('location_id', $this->location_id)
            ->first();

        if (!$slot) {
            $validator->errors()->add('parking_slot_id', trans('The selected parking slot does not belong to this location.'));
            return;
        }

        if ($slot->status === 'occupied') {
            $validator->errors()->add('parking_slot_id', trans('This parking slot is currently occupied.'));
        }

        $availableAgentExists = \App\Models\LocationAgent::where('location_id', $this->location_id)
            ->where('status', 'available')
            ->exists();

        if (!$availableAgentExists) {
            $validator->errors()->add('location_id', trans('Sorry, there are no available agents at this location.'));
        }
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

