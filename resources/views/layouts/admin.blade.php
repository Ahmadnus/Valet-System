<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Valet Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#05070d] font-sans antialiased text-slate-200 selection:bg-cyan-500/30">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute top-1/3 -right-40 h-96 w-96 rounded-full bg-amber-400/5 blur-[120px]"></div>
    </div>

    <div class="relative flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-r border-white/5 bg-white/[0.015] px-4 py-6 lg:flex lg:flex-col">
            <div class="mb-8 flex items-center gap-2.5 px-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 shadow-[0_0_20px_rgba(34,211,238,0.4)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-navy-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4-10 4 10M9.5 13h5"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold tracking-wide text-white">VALET<span class="text-cyan-400">.</span></p>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Admin Console</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h4v8H3zM10 3h4v18h-4zM17 8h4v13h-4z"/></svg>
                    Overview
                </a>
                <a href="{{ route('admin.requests.index') }}" class="nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7a2 2 0 012-2h2a2 2 0 012 2v10M5 21h14a2 2 0 002-2v-5.5a1 1 0 00-.4-.8l-2.87-2.15M5 21a2 2 0 01-2-2v-5.5a1 1 0 01.4-.8l2.87-2.15M5 21V9m14 12V9"/></svg>
                    Parking Requests
                </a>
                <a href="{{ route('admin.agents.index') }}" class="nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1a4 4 0 10-4-4 4 4 0 004 4zm6-6a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Valets & Agents
                </a>
                <a href="{{ route('admin.locations.index') }}" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Locations
                </a>
                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5-1.343 1.5-3 1.5m0-6V6m0 1.5V15m0 1.5V15m-8 0a8 8 0 1016 0 8 8 0 00-16 0z"/></svg>
                    Payments
                </a>
            </nav>

            <div class="mt-6 border-t border-white/5 pt-4">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-full justify-start text-rose-400 hover:text-rose-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-white/5 bg-white/[0.015] px-6 py-4">
                <div>
                    <h1 class="text-lg font-semibold text-white">@yield('title', 'Overview')</h1>
                    <p class="text-xs text-slate-500">{{ now()->format('l, d M Y · H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge badge-active">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        Live
                    </span>
                    <div class="flex items-center gap-2 rounded-full border border-white/10 bg-white/5 py-1 pl-1 pr-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-amber-300 to-amber-500 text-xs font-bold text-navy-950">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="text-sm text-slate-200">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
