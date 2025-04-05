<?php

namespace App\Http\Controllers;

use App\Services\ManagerService;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Http\Requests\Manager\RoleCountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\app\Traits\ApiResponse;

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
        $users = $this->managerService->getAllUsers();
        return $this->successResponse('Users retrieved successfully', $users);
    }

    public function show($id)
    {
        $user = $this->managerService->getUserById($id);
        return $this->successResponse('User retrieved successfully', $user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|max:20',
            'role' => 'required|string|exists:roles,name'
        ]);

        $user = $this->managerService->createUser($validated);
        return $this->successResponse('User created successfully', $user, 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'phone_number' => 'sometimes|string|max:20',
            'role' => 'sometimes|string|exists:roles,name'
        ]);

        $updatedUser = $this->managerService->updateUser($user, $validated);
        return $this->successResponse('User updated successfully', $updatedUser);
    }

    public function destroy($id)
    {
        $result = $this->managerService->deleteUser($id);
        return $this->successResponse('User deleted successfully', $result);
    }

    public function getUserCountsByRole(RoleCountRequest $request)
    {
        $counts = $this->managerService->getUserCountsByRole();
        return $this->successResponse('User role counts retrieved successfully', $counts);
    }
}
