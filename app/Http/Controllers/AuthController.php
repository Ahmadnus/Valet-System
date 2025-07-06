<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Exception;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\login;
use App\Http\Requests\SignupRequest;
use App\Rules\SyrianPhoneNumber;

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
            'phone_number' => ['required', new SyrianPhoneNumber, 'exists:users,phone_number'],
            'otp' => 'required|digits:5',
        ]);

        try {
            $data = $this->authService->verifyOtpCode($request->phone_number, $request->otp);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function login(login $request)
    {


$data = $request->validated();




        $data = $this->authService->login($data);


        return $data;

    }
    public function verifyLogOtp(Request $request)
    {
        $data = $request->validate([
            'phone_number' => ['required', new SyrianPhoneNumber, 'exists:users,phone_number'],
            'otp' => 'required|numeric',
            'fcm_token' => [],
        ]);

        try {
            $data = $this->authService->verifyOtpCode($data['phone_number'], $data['otp']);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


public function logout(Request $request)
{
    $response = $this->authService->logout($request);
    return response()->json($response);
}
}
