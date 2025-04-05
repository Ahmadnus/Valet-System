<?php

namespace App\Services\Payments;
use App\Models\Request as UserRequest;
use App\Models\Payment;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
class PaymentService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PaymentsInterface $payment)
    {

    }
    public function createPayment(UserRequest $userRequest)
    {


        $method = strtolower(str_replace('Service', '', class_basename($this->payment)));
        $userRequest->payment()->create([

                'amount' => $userRequest->total_price,
                'payment_method' => $method,

            ]);



        return $this->payment->pay($userRequest);
    }

}
