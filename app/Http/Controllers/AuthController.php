<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
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
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {

    }
    public function signup(SignupRequest $request)
    {
        try {
            $data = $this->authService->register($request->validated());

            return $this->successResponse(
                trans('OTP sent successfully. Please verify to continue.'),
                $data,
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
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
            return $this->successResponse($data['message'], [
                'user' => $data['user'],
                'token' => $data['token'],
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function login(login $request)
    {
        try {
            $data = $this->authService->login($request->validated());

            return $this->successResponse(
                $data['message'] ?? trans('OTP sent successfully. Please verify to continue.'),
                ['user' => $data['user']]
            );
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 401);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
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
            return $this->successResponse($data['message'], [
                'user' => $data['user'],
                'token' => $data['token'],
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        try {
            $response = $this->authService->logout($request);
            return $this->successResponse($response['message']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
