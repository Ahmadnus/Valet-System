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

            return $data;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required']
        ]);
        $data = $this->authService->login($data);
        $data['user'] = UserResource::make($data['user']);

        return response()->json([$data, 'user created bro']);

    }
    public function logout(Request $request)
    {
        $this->authService->logout();
        return response([]);
    }

}
