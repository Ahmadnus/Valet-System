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
use Mockery\Expectation;

class ManagerService
{
    public function getAllUsers()
    {
        return UserResource::collection(User::with('roles')->get());
    }

    public function getUserById($id)
{
    $user = User::with('roles')->find($id);

    if (!$user) {
        throw new \Exception(trans('User not found'));
    }

    return new UserResource($user);
}

public function createUser($validated)
{
    DB::beginTransaction();

    try {
        $userable = $this->createUserableEntity($validated['role']);

        if (!$userable) {
            throw new \Exception(trans('Invalid role'));
        }

        $user = User::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'userable_id' => $userable->id,
            'userable_type' => get_class($userable)
        ]);

        $role = Role::where('name', $validated['role'])
                    ->where('guard_name', 'api')
                    ->first();

        if (!$role) {
            throw new \Exception(trans('Role not found'));
        }

        $user->assignRole($role);

        DB::commit();

        return new UserResource($user);

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

public function updateUser($user, $validated)
{
    DB::beginTransaction();

    try {
        if (isset($validated['role']) && !$user->hasRole($validated['role'])) {
            $userable = $this->createUserableEntity($validated['role']);

            if (!$userable) {
                throw new \Exception(trans('Invalid role'));
            }

            $user->userable_id = $userable->id;
            $user->userable_type = get_class($userable);
        }

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'phone_number' => $validated['phone_number'] ?? $user->phone_number,
            'password' => isset($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        if (isset($validated['role'])) {
            $role = Role::where('name', $validated['role'])
                        ->where('guard_name', 'api')
                        ->first();

            if (!$role) {
                throw new \Exception(trans('Role not found'));
            }

            $user->syncRoles([$role]);
        }

        DB::commit();

        return new UserResource($user);

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

public function deleteUser($id)
{
    $user = User::find($id);

    if (!$user) {
        throw new \Exception(trans('User not found'));
    }

    $user->delete();
}    public function getUserCountsByRole()
    {
        $users = User::selectRaw('userable_type, COUNT(*) as count')
            ->groupBy('userable_type')
            ->get();

        return $users->mapWithKeys(function ($user) {
            return [class_basename($user->userable_type) => $user->count];
        });
    }

    private function createUserableEntity($role)
    {
        return match ($role) {
            'valet_customer' => Customer::create([]),
            'valet_agent' => Agent::create([]),
            'valet_host' => Host::create([]),

            default => null,
        };
    }
}