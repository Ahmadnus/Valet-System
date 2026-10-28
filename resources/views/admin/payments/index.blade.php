@extends('layouts.admin')

@section('title', 'Payments')

@php
    $statusStyles = [
        'pending' => 'badge-pending',
        'completed' => 'badge-success',
        'failed' => 'badge-danger',
    ];
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

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Revenue</span>
            <p class="text-3xl font-semibold text-white">${{ number_format($stats['total_revenue'], 2) }}</p>
        </div>
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Revenue Today</span>
            <p class="text-3xl font-semibold text-white">${{ number_format($stats['revenue_today'], 2) }}</p>
        </div>
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Pending</span>
            <p class="text-3xl font-semibold text-white">{{ $stats['pending'] }}</p>
        </div>
        <div class="stat-card">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Failed</span>
            <p class="text-3xl font-semibold text-white">{{ $stats['failed'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.payments.index') }}" class="glass-card flex flex-wrap items-end gap-3 p-4">
        <div class="flex-1 min-w-[200px]">
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Customer name"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder:text-slate-600 outline-none ring-cyan-400/40 focus:ring-2">
        </div>
        <div class="min-w-[160px]">
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Status</label>
            <select name="status" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                <option value="">All</option>
                @foreach (['pending','completed','failed'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px]">
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Method</label>
            <select name="method" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                <option value="">All</option>
                @foreach (['cash','credit_card','paypal'] as $m)
                    <option value="{{ $m }}" @selected(($filters['method'] ?? '') === $m)>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('admin.payments.index') }}" class="btn-ghost">Reset</a>
    </form>

    {{-- Table --}}
    <div class="table-shell">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Request</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="text-slate-500">#{{ $payment->id }}</td>
                            <td>{{ $payment->request->user->name ?? '—' }}</td>
                            <td>
                                @if ($payment->request)
                                    <a href="{{ route('admin.requests.show', $payment->request_id) }}" class="text-cyan-300 hover:underline">
                                        #{{ $payment->request_id }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-slate-200">${{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="text-slate-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td>
                                <span class="badge {{ $statusStyles[$payment->status] ?? 'badge-pending' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="text-slate-500">{{ $payment->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.payments.status', $payment->id) }}" class="inline">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()"
                                        class="rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-xs text-white outline-none">
                                        @foreach (['pending','completed','failed'] as $s)
                                            <option value="{{ $s }}" @selected($payment->status === $s)>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-500">No payments match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="border-t border-white/5 px-4 py-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
