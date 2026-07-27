<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MemorAI — Articoli · Memoria · Devozione')</title>
    <meta name="description" content="@yield('meta_description', 'MemorAI: articoli memoriali e devozionali di fattura artigianale — trigesimali, rosari e corone, photoceramiche.')">

    {{-- Anteprime per WhatsApp e Facebook: le pagine che vanno condivise
         spingono qui i propri meta Open Graph. --}}
    @stack('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-bianco text-testo antialiased">

    {{-- ============ TOPBAR (scorre via) ============ --}}
    <div class="bg-caffe text-bianco/80 text-[11px] tracking-[0.25em] uppercase">
        <div class="mx-auto max-w-7xl px-6 h-9 flex items-center justify-center text-center">
            <span class="font-sans font-light">
                {{ \App\Models\ContenutoVetrina::valore('topbar.testo') }}
            </span>
        </div>
    </div>

    {{-- ============ MASTHEAD EDITORIALE (hero con foto, scorre via) ============ --}}
    <header class="bg-bianco bg-[url('/images/hero.jpg')] bg-cover bg-center bg-no-repeat">
        <div class="mx-auto max-w-7xl px-6 py-16 md:py-24 text-center">
            <a href="{{ url('/') }}" class="inline-block">
                <span class="block font-serif text-oro text-4xl md:text-5xl font-medium tracking-[0.35em] leading-none pl-[0.35em]">
                    MemorAI
                </span>
                <span class="mt-3 block font-sans text-[10px] md:text-[11px] tracking-[0.4em] text-testo-soft uppercase pl-[0.4em]">
                    Articoli · Memoria · Devozione
                </span>
            </a>

            <a href="{{ \App\Models\ContenutoVetrina::valore('hero.bottone_primario_url', '#') }}"
               class="mt-9 inline-flex items-center justify-center gap-2 font-sans uppercase text-[12px] tracking-[0.22em]
                      px-8 py-3.5 transition-all duration-300 ease-out cursor-pointer select-none
                      bg-transparent border-2 border-oro text-caffe hover:bg-oro hover:text-bianco">
                {{ \App\Models\ContenutoVetrina::valore('hero.bottone_primario', 'Scopri le collezioni') }}
            </a>
        </div>
    </header>

    {{-- ============ BARRA DI NAVIGAZIONE (sticky) ============ --}}
    {{-- La parte superiore del menu resta sempre visibile durante lo scroll.
         $naveVoci arriva dal View Composer in AppServiceProvider (tabella
         voci_menu, gestibile da /gestione/menu) — condivisa fra il menu
         inline (da md in su) e il pannello mobile a tendina sotto. --}}
    <nav aria-label="Navigazione principale"
         class="sticky top-0 z-50 bg-bianco/95 backdrop-blur-sm border-y-2 border-caffe">
        <div class="mx-auto max-w-7xl px-6 relative flex items-center justify-center min-h-[3.25rem] py-2">

            {{-- brand compatto (sinistra, da md in su) --}}
            <a href="{{ url('/') }}"
               class="absolute left-6 hidden md:block font-serif text-oro text-lg tracking-[0.2em] pl-[0.2em]">
                MemorAI
            </a>

            {{-- apri/chiudi il menu (sinistra, sotto md): deve essere visibile
                 fin da subito, non dietro nessun'altra interazione. --}}
            <button type="button" id="menu-mobile-toggle" aria-controls="menu-mobile" aria-expanded="false"
                    aria-label="Apri il menu"
                    class="absolute left-6 md:hidden text-caffe hover:text-oro-scuro transition-colors duration-300">
                <x-icon.menu class="w-6 h-6" data-menu-icon-open />
                <x-icon.close class="w-6 h-6 hidden" data-menu-icon-close />
            </button>

            {{-- voci principali (centro, da md in su: sotto si passa dal menu a tendina) --}}
            <ul class="hidden md:flex flex-wrap items-center justify-center gap-x-7 gap-y-1
                       font-sans text-[12px] tracking-[0.16em] uppercase">
                @foreach ($naveVoci as $voce)
                    <li>
                        <a href="{{ $voce->url }}"
                           class="relative inline-block text-testo hover:text-oro-scuro transition-colors duration-300
                                  after:content-[''] after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0
                                  after:bg-oro after:transition-all after:duration-300 hover:after:w-full">
                            {{ $voce->etichetta }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- icone (destra): sempre visibili, anche da mobile — accesso e
                 carrello non devono sparire sotto nessuna soglia. --}}
            <div class="absolute right-6 flex items-center gap-4 text-caffe">
                <button type="button" aria-label="Cerca" class="hover:text-oro-scuro transition-colors duration-300">
                    <x-icon.search class="w-5 h-5" />
                </button>
                <a href="{{ auth()->check() ? route('account') : route('login') }}"
                   aria-label="{{ auth()->check() ? 'Il mio account' : 'Accedi' }}"
                   class="hover:text-oro-scuro transition-colors duration-300">
                    <x-icon.account class="w-5 h-5" />
                </a>
                @php
                    // Legge il carrello senza crearlo: aprire la home non deve
                    // lasciare una riga a database per ogni visitatore.
                    $pezziCarrello = app(\Modules\Commerce\Servizi\GestoreCarrello::class)->pezzi();
                @endphp
                <a href="{{ route('carrello') }}"
                   aria-label="Carrello{{ $pezziCarrello ? ": {$pezziCarrello} pezzi" : ' (vuoto)' }}"
                   class="relative hover:text-oro-scuro transition-colors duration-300">
                    <x-icon.cart class="w-5 h-5" />
                    @if ($pezziCarrello > 0)
                        {{-- appoggiato all'angolo dell'icona: più in alto verrebbe
                             tagliato dal bordo superiore della barra --}}
                        <span class="absolute -top-1 -right-2 min-w-[1rem] px-1
                                     bg-oro text-bianco font-sans text-[9px] leading-4
                                     text-center tabular-nums">{{ $pezziCarrello }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- pannello mobile a tendina: le stesse voci, in colonna --}}
        <div id="menu-mobile" class="hidden md:hidden border-t border-caffe/15 bg-bianco">
            <ul class="divide-y divide-caffe/10 font-sans text-[12px] tracking-[0.16em] uppercase">
                @foreach ($naveVoci as $voce)
                    <li>
                        <a href="{{ $voce->url }}"
                           class="block px-6 py-3.5 text-testo hover:text-oro-scuro hover:bg-panna/50 transition-colors duration-300">
                            {{ $voce->etichetta }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    @push('scripts')
        <script>
            // Apre/chiude il menu mobile e scambia l'icona hamburger con la X.
            (function () {
                const toggle = document.getElementById('menu-mobile-toggle');
                const panel = document.getElementById('menu-mobile');
                if (!toggle || !panel) return;

                const iconApri = toggle.querySelector('[data-menu-icon-open]');
                const iconChiudi = toggle.querySelector('[data-menu-icon-close]');

                toggle.addEventListener('click', () => {
                    const aperto = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', String(!aperto));
                    panel.classList.toggle('hidden', aperto);
                    iconApri.classList.toggle('hidden', !aperto);
                    iconChiudi.classList.toggle('hidden', aperto);
                });
            })();
        </script>
    @endpush

    {{-- ============ HERO A TUTTA LARGHEZZA (opzionale) ============ --}}
    @yield('hero')

    {{-- ============ CORPO: sidebar categorie + contenuto ============ --}}
    {{-- Le pagine che non c'entrano con la vetrina (accesso, area account)  --}}
    {{-- dichiarano @section('senza-sidebar', 1) e occupano tutta la colonna. --}}
    <div class="flex-1 mx-auto w-full max-w-7xl px-6">
        @hasSection('senza-sidebar')
            <main class="py-12">
                @yield('content')
            </main>
        @else
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
        @endif
    </div>

    {{-- ============ FOOTER ============ --}}
    <footer class="bg-caffe text-bianco/70">
        <div class="mx-auto max-w-7xl px-6 py-16
                    grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">

            <div>
                <span class="font-serif text-oro text-2xl font-medium tracking-[0.3em] pl-[0.3em]">MemorAI</span>
                <p class="mt-5 font-sans font-light text-[13px] leading-relaxed text-bianco/60">
                    {{ \App\Models\ContenutoVetrina::valore('footer.testo_brand') }}
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Collezioni</h4>
                <ul class="footer-list">
                    @foreach ($footerCollezioni as $voce)
                        <li><a href="{{ $voce->url }}" class="footer-link">{{ $voce->etichetta }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Servizi</h4>
                <ul class="footer-list">
                    @foreach ($footerServizi as $voce)
                        <li><a href="{{ $voce->url }}" class="footer-link">{{ $voce->etichetta }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Assistenza</h4>
                <ul class="footer-list">
                    @foreach ($footerAssistenza as $voce)
                        <li><a href="{{ $voce->url }}" class="footer-link">{{ $voce->etichetta }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="border-t border-bianco/10">
            <div class="mx-auto max-w-7xl px-6 py-6
                        flex flex-col sm:flex-row items-center justify-between gap-3
                        font-sans text-[11px] tracking-[0.15em] uppercase text-bianco/40">
                <span>© {{ date('Y') }} MemorAI — Ecosistema kerachrom.it</span>
                <div class="flex gap-6">
                    @foreach ($legaleVoci as $voce)
                        <a href="{{ $voce->url }}" class="hover:text-oro transition-colors">{{ $voce->etichetta }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
