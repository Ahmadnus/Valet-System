<?php

namespace App\Services\Payments;
use App\Models\Request as UserRequest;
use App\Models\Payment;


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
