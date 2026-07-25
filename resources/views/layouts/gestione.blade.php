<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard operativa') — MemorAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-bianco text-testo antialiased">

    <header class="bg-caffe text-bianco">
        <div class="mx-auto max-w-6xl px-6 py-4 flex flex-wrap items-center gap-x-8 gap-y-3">
            <a href="{{ route('gestione.pannello') }}"
               class="font-serif text-oro text-2xl tracking-[0.28em] pl-[0.28em]">MemorAI</a>
            <span class="font-sans text-[10px] tracking-[0.3em] uppercase text-bianco/50">
                Dashboard operativa
            </span>

            @if (session('gestione_accesso'))
                <nav class="flex items-center gap-6 font-sans text-[12px] tracking-[0.16em] uppercase">
                    @foreach ([
                        ['Pannello', route('gestione.pannello')],
                        ['Slide home', route('gestione.slide.index')],
                        ['Pratiche', route('gestione.pratiche')],
                    ] as [$voce, $href])
                        <a href="{{ $href }}"
                           class="{{ url()->current() === $href ? 'text-oro' : 'text-bianco/70 hover:text-oro' }} transition-colors">
                            {{ $voce }}
                        </a>
                    @endforeach
                </nav>

                <div class="ml-auto flex items-center gap-5 font-sans text-[12px] tracking-[0.16em] uppercase">
                    <a href="{{ url('/') }}" target="_blank"
                       class="text-bianco/70 hover:text-oro transition-colors">Vedi la vetrina ↗</a>
                    <form method="POST" action="{{ route('gestione.esci') }}">
                        @csrf
                        <button type="submit" class="text-bianco/70 hover:text-oro transition-colors cursor-pointer">
                            Esci
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </header>

    <main class="flex-1 mx-auto w-full max-w-6xl px-6 py-10">
        @if (session('ok'))
            <div role="status" class="mb-8 border-2 border-caffe bg-panna px-5 py-3 font-sans text-[14px]">
                {{ session('ok') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t-2 border-caffe/15">
        <div class="mx-auto max-w-6xl px-6 py-6 font-sans text-[11px] tracking-[0.15em] uppercase text-testo-soft">
            Area riservata · MemorAI
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
