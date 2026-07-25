<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MemorAI — Articoli · Memoria · Devozione')</title>
    <meta name="description" content="@yield('meta_description', 'MemorAI: articoli memoriali e devozionali di fattura artigianale — trigesimali, rosari e corone, photoceramiche.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-bianco text-testo antialiased">

    {{-- ============ TOPBAR (scorre via) ============ --}}
    <div class="bg-caffe text-bianco/80 text-[11px] tracking-[0.25em] uppercase">
        <div class="mx-auto max-w-7xl px-6 h-9 flex items-center justify-center text-center">
            <span class="font-sans font-light">
                Lavorazione artigianale · Spedizione curata in tutta Italia
            </span>
        </div>
    </div>

    {{-- ============ MASTHEAD EDITORIALE (logo, scorre via) ============ --}}
    <header class="bg-bianco">
        <div class="mx-auto max-w-7xl px-6 py-8 text-center">
            <a href="{{ url('/') }}" class="inline-block">
                <span class="block font-serif text-oro text-4xl md:text-5xl font-medium tracking-[0.35em] leading-none pl-[0.35em]">
                    MemorAI
                </span>
                <span class="mt-3 block font-sans text-[10px] md:text-[11px] tracking-[0.4em] text-testo-soft uppercase pl-[0.4em]">
                    Articoli · Memoria · Devozione
                </span>
            </a>
        </div>
    </header>

    {{-- ============ BARRA DI NAVIGAZIONE (sticky) ============ --}}
    {{-- La parte superiore del menu resta sempre visibile durante lo scroll. --}}
    <nav aria-label="Navigazione principale"
         class="sticky top-0 z-50 bg-bianco/95 backdrop-blur-sm border-y-2 border-caffe">
        <div class="mx-auto max-w-7xl px-6 relative flex items-center justify-center min-h-[3.25rem] py-2">

            {{-- menu laterale: qui solo il bottone (sotto lg) --}}
            <x-nav-drawer-bottone />

            {{-- brand compatto (sinistra) --}}
            <a href="{{ url('/') }}"
               class="absolute left-6 hidden lg:block font-serif text-oro text-lg tracking-[0.2em] pl-[0.2em]">
                MemorAI
            </a>

            {{-- voci principali (centro): sotto lg stanno nel drawer --}}
            <ul class="hidden lg:flex flex-wrap items-center justify-center gap-x-7 gap-y-1
                       font-sans text-[12px] tracking-[0.16em] uppercase">
                @foreach (config('vetrina.voci', []) as ['etichetta' => $voce, 'href' => $href])
                    <li>
                        <a href="{{ $href }}"
                           class="relative inline-block text-testo hover:text-oro-scuro transition-colors duration-300
                                  after:content-[''] after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0
                                  after:bg-oro after:transition-all after:duration-300 hover:after:w-full">
                            {{ $voce }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- sotto lg il centro della barra porta il marchio --}}
            <a href="{{ url('/') }}"
               class="lg:hidden font-serif text-oro text-xl tracking-[0.28em] pl-[0.28em]">
                MemorAI
            </a>

            {{-- icone (destra) --}}
            <div class="absolute right-6 hidden sm:flex items-center gap-4 text-caffe">
                <button type="button" aria-label="Cerca" class="hover:text-oro-scuro transition-colors duration-300">
                    <x-icon.search class="w-5 h-5" />
                </button>
                <a href="#" aria-label="Il mio account" class="hover:text-oro-scuro transition-colors duration-300">
                    <x-icon.account class="w-5 h-5" />
                </a>
                <a href="#" aria-label="Carrello" class="hover:text-oro-scuro transition-colors duration-300">
                    <x-icon.cart class="w-5 h-5" />
                </a>
            </div>
        </div>
    </nav>

    {{-- Pannello del menu laterale: fuori dalla barra, che con backdrop-filter
         farebbe da containing block e lo terrebbe incastrato dentro di sé. --}}
    <x-nav-drawer />

    {{-- ============ HERO A TUTTA LARGHEZZA (opzionale) ============ --}}
    @yield('hero')

    {{-- ============ CORPO: sidebar categorie + contenuto ============ --}}
    <div class="flex-1 mx-auto w-full max-w-7xl px-6">
        <div class="flex gap-10">

            {{-- accesso diretto alle categorie (sinistra, sticky) --}}
            <aside class="hidden lg:block w-56 shrink-0 py-12">
                <div class="sticky top-[4.5rem]">
                    <x-category-sidebar />
                </div>
            </aside>

            {{-- contenuto della pagina --}}
            <main class="flex-1 min-w-0 py-12">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ============ FOOTER ============ --}}
    <footer class="bg-caffe text-bianco/70">
        <div class="mx-auto max-w-7xl px-6 py-16
                    grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">

            <div>
                <span class="font-serif text-oro text-2xl font-medium tracking-[0.3em] pl-[0.3em]">MemorAI</span>
                <p class="mt-5 font-sans font-light text-[13px] leading-relaxed text-bianco/60">
                    Oggetti di memoria e devozione, pensati e realizzati con cura artigianale.
                    Bellezza che dura, da tramandare.
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Collezioni</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">Articoli trigesimali</a></li>
                    <li><a href="#" class="footer-link">Rosari e corone</a></li>
                    <li><a href="#" class="footer-link">Croci</a></li>
                    <li><a href="#" class="footer-link">Photoceramiche</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Servizi</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">Personalizza il tuo ricordino</a></li>
                    <li><a href="#" class="footer-link">Stampa foto</a></li>
                    <li><a href="#" class="footer-link">QR Memoria</a></li>
                    <li><a href="#" class="footer-link">Onoranze funebri (B2B)</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Assistenza</h4>
                <ul class="footer-list">
                    <li><a href="#" class="footer-link">Contatti</a></li>
                    <li><a href="#" class="footer-link">Spedizioni e resi</a></li>
                    <li><a href="#" class="footer-link">Domande frequenti</a></li>
                    <li><a href="#" class="footer-link">Assistenza personalizzazione</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-bianco/10">
            <div class="mx-auto max-w-7xl px-6 py-6
                        flex flex-col sm:flex-row items-center justify-between gap-3
                        font-sans text-[11px] tracking-[0.15em] uppercase text-bianco/40">
                <span>© {{ date('Y') }} MemorAI — Ecosistema kerachrom.it</span>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-oro transition-colors">Privacy</a>
                    <a href="#" class="hover:text-oro transition-colors">Cookie</a>
                    <a href="#" class="hover:text-oro transition-colors">Termini</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
