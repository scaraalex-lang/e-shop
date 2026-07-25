@php
    $voci = config('vetrina.voci', []);
@endphp

{{-- Navigazione laterale sotto lg: il bottone vive nella barra sticky, il
     pannello è fixed e scorre da sinistra. Sopra lg non serve: c'è l'aside
     con le stesse categorie. --}}

{{-- velo --}}
<div data-drawer-velo
     class="lg:hidden fixed inset-0 z-[60] bg-caffe/50 opacity-0 pointer-events-none
            transition-opacity duration-300"></div>

{{-- pannello --}}
<div id="nav-laterale"
     data-drawer
     role="dialog"
     aria-modal="true"
     aria-label="Menu di navigazione"
     class="lg:hidden fixed top-0 left-0 z-[70] h-full w-[85%] max-w-sm
            bg-bianco border-r-2 border-caffe shadow-xl
            -translate-x-full transition-transform duration-300 ease-out
            flex flex-col">

    {{-- testata --}}
    <div class="shrink-0 flex items-center justify-between px-6 h-16 border-b-2 border-caffe">
        <a href="{{ url('/') }}" class="font-serif text-oro text-2xl tracking-[0.28em] pl-[0.28em]">
            MemorAI
        </a>
        <button type="button" data-drawer-chiudi
                class="p-2 -mr-2 text-caffe hover:text-oro-scuro transition-colors duration-300">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <line x1="6" y1="6" x2="18" y2="18" />
                <line x1="18" y1="6" x2="6" y2="18" />
            </svg>
            <span class="sr-only">Chiudi il menu</span>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto overscroll-contain px-6 py-7 space-y-9">

        {{-- voci principali --}}
        @if ($voci)
            <nav aria-label="Navigazione principale (menu laterale)">
                <ul class="space-y-1">
                    @foreach ($voci as $voce)
                        <li>
                            <a href="{{ $voce['href'] }}"
                               class="block py-2 font-sans text-[13px] tracking-[0.18em] uppercase
                                      text-testo hover:text-oro-scuro transition-colors duration-200">
                                {{ $voce['etichetta'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <div class="border-t-2 border-caffe/15"></div>

        {{-- stesse categorie della colonna desktop --}}
        <x-category-sidebar idPrefisso="drawer-reparto" />

        <div class="border-t-2 border-caffe/15"></div>

        {{-- servizi rapidi --}}
        <div>
            <h2 class="font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-4">
                Servizi
            </h2>
            <ul class="space-y-1">
                <li>
                    <a href="{{ url('/prenota/ricordino') }}"
                       class="block py-1.5 font-serif text-lg text-testo hover:text-oro-scuro transition-colors duration-200">
                        Prenota i ricordini
                    </a>
                </li>
                <li>
                    <a href="{{ url('/categoria/photoceramiche') }}"
                       class="block py-1.5 font-serif text-lg text-testo hover:text-oro-scuro transition-colors duration-200">
                        Prenota la photoceramica
                    </a>
                </li>
            </ul>
        </div>

        {{-- account e carrello (le icone della barra sono nascoste sotto sm) --}}
        <div class="flex items-center gap-6 pt-1 text-caffe">
            <a href="#" class="inline-flex items-center gap-2 font-sans text-[12px] tracking-[0.16em] uppercase
                               hover:text-oro-scuro transition-colors duration-300">
                <x-icon.account class="w-5 h-5" /> Account
            </a>
            <a href="#" class="inline-flex items-center gap-2 font-sans text-[12px] tracking-[0.16em] uppercase
                               hover:text-oro-scuro transition-colors duration-300">
                <x-icon.cart class="w-5 h-5" /> Carrello
            </a>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            // Drawer di navigazione: apre/chiude, blocca lo scroll del fondo,
            // chiude con Esc, col velo e cliccando una voce.
            (() => {
                const pannello = document.querySelector('[data-drawer]');
                const velo     = document.querySelector('[data-drawer-velo]');
                const apri     = document.querySelector('[data-drawer-apri]');
                const chiudiBtn = document.querySelector('[data-drawer-chiudi]');
                if (!pannello || !velo || !apri) return;

                let aperto = false;

                const imposta = (stato) => {
                    aperto = stato;
                    pannello.classList.toggle('-translate-x-full', !stato);
                    velo.classList.toggle('opacity-0', !stato);
                    velo.classList.toggle('pointer-events-none', !stato);
                    apri.setAttribute('aria-expanded', String(stato));
                    document.body.classList.toggle('overflow-hidden', stato);
                    if (stato) {
                        chiudiBtn?.focus();
                    } else {
                        apri.focus();
                    }
                };

                apri.addEventListener('click', () => imposta(true));
                chiudiBtn?.addEventListener('click', () => imposta(false));
                velo.addEventListener('click', () => imposta(false));

                // una voce cliccata porta altrove: il drawer non deve restare aperto
                pannello.addEventListener('click', (e) => {
                    if (e.target.closest('a')) imposta(false);
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && aperto) imposta(false);
                });

                // tornando sopra lg il drawer non ha più senso: richiudi e sblocca
                window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
                    if (e.matches && aperto) imposta(false);
                });
            })();
        </script>
    @endpush
@endonce
