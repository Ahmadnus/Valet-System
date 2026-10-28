<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationAgent;
use App\Models\ParkingSlot;
use App\Models\Payment;
use App\Models\Request as ParkingRequest;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $activeStatuses = [
            ParkingRequest::STATUS_ACCEPTED,
            ParkingRequest::STATUS_IN_PROGRESS,
            ParkingRequest::STATUS_PREPARING,
            ParkingRequest::STATUS_DELIVERING,
        ];

        $stats = [
            'parked_cars' => ParkingSlot::where('status', 'occupied')->count(),
            'active_requests' => ParkingRequest::whereIn('status', $activeStatuses)->count(),
            'pending_requests' => ParkingRequest::where('status', ParkingRequest::STATUS_PENDING)->count(),
            'valets_on_duty' => LocationAgent::whereIn('status', ['available', 'occupied', 'in_way'])->distinct('agent_id')->count('agent_id'),
            'completed_today' => ParkingRequest::where('status', ParkingRequest::STATUS_COMPLETED)
                ->whereDate('completed_time', Carbon::today())
                ->count(),
            'revenue_today' => Payment::where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->get()
                ->sum(fn ($payment) => (float) $payment->amount),
        ];

        $recentRequests = ParkingRequest::with(['user', 'car', 'location', 'valetAgent'])
            ->latest()
            ->limit(8)
            ->get();

        $valetsOnDuty = LocationAgent::with(['location', 'agent'])
            ->whereIn('status', ['available', 'occupied', 'in_way'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $locations = Location::withCount([
            'parkingSlots as occupied_slots' => fn ($q) => $q->where('status', 'occupied'),
        ])->withCount('parkingSlots')->get();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'valetsOnDuty', 'locations'));
    }
}
