<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Http\Requests\Manager\RoleCountRequest;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\Host;
use App\Models\Provider;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|unique:users,email',
        'password' => 'required|string|min:6',
        'phone_number' => 'required|string|max:20',
        'role' => 'required|string|exists:roles,name'
    ]);

    DB::beginTransaction();
    try {

        $userable = null;
        switch ($validated['role']) {
            case 'valet_customer':
                $userable = Customer::create([]); // Create a Customer
                break;
            case 'valet_agent':
                $userable = Agent::create([]); // Create an Agent
                break;
            case 'valet_host':
                $userable = Host::create([]); // Create a Host
                break;
            case 'valet_provider':
                $userable = Provider::create([]); // Create a Host
                break;
            default:
                return response()->json(['message' => 'Invalid role'], 400);
        }


        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'userable_id' => $userable->id, // Associate with the specific model
            'userable_type' => get_class($userable) // Store the full class name (e.g., App\Models\Customer)
        ]);


        $user->assignRole(Role::where('name', $validated['role'])->where('guard_name', 'api')->first());

        DB::commit();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function getUserCountsByRole(RoleCountRequest $request)
{
    $users = User::selectRaw('userable_type, COUNT(*) as count')
    ->groupBy('userable_type')
    ->get();


$typeCounts = $users->mapWithKeys(function ($user) {
    return [class_basename($user->userable_type) => $user->count];
});

return response()->json($typeCounts);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manager $manager)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|string|min:6',
        'phone_number' => 'sometimes|string|max:20',
        'role' => 'sometimes|string|exists:roles,name'
    ]);

    DB::beginTransaction();
    try {

        if (isset($validated['role']) && !$user->hasRole($validated['role'])) {

            switch ($validated['role']) {
                case 'valet_customer':
                    $userable = Customer::create([]);
                    break;
                case 'valet_agent':
                    $userable = Agent::create([]);
                    break;
                case 'valet_host':
                    $userable = Host::create([]);
                    break;
                case 'valet_provider':
                    $userable = Provider::create([]);
                    break;
                default:
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
            'user' => $user
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
