@props([
    // Prefisso degli id dei pannelli: la sidebar compare due volte nella stessa
    // pagina (colonna desktop + drawer mobile) e gli id devono restare unici,
    // altrimenti il chevron del drawer aprirebbe il pannello della colonna.
    'idPrefisso' => 'reparto',
])

@php
    use Modules\Catalog\Models\Category;

    // Albero categorie dal DB: radici attive + figli attivi, per sort_order.
    // Gestibile in futuro dalla dashboard admin senza toccare il codice.
    $radici = Category::query()
        ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    // Slug attivo (se siamo su /categoria/{slug}) per evidenziare e aprire il ramo.
    $attivo = request()->segment(1) === 'categoria' ? request()->segment(2) : null;
@endphp

<nav aria-label="Categorie prodotti" {{ $attributes }}>
    <h2 class="font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-4">
        Reparti
    </h2>

    <ul class="space-y-1">
        @foreach ($radici as $radice)
            @php
                $rAttiva = $attivo === $radice->slug;
                $haFigli = $radice->children->isNotEmpty();
                // Il ramo si apre di default se la radice o un suo figlio è attivo.
                $aperto  = $rAttiva || ($haFigli && $radice->children->contains('slug', $attivo));
                $panelId = $idPrefisso . '-' . $radice->slug;
            @endphp
            <li>
                <div class="flex items-center">
                    <a href="{{ url('/categoria/' . $radice->slug) }}"
                       @class([
                           'flex-1 min-w-0 py-1.5 font-serif text-lg transition-colors duration-200 truncate',
                           'text-oro-scuro' => $rAttiva,
                           'text-testo hover:text-oro-scuro' => ! $rAttiva,
                       ])>
                        {{ $radice->name }}
                    </a>

                    @if ($haFigli)
                        <button type="button"
                                data-cat-toggle
                                aria-controls="{{ $panelId }}"
                                aria-expanded="{{ $aperto ? 'true' : 'false' }}"
                                class="shrink-0 ml-2 p-1.5 -mr-1.5 text-caffe/60 hover:text-oro-scuro transition-colors duration-200">
                            <span class="sr-only">Mostra sottocategorie di {{ $radice->name }}</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 {{ $aperto ? 'rotate-90' : '' }}"
                                 data-cat-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                    @endif
                </div>

                @if ($haFigli)
                    <ul id="{{ $panelId }}"
                        class="mt-1 mb-2 pl-3 border-l-2 border-caffe/15 space-y-0.5 {{ $aperto ? '' : 'hidden' }}">
                        @foreach ($radice->children as $figlio)
                            @php $fAttiva = $attivo === $figlio->slug; @endphp
                            <li>
                                <a href="{{ url('/categoria/' . $figlio->slug) }}"
                                   @class([
                                       'block py-1 font-sans text-[13px] transition-colors duration-200',
                                       'text-oro-scuro' => $fAttiva,
                                       'text-testo-soft hover:text-oro-scuro' => ! $fAttiva,
                                   ])>
                                    {{ $figlio->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</nav>

@once
    @push('scripts')
        <script>
            // Accordion sidebar categorie: apre/chiude il pannello sottocategorie.
            document.addEventListener('click', (e) => {
                const toggle = e.target.closest('[data-cat-toggle]');
                if (!toggle) return;

                const panel = document.getElementById(toggle.getAttribute('aria-controls'));
                if (!panel) return;

                const aperto = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!aperto));
                panel.classList.toggle('hidden', aperto);

                const chevron = toggle.querySelector('[data-cat-chevron]');
                if (chevron) chevron.classList.toggle('rotate-90', !aperto);
            });
        </script>
    @endpush
@endonce
