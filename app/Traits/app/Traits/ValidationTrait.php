<?php

namespace App\Traits\app\Traits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
trait ValidationTrait
{
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'message' => trans('Validation failed'),
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
