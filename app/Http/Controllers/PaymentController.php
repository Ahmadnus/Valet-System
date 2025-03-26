<?php

namespace App\Http\Controllers;
use App\Models\Request as UserRequest;
use App\Services\Payments\PaymentService;
use App\Services\Payments\StripeService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
   public function createStripePayment(UserRequest $userRequest)
    {
        $paymentService = new PaymentService(new StripeService());
        $url =  $paymentService->createPayment($userRequest);
        return response()->json(['url' => $url]);
    }

}
