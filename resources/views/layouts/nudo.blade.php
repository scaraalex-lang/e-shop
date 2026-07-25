<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MemorAI')</title>
    <meta name="description" content="@yield('meta_description', '')">

    {{-- Anteprime per WhatsApp e Facebook --}}
    @stack('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{--
    Pagina nuda: nessuna barra, nessun menu, nessun footer di negozio.

    Serve alle pagine che si aprono da un link ricevuto — un necrologio, una
    bozza da approvare — dove chi arriva non sta comprando niente e la vetrina
    intorno sarebbe fuori tono. Resta solo la firma in fondo, discreta.
--}}
<body class="min-h-screen bg-panna text-testo antialiased flex flex-col">

    <main class="flex-1 flex items-center justify-center px-4 py-10 sm:py-16">
        @yield('content')
    </main>

    <footer class="pb-10 text-center">
        <a href="{{ url('/') }}" class="inline-block">
            <span class="block font-serif text-oro/70 text-lg tracking-[0.3em] leading-none pl-[0.3em]
                         hover:text-oro transition-colors duration-300">
                MemorAI
            </span>
        </a>
    </footer>

    @stack('scripts')
</body>
</html>
