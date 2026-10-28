@extends('layouts.admin')

@section('title', 'Request #' . $parkingRequest->id)

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
    $activeStatuses = ['pending', 'accepted', 'in_progress', 'preparing', 'delivering'];
@endphp

@section('content')
<div class="space-y-6">

    <a href="{{ route('admin.requests.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to requests
    </a>

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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Summary --}}
        <div class="glass-card space-y-4 p-6 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Request #{{ $parkingRequest->id }}</h2>
                <span class="badge {{ $statusStyles[$parkingRequest->status] ?? 'badge-pending' }}">
                    {{ ucfirst(str_replace('_', ' ', $parkingRequest->status)) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Customer</p>
                    <p class="text-slate-200">{{ $parkingRequest->user->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Plate Number</p>
                    <p class="text-slate-200">{{ $parkingRequest->car->plate_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Location</p>
                    <p class="text-slate-200">{{ optional($parkingRequest->location)->id ? 'Location #'.$parkingRequest->location->id : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Parking Slot</p>
                    <p class="text-slate-200">{{ optional($parkingRequest->parkingSlot)->slot_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Assigned Valet</p>
                    <p class="text-slate-200">{{ $parkingRequest->valetAgent->name ?? 'Unassigned' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Requested</p>
                    <p class="text-slate-200">{{ $parkingRequest->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">In Progress Since</p>
                    <p class="text-slate-200">{{ optional($parkingRequest->in_progress_time)->format('d M Y, H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Completed</p>
                    <p class="text-slate-200">{{ optional($parkingRequest->completed_time)->format('d M Y, H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Fee Waived</p>
                    <p class="text-slate-200">{{ $parkingRequest->fee_waived ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            @if ($parkingRequest->payment)
                <div class="border-t border-white/5 pt-4">
                    <p class="mb-2 text-xs uppercase tracking-wider text-slate-500">Payment</p>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-slate-200">${{ number_format((float) $parkingRequest->payment->amount, 2) }}</span>
                        <span class="badge {{ $parkingRequest->payment->status === 'completed' ? 'badge-success' : 'badge-pending' }}">
                            {{ ucfirst($parkingRequest->payment->status) }}
                        </span>
                        <span class="text-slate-500">via {{ $parkingRequest->payment->payment_method }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="space-y-6">
            @if (in_array($parkingRequest->status, $activeStatuses, true))
                <div class="glass-card space-y-3 p-5">
                    <h3 class="text-sm font-semibold text-white">Assign / Reassign Valet</h3>
                    <form method="POST" action="{{ route('admin.requests.assign', $parkingRequest->id) }}" class="space-y-3">
                        @csrf
                        <select name="agent_id" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                            <option value="">Select an available valet…</option>
                            @foreach ($availableAgents as $entry)
                                <option value="{{ $entry->agent_id }}">{{ $entry->agent->name ?? 'Valet #'.$entry->agent_id }}</option>
                            @endforeach
                        </select>
                        @if ($availableAgents->isEmpty())
                            <p class="text-xs text-amber-300/80">No available valets at this location right now.</p>
                        @endif
                        <button type="submit" class="btn-primary w-full" @disabled($availableAgents->isEmpty())>Assign Valet</button>
                    </form>
                </div>

                <div class="glass-card space-y-3 p-5">
                    <h3 class="text-sm font-semibold text-white">Cancel Request</h3>
                    <form method="POST" action="{{ route('admin.requests.cancel', $parkingRequest->id) }}" class="space-y-3"
                          onsubmit="return confirm('Cancel this parking request?');">
                        @csrf
                        <textarea name="reason" rows="2" placeholder="Reason (optional)"
                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder:text-slate-600 outline-none ring-rose-400/40 focus:ring-2"></textarea>
                        <button type="submit" class="w-full rounded-lg border border-rose-400/20 bg-rose-400/10 px-4 py-2 text-sm font-medium text-rose-300 transition hover:bg-rose-400/20">
                            Cancel Request
                        </button>
                    </form>
                </div>
            @else
                <div class="glass-card p-5 text-sm text-slate-500">
                    This request is {{ str_replace('_', ' ', $parkingRequest->status) }} — no further actions available.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
