@extends('layouts.admin')

@section('title', 'Locations')

@section('content')
<div class="space-y-6" x-data="{ showCreate: false, editing: null }">

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

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $locations->total() }} locations configured</p>
        <button type="button" @click="showCreate = true" class="btn-primary">
            + New Location
        </button>
    </div>

    {{-- Locations grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($locations as $location)
            @php
                $occupancy = $location->parking_slots_count > 0
                    ? round(($location->occupied_slots_count / $location->parking_slots_count) * 100)
                    : 0;
            @endphp
            <div class="glass-card space-y-3 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base font-semibold text-white">{{ $location->name ?? 'Location #'.$location->id }}</p>
                        <p class="text-xs text-slate-500">Host: {{ $location->host->name ?? 'Unassigned' }}</p>
                    </div>
                    <span class="badge badge-gold">${{ number_format($location->cost, 2) }}/hr</span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs text-slate-400">
                    <div>
                        <p class="text-slate-500">Coordinates</p>
                        <p class="text-slate-300">{{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Valets Assigned</p>
                        <p class="text-slate-300">{{ $location->agents_count }}</p>
                    </div>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $location->occupied_slots_count }}/{{ $location->parking_slots_count }} slots occupied</span>
                        <span>{{ $occupancy }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-cyan-300" style="width: {{ $occupancy }}%"></div>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-white/5 pt-3">
                    <button type="button" class="btn-ghost flex-1 !py-1.5 text-xs"
                        @click="editing = {
                            id: {{ $location->id }},
                            name_en: {{ Js::from($location->translate('en')->name ?? '') }},
                            name_ar: {{ Js::from($location->translate('ar')->name ?? '') }},
                            latitude: {{ $location->latitude }},
                            longitude: {{ $location->longitude }},
                            cost: {{ $location->cost }},
                            host_id: {{ $location->host_id ?? 'null' }}
                        }">
                        Edit
                    </button>
                    <form method="POST" action="{{ route('admin.locations.destroy', $location->id) }}"
                          onsubmit="return confirm('Delete this location? This cannot be undone.');" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-lg border border-rose-400/20 bg-rose-400/10 px-3 py-1.5 text-xs font-medium text-rose-300 transition hover:bg-rose-400/20">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="glass-card col-span-full py-10 text-center text-slate-500">No locations yet. Create your first one.</div>
        @endforelse
    </div>

    @if ($locations->hasPages())
        <div class="glass-card px-4 py-3">
            {{ $locations->links() }}
        </div>
    @endif

    {{-- Create modal --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="glass-card w-full max-w-lg p-6" @click.outside="showCreate = false">
            <h3 class="mb-4 text-lg font-semibold text-white">New Location</h3>
            <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Name (EN)</label>
                        <input type="text" name="name_en" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Name (AR)</label>
                        <input type="text" name="name_ar" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Latitude</label>
                        <input type="number" step="any" name="latitude" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Longitude</label>
                        <input type="number" step="any" name="longitude" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Hourly Cost ($)</label>
                        <input type="number" step="0.01" name="cost" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Parking Slots</label>
                        <input type="number" min="0" max="200" name="number_of_slots" value="6" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Host</label>
                    <select name="host_id" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                        <option value="">Select a host…</option>
                        @forelse ($availableHosts as $host)
                            <option value="{{ $host->id }}">{{ $host->name }}</option>
                        @empty
                            <option value="" disabled>No unassigned hosts available</option>
                        @endforelse
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreate = false" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary">Create Location</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="glass-card w-full max-w-lg p-6" @click.outside="editing = null" x-show="editing" x-cloak>
            <h3 class="mb-4 text-lg font-semibold text-white">Edit Location</h3>
            <template x-if="editing">
                <form method="POST" :action="'/admin/locations/' + editing.id" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Name (EN)</label>
                            <input type="text" name="name_en" x-model="editing.name_en" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Name (AR)</label>
                            <input type="text" name="name_ar" x-model="editing.name_ar" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Latitude</label>
                            <input type="number" step="any" name="latitude" x-model="editing.latitude" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Longitude</label>
                            <input type="number" step="any" name="longitude" x-model="editing.longitude" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Hourly Cost ($)</label>
                        <input type="number" step="0.01" name="cost" x-model="editing.cost" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Host</label>
                        <select name="host_id" x-model="editing.host_id" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white outline-none ring-cyan-400/40 focus:ring-2">
                            <option :value="null">Unassigned</option>
                            @foreach ($hosts as $host)
                                <option value="{{ $host->id }}">{{ $host->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editing = null" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
