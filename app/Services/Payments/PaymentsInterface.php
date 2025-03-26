<?php

namespace App\Services\Payments;
use App\Models\Request as UserRequest;
interface PaymentsInterface
{
    public function pay(UserRequest $userRequest);
}
