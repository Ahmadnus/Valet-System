<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Car;
class CheckPlateNumRequest extends FormRequest
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
            'plate_number' => ['required', 'string', 'exists:cars,plate_number'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $plateNumber = $this->input('plate_number');
            $requestId = $this->route('userRequest');

            $car = Car::where('plate_number', $plateNumber)->first();
            if (!$car) {
                return;
            }

            $userRequest = \App\Models\Request::find($requestId);
            if (!$userRequest || $userRequest->car_id !== $car->id) {
                $validator->errors()->add('plate_number', 'The plate number does not match the car in this request.');
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
