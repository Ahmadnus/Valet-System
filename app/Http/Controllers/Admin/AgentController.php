<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationAgent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('valet_agent')
            ->with(['locationse'])
            ->withAvg('ratingsReceived', 'rating')
            ->withCount(['assignedRequests as completed_deliveries' => fn ($q) => $q->where('status', 'completed')]);

        if ($locationId = $request->integer('location_id')) {
            $query->whereHas('locationse', fn ($q) => $q->where('locations.id', $locationId));
        }

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        $agents = $query->orderBy('name')->paginate(15)->withQueryString();

        // Pull the current pivot status per agent/location for display.
        $agentIds = $agents->pluck('id');
        $assignments = LocationAgent::with('location')
            ->whereIn('agent_id', $agentIds)
            ->get()
            ->groupBy('agent_id');

        $locations = Location::all();

        $stats = [
            'total_agents' => User::role('valet_agent')->count(),
            'on_duty' => LocationAgent::whereIn('status', ['available', 'occupied', 'in_way'])->distinct('agent_id')->count('agent_id'),
            'available_now' => LocationAgent::where('status', 'available')->distinct('agent_id')->count('agent_id'),
            'avg_rating' => round((float) (\App\Models\Rating::where('ratee_role', 'valet_agent')->avg('rating') ?? 0), 2),
        ];

        return view('admin.agents.index', [
            'agents' => $agents,
            'assignments' => $assignments,
            'locations' => $locations,
            'stats' => $stats,
            'filters' => $request->only(['location_id', 'search']),
        ]);
    }

    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        $exists = LocationAgent::where('agent_id', $id)
            ->where('location_id', $validated['location_id'])
            ->exists();

        if (! $exists) {
            LocationAgent::create([
                'agent_id' => $id,
                'location_id' => $validated['location_id'],
                'status' => 'available',
            ]);
            Log::info("Admin assigned agent {$id} to location {$validated['location_id']}.");
        }

        return back()->with('status', 'Agent assigned to location.');
    }

    public function unassign(Request $request, int $id)
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        DB::transaction(function () use ($id, $validated) {
            LocationAgent::where('agent_id', $id)
                ->where('location_id', $validated['location_id'])
                ->delete();

            \App\Models\Request::where('valet_agent_id', $id)
                ->where('location_id', $validated['location_id'])
                ->whereIn('status', ['accepted', 'in_progress', 'preparing', 'delivering'])
                ->update(['valet_agent_id' => null]);
        });

        Log::info("Admin unassigned agent {$id} from location {$validated['location_id']}.");

        return back()->with('status', 'Agent removed from location.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'status' => ['required', 'in:available,occupied,in_way'],
        ]);

        LocationAgent::where('agent_id', $id)
            ->where('location_id', $validated['location_id'])
            ->update(['status' => $validated['status']]);

        Log::info("Admin set agent {$id} status to {$validated['status']} at location {$validated['location_id']}.");

        return back()->with('status', 'Valet status updated.');
    }
}
