<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Exception;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignupRequest;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {

    }
    public function signup(SignupRequest $request)
    {
        try {

            $data = $this->authService->register($request->validated());

            return [$data,'enter the code that we send to telegram'];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function verifyOtp(Request $request)
{
    $request->validate([
        'phone_number' => 'required|exists:users,phone_number',
        'otp' => 'required|digits:5',
    ]);

    try {

        $data = $this->authService->verifyOtp($request->phone_number, $request->otp);
        return response()->json($data);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

    public function login(Request $request)
    {

        $data = $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'password' => ['required']
        ]);




        $data = $this->authService->login($data);


        return response()->json([ 'enter the code that we send to telegram']);

    }
    public function verifyLogOtp(Request $request)
{
    $data = $request->validate([
        'phone_number' => 'required|exists:users,phone_number',
        'otp' => 'required|numeric',
    ]);

    return $this->authService->verifyLogOtp($data['phone_number'], $data['otp']);
}

    public function logout(Request $request)
    {
        $this->authService->logout();
        return response([]);
    }

}
