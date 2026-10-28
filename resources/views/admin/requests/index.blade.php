@extends('layouts.admin')

@section('title', 'Parking Requests')

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
    $statusOptions = ['pending', 'accepted', 'in_progress', 'preparing', 'delivering', 'completed', 'canceled'];
@endphp

@section('content')
<div class="space-y-6">

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

    {{-- Status summary chips --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.requests.index') }}"
           class="badge {{ empty($filters['status']) ? 'badge-active' : 'bg-white/5 text-slate-400 ring-1 ring-inset ring-white/10' }}">
            All ({{ $statusCounts->sum() }})
        </a>
        @foreach ($statusOptions as $option)
            <a href="{{ route('admin.requests.index', array_merge($filters, ['status' => $option])) }}"
               class="badge {{ ($filters['status'] ?? '') === $option ? $statusStyles[$option] : 'bg-white/5 text-slate-400 ring-1 ring-inset ring-white/10' }}">
                {{ ucfirst(str_replace('_', ' ', $option)) }} ({{ $statusCounts[$option] ?? 0 }})
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.requests.index') }}" class="glass-card flex flex-wrap items-end gap-3 p-4">
        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">

        <div class="flex-1 min-w-[200px]">
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Customer name or plate number"
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
        <a href="{{ route('admin.requests.index') }}" class="btn-ghost">Reset</a>
    </form>

    {{-- Table --}}
    <div class="table-shell">
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
                        <th>Requested</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="text-slate-500">#{{ $req->id }}</td>
                            <td>{{ $req->user->name ?? '—' }}</td>
                            <td>{{ $req->car->plate_number ?? '—' }}</td>
                            <td>{{ optional($req->location)->id ? 'Location #'.$req->location->id : '—' }}</td>
                            <td>{{ $req->valetAgent->name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="badge {{ $statusStyles[$req->status] ?? 'badge-pending' }}">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </td>
                            <td class="text-slate-500">{{ $req->created_at->diffForHumans() }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.requests.show', $req->id) }}" class="btn-ghost !px-3 !py-1.5 text-xs">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-500">No requests match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="border-t border-white/5 px-4 py-3">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
