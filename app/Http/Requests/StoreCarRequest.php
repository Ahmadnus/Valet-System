<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cartype;

class StoreCarRequest extends FormRequest
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
        $carTypes = Cartype::pluck('type')->toArray();
        return [
            'plate_number' => 'sometimes|string|unique:cars,plate_number,' ,
            'key_box' => 'sometimes|string|max:255',
            'cartypeable_id' => 'sometimes|integer',
            'cartypeable_type' => ['sometimes', 'string', Rule::in($carTypes)],
        ];
    }
}
