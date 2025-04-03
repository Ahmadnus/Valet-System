<?php

namespace App\Services;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\Host;
use App\Models\Provider;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
class ManagerService
{
    /**
     * Create a new class instance.
     */
    public function getAllUsers()
    {
        return UserResource::collection(User::with('roles')->get());
    }

    public function getUserById($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(new UserResource($user));
    }
    public function createUser($validated)
    {
        DB::beginTransaction();
        try {
            $userable = $this->createUserableEntity($validated['role']);
            if (!$userable) {
                return response()->json(['message' => 'Invalid role'], 400);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'userable_id' => $userable->id,
                'userable_type' => get_class($userable)
            ]);

            $user->assignRole(Role::where('name', $validated['role'])->where('guard_name', 'api')->first());

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'user' => new UserResource($user)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updateUser($user, $validated)
    {
        DB::beginTransaction();
        try {
            if (isset($validated['role']) && !$user->hasRole($validated['role'])) {
                $userable = $this->createUserableEntity($validated['role']);
                if (!$userable) {
                    return response()->json(['message' => 'Invalid role'], 400);
                }
                $user->userable_id = $userable->id;
                $user->userable_type = get_class($userable);
            }

            $user->update([
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'phone_number' => $validated['phone_number'] ?? $user->phone_number,
                'password' => isset($validated['password']) ? Hash::make($validated['password']) : $user->password,
            ]);

            if (isset($validated['role'])) {
                $user->assignRole(Role::where('name', $validated['role'])->where('guard_name', 'api')->first());
            }

            DB::commit();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => new UserResource($user)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
    public function getUserCountsByRole()
    {
        $users = User::selectRaw('userable_type, COUNT(*) as count')
            ->groupBy('userable_type')
            ->get();

        $typeCounts = $users->mapWithKeys(function ($user) {
            return [class_basename($user->userable_type) => $user->count];
        });

        return response()->json($typeCounts);
    }

    private function createUserableEntity($role)
    {
        return match ($role) {
            'valet_customer' => Customer::create([]),
            'valet_agent' => Agent::create([]),
            'valet_host' => Host::create([]),
            'valet_provider' => Provider::create([]),
            default => null,
        };
    }

}
