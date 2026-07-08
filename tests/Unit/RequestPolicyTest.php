<?php

namespace Tests\Unit;

use App\Models\Manager;
use App\Models\Customer;
use App\Models\Request as UserRequest;
use App\Models\User;
use App\Policies\RequestPolicy;
use PHPUnit\Framework\TestCase;

class RequestPolicyTest extends TestCase
{
    private function user(int $id, string $userableType): User
    {
        $user = new User();
        $user->id = $id;
        $user->userable_type = $userableType;

        return $user;
    }

    private function request(int $ownerId): UserRequest
    {
        $request = new UserRequest();
        $request->user_id = $ownerId;

        return $request;
    }

    public function test_owner_may_pay(): void
    {
        $policy = new RequestPolicy();
        $this->assertTrue($policy->pay($this->user(1, Customer::class), $this->request(1)));
    }

    public function test_non_owner_may_not_pay(): void
    {
        $policy = new RequestPolicy();
        $this->assertFalse($policy->pay($this->user(2, Customer::class), $this->request(1)));
    }

    public function test_manager_may_pay_any_request(): void
    {
        $policy = new RequestPolicy();
        $this->assertTrue($policy->pay($this->user(9, Manager::class), $this->request(1)));
    }
}
