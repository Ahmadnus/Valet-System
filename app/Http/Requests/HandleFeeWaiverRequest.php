<?php

namespace App\Http\Requests;

use App\Models\FeeWaiverRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as UserRequest;

class HandleFeeWaiverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $requestId = $this->route('requestId'); // assuming it's passed as a route param

        $valetRequest = UserRequest::find($requestId);

        return $valetRequest && $valetRequest->user_id === $user->id;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::user();
            $requestId = $this->route('requestId');

            $valetRequest = UserRequest::find($requestId);

            if (!$valetRequest) {
                $validator->errors()->add('request', 'Request not found.');
                return;
            }

            if ($valetRequest->status !==  UserRequest::STATUS_DELIVERING) {
                $validator->errors()->add('status', 'You can only request a fee waiver during the preparing stage.');
            }

            $existingRequest = FeeWaiverRequest::where('user_id', $user->id)
                ->where('request_id', $requestId)
                ->first();

            if ($existingRequest) {
                $validator->errors()->add('duplicate', 'You already sent a request.');
            }
        });
    }

}
