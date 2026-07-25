@props([
    'slide' => null,        // Collection di App\Models\HomeSlide
    'intervallo' => 7000,   // ms fra un avanzamento automatico e il successivo
])

@php
    $slide = $slide instanceof \Illuminate\Support\Collection ? $slide : collect($slide);
@endphp

@if ($slide->isNotEmpty())
    {{-- Carosello editoriale della home: ogni slide è una porta d'ingresso a una
         sezione o a un flusso. Contenuti da DB (home_slides), gestibili dalla
         dashboard operativa. Nessuna libreria esterna: transform + JS vanilla. --}}
    <section
        data-carosello
        data-intervallo="{{ (int) $intervallo }}"
        aria-roledescription="carosello"
        aria-label="In primo piano"
        {{ $attributes->merge(['class' => 'relative bg-bianco overflow-hidden']) }}>

        {{-- pista scorrevole --}}
        <div data-carosello-pista
             class="flex w-full transition-transform duration-700 ease-[cubic-bezier(0.22,0.61,0.36,1)]">

            @foreach ($slide as $i => $s)
                <div class="w-full shrink-0"
                     role="group"
                     aria-roledescription="slide"
                     aria-label="{{ $i + 1 }} di {{ $slide->count() }}"
                     data-carosello-slide
                     @if ($i > 0) aria-hidden="true" @endif>

                    <div class="mx-auto max-w-7xl px-6 py-10 md:py-16 lg:py-20
                                grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">

                        {{-- testo --}}
                        <div class="max-w-xl order-2 lg:order-1">
                            @if ($s->occhiello)
                                <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">
                                    {{ $s->occhiello }}
                                </span>
                            @endif

                            <h2 class="mt-5 font-serif font-medium text-caffe leading-[1.05] text-4xl md:text-6xl">
                                {{ $s->titolo }}@if ($s->titolo_corsivo)
                                    <em class="italic text-oro">{{ ' ' . $s->titolo_corsivo }}</em>
                                @endif
                            </h2>

                            @if ($s->testo)
                                <p class="mt-5 font-sans font-light text-testo-soft text-base md:text-lg leading-relaxed">
                                    {{ $s->testo }}
                                </p>
                            @endif

                            @if ($s->cta_label || $s->cta2_label)
                                <div class="mt-8 flex flex-wrap gap-4">
                                    @if ($s->cta_label)
                                        <x-button variant="piena" :href="$s->cta_href ?? '#'"
                                                  :tabindex="$i > 0 ? '-1' : null">
                                            {{ $s->cta_label }}
                                        </x-button>
                                    @endif
                                    @if ($s->cta2_label)
                                        <x-button variant="contornata" :href="$s->cta2_href ?? '#'"
                                                  :tabindex="$i > 0 ? '-1' : null">
                                            {{ $s->cta2_label }}
                                        </x-button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- immagine in cornice marrone 2px --}}
                        {{-- sotto lg l'immagine è più bassa: altrimenti da sola
                             riempie la prima schermata e i pulsanti finiscono
                             sotto la piega, che è l'opposto di quel che serve --}}
                        <div class="order-1 lg:order-2 lg:justify-self-end w-full max-w-md">
                            <figure class="border-2 border-caffe bg-panna aspect-[3/2] lg:aspect-[4/5] overflow-hidden">
                                @if ($s->immagineUrl())
                                    <img src="{{ $s->immagineUrl() }}"
                                         alt="{{ $s->immagine_alt ?? $s->titolo }}"
                                         @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif
                                         {{-- taglio ancorato in alto sul formato basso: i volti stanno lì --}}
                                         class="h-full w-full object-cover object-top lg:object-center">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <span class="font-serif text-3xl text-caffe/60">MemorAI</span>
                                    </div>
                                @endif
                            </figure>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($slide->count() > 1)
            {{-- frecce (da md in su: sotto, il gesto di scorrimento e i pallini bastano) --}}
            <button type="button" data-carosello-prec
                    class="hidden md:flex absolute left-3 top-1/2 -translate-y-1/2 z-10
                           h-11 w-11 items-center justify-center rounded-full
                           border-2 border-caffe bg-bianco/90 text-caffe
                           hover:bg-caffe hover:text-bianco transition-colors duration-300">
                <span class="sr-only">Slide precedente</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>

            <button type="button" data-carosello-succ
                    class="hidden md:flex absolute right-3 top-1/2 -translate-y-1/2 z-10
                           h-11 w-11 items-center justify-center rounded-full
                           border-2 border-caffe bg-bianco/90 text-caffe
                           hover:bg-caffe hover:text-bianco transition-colors duration-300">
                <span class="sr-only">Slide successiva</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>

            {{-- indicatori --}}
            <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-10 flex items-center gap-3">
                @foreach ($slide as $i => $s)
                    <button type="button"
                            data-carosello-punto="{{ $i }}"
                            aria-label="Vai alla slide {{ $i + 1 }}: {{ $s->titolo }}"
                            @if ($i === 0) aria-current="true" @endif
                            class="h-2.5 w-2.5 rounded-full border-2 border-caffe transition-colors duration-300
                                   {{ $i === 0 ? 'bg-caffe' : 'bg-transparent hover:bg-caffe/40' }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    @once
        @push('scripts')
            <script>
                // Carosello home: scorrimento con transform, autoplay sospeso su
                // hover/focus/tab nascosta e disattivato se l'utente chiede meno
                // movimento. Nessuna dipendenza esterna.
                document.querySelectorAll('[data-carosello]').forEach((root) => {
                    const pista  = root.querySelector('[data-carosello-pista]');
                    const slide  = [...root.querySelectorAll('[data-carosello-slide]')];
                    const punti  = [...root.querySelectorAll('[data-carosello-punto]')];
                    if (!pista || slide.length < 2) return;

                    const menoMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const intervallo = parseInt(root.dataset.intervallo || '7000', 10);
                    let corrente = 0;
                    let timer = null;

                    const mostra = (i) => {
                        corrente = (i + slide.length) % slide.length;
                        pista.style.transform = `translateX(-${corrente * 100}%)`;

                        slide.forEach((s, n) => {
                            const attiva = n === corrente;
                            s.toggleAttribute('aria-hidden', !attiva);
                            // fuori dal flusso di tabulazione quando non è visibile
                            s.querySelectorAll('a, button').forEach((el) => {
                                el.tabIndex = attiva ? 0 : -1;
                            });
                        });

                        punti.forEach((p, n) => {
                            const attivo = n === corrente;
                            p.toggleAttribute('aria-current', attivo);
                            p.classList.toggle('bg-caffe', attivo);
                            p.classList.toggle('bg-transparent', !attivo);
                            p.classList.toggle('hover:bg-caffe/40', !attivo);
                        });
                    };

                    const ferma = () => { clearInterval(timer); timer = null; };
                    const avvia = () => {
                        if (menoMovimento || timer || document.hidden) return;
                        timer = setInterval(() => mostra(corrente + 1), intervallo);
                    };
                    const riavvia = () => { ferma(); avvia(); };

                    root.querySelector('[data-carosello-prec]')?.addEventListener('click', () => {
                        mostra(corrente - 1); riavvia();
                    });
                    root.querySelector('[data-carosello-succ]')?.addEventListener('click', () => {
                        mostra(corrente + 1); riavvia();
                    });
                    punti.forEach((p, n) => p.addEventListener('click', () => { mostra(n); riavvia(); }));

                    // tastiera: frecce quando il carosello ha il fuoco
                    root.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowLeft')  { mostra(corrente - 1); riavvia(); }
                        if (e.key === 'ArrowRight') { mostra(corrente + 1); riavvia(); }
                    });

                    // pausa quando l'utente ci sta sopra o ci naviga dentro
                    root.addEventListener('mouseenter', ferma);
                    root.addEventListener('mouseleave', avvia);
                    root.addEventListener('focusin', ferma);
                    root.addEventListener('focusout', (e) => {
                        if (!root.contains(e.relatedTarget)) avvia();
                    });
                    document.addEventListener('visibilitychange', () => document.hidden ? ferma() : avvia());

                    // scorrimento col dito
                    let x0 = null;
                    root.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; ferma(); }, { passive: true });
                    root.addEventListener('touchend', (e) => {
                        if (x0 === null) return;
                        const dx = e.changedTouches[0].clientX - x0;
                        if (Math.abs(dx) > 45) mostra(corrente + (dx < 0 ? 1 : -1));
                        x0 = null;
                        avvia();
                    }, { passive: true });

                    mostra(0);
                    avvia();
                });
            </script>
        @endpush
    @endonce
@endif
