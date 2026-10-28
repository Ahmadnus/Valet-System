@extends('layouts.admin')

@section('title', 'Overview')

@section('content')
<div class="space-y-6" x-data="{ refreshedAt: new Date().toLocaleTimeString() }">

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Parked Cars</span>
                <span class="badge badge-active">Live</span>
            </div>
            <p class="text-3xl font-semibold text-white">{{ $stats['parked_cars'] }}</p>
            <p class="text-xs text-slate-500">Slots currently occupied</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Active Requests</span>
                <span class="badge badge-pending">{{ $stats['pending_requests'] }} pending</span>
            </div>
            <p class="text-3xl font-semibold text-white">{{ $stats['active_requests'] }}</p>
            <p class="text-xs text-slate-500">Accepted → delivering</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Valets on Duty</span>
                <span class="badge badge-success">Active</span>
            </div>
            <p class="text-3xl font-semibold text-white">{{ $stats['valets_on_duty'] }}</p>
            <p class="text-xs text-slate-500">Assigned to a location right now</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Revenue Today</span>
                <span class="badge badge-gold">${{ number_format($stats['revenue_today'], 2) }}</span>
            </div>
            <p class="text-3xl font-semibold text-white">{{ $stats['completed_today'] }}</p>
            <p class="text-xs text-slate-500">Requests completed today</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Recent requests table --}}
        <div class="table-shell xl:col-span-2">
            <div class="flex items-center justify-between border-b border-white/5 px-4 py-3">
                <h2 class="text-sm font-semibold text-white">Recent Parking Requests</h2>
                <span class="text-xs text-slate-500" x-text="'Updated ' + refreshedAt"></span>
            </div>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Location</th>
                            <th>Valet</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentRequests as $request)
                            <tr>
                                <td class="text-slate-500">#{{ $request->id }}</td>
                                <td>{{ $request->user->name ?? '—' }}</td>
                                <td>{{ $request->car->plate_number ?? $request->car->model ?? '—' }}</td>
                                <td>{{ optional($request->location)->id ? 'Location #'.$request->location->id : '—' }}</td>
                                <td>{{ $request->valetAgent->name ?? 'Unassigned' }}</td>
                                <td>
                                    @php
                                        $statusStyles = [
                                            'pending' => 'badge-pending',
                                            'accepted' => 'badge-active',
                                            'in_progress' => 'badge-active',
                                            'preparing' => 'badge-active',
                                            'delivering' => 'badge-active',
                                            'completed' => 'badge-success',
                                            'canceled' => 'badge-danger',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusStyles[$request->status] ?? 'badge-pending' }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">No parking requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Valets on duty --}}
        <div class="table-shell">
            <div class="flex items-center justify-between border-b border-white/5 px-4 py-3">
                <h2 class="text-sm font-semibold text-white">Valets on Duty</h2>
            </div>
            <div class="divide-y divide-white/5">
                @forelse ($valetsOnDuty as $entry)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-cyan-600/30 text-xs font-semibold text-cyan-200">
                                {{ strtoupper(substr($entry->agent->name ?? 'V', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm text-slate-200">{{ $entry->agent->name ?? 'Valet #'.$entry->agent_id }}</p>
                                <p class="text-xs text-slate-500">{{ optional($entry->location)->id ? 'Location #'.$entry->location->id : '—' }}</p>
                            </div>
                        </div>
                        @php
                            $agentStatusStyles = [
                                'available' => 'badge-success',
                                'occupied' => 'badge-pending',
                                'in_way' => 'badge-active',
                            ];
                        @endphp
                        <span class="badge {{ $agentStatusStyles[$entry->status] ?? 'badge-pending' }}">
                            {{ ucfirst(str_replace('_', ' ', $entry->status)) }}
                        </span>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-slate-500">No valets assigned right now.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Locations occupancy --}}
    <div class="table-shell">
        <div class="flex items-center justify-between border-b border-white/5 px-4 py-3">
            <h2 class="text-sm font-semibold text-white">Locations Occupancy</h2>
        </div>
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Occupied Slots</th>
                        <th>Total Slots</th>
                        <th>Occupancy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        @php
                            $occupancy = $location->parking_slots_count > 0
                                ? round(($location->occupied_slots / $location->parking_slots_count) * 100)
                                : 0;
                        @endphp
                        <tr>
                            <td>Location #{{ $location->id }}</td>
                            <td>{{ $location->occupied_slots }}</td>
                            <td>{{ $location->parking_slots_count }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-cyan-300" style="width: {{ $occupancy }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $occupancy }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">No locations configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
