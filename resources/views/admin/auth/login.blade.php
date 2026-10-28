<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — Valet Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#05070d] font-sans text-slate-200">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-amber-400/10 blur-[120px]"></div>
    </div>

    <div class="relative w-full max-w-sm px-6">
        <div class="mb-8 flex flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-cyan-600 shadow-[0_0_30px_rgba(34,211,238,0.45)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-navy-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4-10 4 10M9.5 13h5"/></svg>
            </div>
            <div>
                <p class="text-xl font-semibold tracking-wide text-white">VALET<span class="text-cyan-400">.</span></p>
                <p class="text-xs uppercase tracking-widest text-slate-500">Admin Console</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="glass-card space-y-4 p-6">
            @csrf

            @if ($errors->any())
                <div class="rounded-lg border border-rose-400/20 bg-rose-400/10 px-3 py-2 text-sm text-rose-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Phone number</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required autofocus
                    placeholder="+963939115126"
                    class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-slate-600 outline-none ring-cyan-400/40 focus:ring-2">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Password</label>
                <input type="password" name="password" required
                    placeholder="••••••••"
                    class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-slate-600 outline-none ring-cyan-400/40 focus:ring-2">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-400/40">
                Remember me
            </label>

            <button type="submit" class="btn-primary w-full">Sign in</button>
        </form>

        <p class="mt-6 text-center text-xs text-slate-600">Platform managers only · Valet-System</p>
    </div>
</body>
</html>
