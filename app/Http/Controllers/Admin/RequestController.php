<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationAgent;
use App\Models\RejectedRequest;
use App\Models\Request as ParkingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    private const ACTIVE_STATUSES = [
        ParkingRequest::STATUS_PENDING,
        ParkingRequest::STATUS_ACCEPTED,
        ParkingRequest::STATUS_IN_PROGRESS,
        ParkingRequest::STATUS_PREPARING,
        ParkingRequest::STATUS_DELIVERING,
    ];

    public function index(Request $request)
    {
        $query = ParkingRequest::with(['user', 'car', 'location', 'valetAgent']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($locationId = $request->integer('location_id')) {
            $query->where('location_id', $locationId);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('car', fn ($c) => $c->where('plate_number', 'like', "%{$search}%"));
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        $locations = Location::all();

        $statusCounts = ParkingRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.requests.index', [
            'requests' => $requests,
            'locations' => $locations,
            'statusCounts' => $statusCounts,
            'filters' => $request->only(['status', 'location_id', 'search']),
        ]);
    }

    public function show(int $id)
    {
        $parkingRequest = ParkingRequest::with(['user', 'car', 'location', 'valetAgent', 'payment', 'valetTime'])
            ->findOrFail($id);

        $availableAgents = LocationAgent::with('agent')
            ->where('location_id', $parkingRequest->location_id)
            ->where('status', 'available')
            ->get();

        return view('admin.requests.show', compact('parkingRequest', 'availableAgents'));
    }

    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                $parkingRequest = ParkingRequest::lockForUpdate()->findOrFail($id);

                if (! in_array($parkingRequest->status, self::ACTIVE_STATUSES, true)) {
                    throw new \Exception('Only active requests can be (re)assigned a valet.');
                }

                if ($parkingRequest->valet_agent_id) {
                    LocationAgent::where('agent_id', $parkingRequest->valet_agent_id)
                        ->where('location_id', $parkingRequest->location_id)
                        ->update(['status' => 'available']);
                }

                $parkingRequest->update([
                    'valet_agent_id' => $validated['agent_id'],
                    'status' => $parkingRequest->status === ParkingRequest::STATUS_PENDING
                        ? ParkingRequest::STATUS_ACCEPTED
                        : $parkingRequest->status,
                ]);

                LocationAgent::where('agent_id', $validated['agent_id'])
                    ->where('location_id', $parkingRequest->location_id)
                    ->update(['status' => 'occupied']);

                Log::info("Admin assigned agent {$validated['agent_id']} to request {$id}.");
            });
        } catch (\Exception $e) {
            return back()->withErrors(['assign' => $e->getMessage()]);
        }

        return back()->with('status', 'Valet assigned successfully.');
    }

    public function cancel(Request $request, int $id)
    {
        $reason = $request->string('reason')->toString() ?: 'Canceled by admin.';

        try {
            DB::transaction(function () use ($id, $reason) {
                $parkingRequest = ParkingRequest::lockForUpdate()->findOrFail($id);

                if (! in_array($parkingRequest->status, self::ACTIVE_STATUSES, true)) {
                    throw new \Exception('This request can no longer be canceled.');
                }

                if ($parkingRequest->valet_agent_id) {
                    LocationAgent::where('agent_id', $parkingRequest->valet_agent_id)
                        ->where('location_id', $parkingRequest->location_id)
                        ->update(['status' => 'available']);
                }

                $parkingRequest->update(['status' => ParkingRequest::STATUS_CANCELED]);

                RejectedRequest::create([
                    'user_id' => $parkingRequest->user_id,
                    'plate_number' => $parkingRequest->car->plate_number ?? null,
                    'cartypeable_type' => $parkingRequest->car ? get_class($parkingRequest->car) : null,
                    'cartypeable_id' => $parkingRequest->car_id,
                    'rejection_reason' => $reason,
                ]);

                Log::info("Admin canceled request {$id}. Reason: {$reason}");
            });
        } catch (\Exception $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return back()->with('status', 'Request canceled.');
    }
}
