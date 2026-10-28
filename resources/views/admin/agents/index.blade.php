@extends('layouts.admin')

@section('title', 'Valets & Agents')

@php
    $agentStatusStyles = [
        'available' => 'badge-success',
        'occupied' => 'badge-pending',
        'in_way' => 'badge-active',
    ];
@endphp

@section('content')
<div class="space-y-6" x-data="{ openAssign: null }">

    @if (session('status'))
        <div class="rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-4 py-2.5 text-sm text-emerald-300">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-rose-400/20 bg-rose-400/10 px-4 py-2.5 text-sm text-rose-300">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Valets</span>
            <p class="text-3xl font-semibold text-white">{{ $stats['total_agents'] }}</p>
        </div>
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">On Duty</span>
            <p class="text-3xl font-semibold text-white">{{ $stats['on_duty'] }}</p>
        </div>
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Available Now</span>
            <p class="text-3xl font-semibold text-white">{{ $stats['available_now'] }}</p>
        </div>
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Avg. Rating</span>
            <p class="text-3xl font-semibold text-white">{{ $stats['avg_rating'] ?: '—' }} <span class="text-sm text-amber-300">★</span></p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.agents.index') }}" class="glass-card flex flex-wrap items-end gap-3 p-4">
        <div class="flex-1 min-w-[200px]">
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Valet name"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder:text-slate-600 outline-none ring-cyan-400/40 focus:ring-2">
        </div>
        <div class="min-w-[180px]">
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Location</label>
            <select name="location_id" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                <option value="">All locations</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(($filters['location_id'] ?? '') == $location->id)>
                        Location #{{ $location->id }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('admin.agents.index') }}" class="btn-ghost">Reset</a>
    </form>

    {{-- Agents table --}}
    <div class="table-shell">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Valet</th>
                        <th>Locations</th>
                        <th>Rating</th>
                        <th>Completed</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agents as $agent)
                        @php $rows = $assignments->get($agent->id, collect()); @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400/30 to-cyan-600/30 text-xs font-semibold text-cyan-200">
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-slate-200">{{ $agent->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $agent->phone_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @forelse ($rows as $row)
                                    <div class="mb-1 flex items-center gap-2 last:mb-0">
                                        <span class="text-xs text-slate-400">Location #{{ $row->location_id }}</span>
                                        <form method="POST" action="{{ route('admin.agents.status', $agent->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="location_id" value="{{ $row->location_id }}">
                                            <select name="status" onchange="this.form.submit()"
                                                class="badge {{ $agentStatusStyles[$row->status] ?? 'badge-pending' }} border-0 bg-transparent focus:outline-none cursor-pointer">
                                                @foreach (['available','occupied','in_way'] as $s)
                                                    <option value="{{ $s }}" @selected($row->status === $s) class="bg-navy-800 text-slate-200">
                                                        {{ ucfirst(str_replace('_',' ',$s)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                        <form method="POST" action="{{ route('admin.agents.unassign', $agent->id) }}"
                                              onsubmit="return confirm('Remove this valet from the location?');" class="inline">
                                            @csrf
                                            <input type="hidden" name="location_id" value="{{ $row->location_id }}">
                                            <button type="submit" class="text-rose-400/70 hover:text-rose-300" title="Unassign">✕</button>
                                        </form>
                                    </div>
                                @empty
                                    <span class="text-xs text-slate-600">Not assigned to any location</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($agent->ratings_received_avg_rating)
                                    <span class="text-amber-300">★ {{ number_format($agent->ratings_received_avg_rating, 1) }}</span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="text-slate-300">{{ $agent->completed_deliveries }}</td>
                            <td class="relative text-right">
                                <button type="button" @click="openAssign = openAssign === {{ $agent->id }} ? null : {{ $agent->id }}"
                                        class="btn-ghost !px-3 !py-1.5 text-xs">
                                    + Assign
                                </button>
                                <div x-show="openAssign === {{ $agent->id }}" x-cloak
                                     class="absolute z-10 mt-2 w-56 rounded-lg border border-white/10 bg-navy-800 p-3 shadow-xl">
                                    <form method="POST" action="{{ route('admin.agents.assign', $agent->id) }}" class="space-y-2">
                                        @csrf
                                        <select name="location_id" required class="w-full rounded-lg border border-white/10 bg-white/5 px-2 py-1.5 text-xs text-white outline-none">
                                            <option value="">Select location…</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location->id }}">Location #{{ $location->id }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-primary w-full !py-1.5 text-xs">Assign</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-500">No valets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($agents->hasPages())
            <div class="border-t border-white/5 px-4 py-3">
                {{ $agents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
