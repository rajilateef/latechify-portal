@props(['title' => null, 'metaDescription' => null])

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <script>document.documentElement.classList.add('js')</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' | '.setting('site_name', 'Latechify Digital Hub') : setting('meta_title', 'Latechify Digital Hub') }}</title>
    <meta name="description" content="{{ $metaDescription ?? setting('meta_description') }}">

    <link rel="icon" href="{{ media_url(setting('logo'), 'favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;700&family=Satisfy&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <div id="scroll-progress"></div>
    @include('layouts.partials.header')

    <main>
        {{ $slot }}
    </main>

    @include('layouts.partials.footer')
    @include('layouts.partials.whatsapp')
    @include('layouts.partials.floating-advert')

    {{ $popup ?? '' }}

    @livewireScripts
    @stack('scripts')
    <script>
        window.addEventListener('load', () => window.renderIcons && window.renderIcons());
    </script>
</body>
</html>
