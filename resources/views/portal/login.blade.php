<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trainee Portal Login · {{ setting('site_name', 'Latechify') }}</title>
    <link rel="icon" href="{{ media_url(setting('logo'), 'favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen text-gray-900 antialiased">
    @php
        // Inline lucide SVGs so the page stays self-contained (no JS needed to render icons).
        $svg = fn ($paths, $cls = 'w-4 h-4') => '<svg class="'.$cls.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'.$paths.'</svg>';
        $iMail = '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>';
        $iLock = '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>';
        $iCap  = '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>';
        $iLogin = '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/>';
        $iArrow = '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>';
    @endphp

    <div class="relative min-h-screen flex items-center justify-center p-4 sm:p-6 overflow-hidden bg-gradient-to-br from-primary via-[#1a3ad4] to-[#0a1550]">
        {{-- Decorative backdrop --}}
        <div class="absolute -top-28 -left-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-24 w-[30rem] h-[30rem] rounded-full bg-primary-0/25 blur-3xl"></div>
        <div class="absolute inset-0 dot-pattern-white opacity-[0.12]"></div>

        <div class="relative w-full max-w-md">
            {{-- Logo + eyebrow on the branded backdrop --}}
            <div class="flex flex-col items-center mb-6">
                <img src="{{ media_url(setting('logo_white'), 'assets/imgs/latechify_logo_white.png') }}" class="h-11 sm:h-12 w-auto" alt="{{ setting('site_name') }}">
                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/20 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur-sm">
                    {!! $svg($iCap, 'w-3.5 h-3.5') !!} Trainee Portal
                </span>
            </div>

            {{-- Card --}}
            <div class="rounded-2xl bg-white/95 shadow-2xl ring-1 ring-black/5 backdrop-blur p-7 sm:p-9">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">Sign in to continue your training.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('portal.login.attempt') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid place-items-center pl-3.5 text-gray-400">{!! $svg($iMail) !!}</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com"
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid place-items-center pl-3.5 text-gray-400">{!! $svg($iLock) !!}</span>
                            <input id="pw" type="password" name="password" required placeholder="••••••••"
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-11 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <button type="button" onclick="togglePw(this)" tabindex="-1" aria-label="Show password"
                                    class="absolute inset-y-0 right-0 grid place-items-center pr-3.5 text-gray-400 hover:text-gray-600">
                                <svg data-eye class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg data-eyeoff class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary focus:ring-primary/30"> Keep me signed in
                        </label>
                        <a href="{{ route('contact') }}" class="text-sm text-primary hover:underline">Need help?</a>
                    </div>
                    <button type="submit" class="btn-shine w-full rounded-lg bg-gradient-to-r from-primary to-[#1a3ad4] hover:from-[#1a3ad4] hover:to-primary px-6 py-3 font-semibold text-white shadow-md shadow-primary/20 transition-all inline-flex items-center justify-center gap-2">
                        {!! $svg($iLogin) !!} Sign in
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-gray-100 text-center text-sm text-muted-foreground">
                    Don’t have an account yet? <a href="{{ route('checkout') }}" class="text-primary font-medium hover:underline">Enrol at checkout</a> — your portal opens once your payment is confirmed.
                </div>
            </div>

            <p class="mt-6 text-center text-sm">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-white/70 hover:text-white transition-colors">{!! $svg($iArrow) !!} Back to website</a>
            </p>
        </div>
    </div>

    <script>
        function togglePw(btn) {
            var input = document.getElementById('pw');
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.querySelector('[data-eye]').classList.toggle('hidden', !showing);
            btn.querySelector('[data-eyeoff]').classList.toggle('hidden', showing);
        }
    </script>
</body>
</html>
