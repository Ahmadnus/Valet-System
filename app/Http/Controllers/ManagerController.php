<?php

namespace App\Http\Controllers;

use App\Services\ManagerService;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Http\Requests\Manager\RoleCountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\app\Traits\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;

class ManagerController extends Controller
{
    use ApiResponse;

    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    public function index()
    {
        $lang = app()->getLocale();
        $users = $this->managerService->getAllUsers();
        return $this->successResponse(trans('Users retrieved successfully'), $users);
    }

    public function show($id)
    {
        $lang = app()->getLocale();
        $user = $this->managerService->getUserById($id);
        return $this->successResponse(trans('User retrieved successfully'), $user);
    }

    public function store(StoreUserRequest $request)
{
    $user = $this->managerService->createUser($request->validated());
    return $this->successResponse(trans('User created successfully'), $user, 201);
}

public function update(UpdateUserRequest $request, User $user)
{
    $updatedUser = $this->managerService->updateUser($user, $request->validated());
    return $this->successResponse(trans('User updated successfully'), $updatedUser);
}

    public function destroy($id)
    {
        $lang = app()->getLocale();
        $result = $this->managerService->deleteUser($id);
        return $this->successResponse(trans('User deleted successfully'), $result);
    }

    public function getUserCountsByRole(RoleCountRequest $request)
    {
        $lang = app()->getLocale();
        $counts = $this->managerService->getUserCountsByRole();
        return $this->successResponse(trans('User role counts retrieved successfully'), $counts);
    }
}
