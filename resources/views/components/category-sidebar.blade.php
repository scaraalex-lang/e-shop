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

    // Slug attivo (se siamo su /categoria/{slug}) per evidenziare la voce.
    $attivo = request()->segment(1) === 'categoria' ? request()->segment(2) : null;
@endphp

<nav aria-label="Categorie prodotti" {{ $attributes }}>
    <h2 class="font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro mb-4">
        Reparti
    </h2>

    <ul class="space-y-1">
        @foreach ($radici as $radice)
            @php $rAttiva = $attivo === $radice->slug; @endphp
            <li>
                <a href="{{ url('/categoria/' . $radice->slug) }}"
                   @class([
                       'block py-1.5 font-serif text-lg transition-colors duration-200',
                       'text-oro-scuro' => $rAttiva,
                       'text-testo hover:text-oro-scuro' => ! $rAttiva,
                   ])>
                    {{ $radice->name }}
                </a>

                @if ($radice->children->isNotEmpty())
                    <ul class="mt-1 mb-2 pl-3 border-l-2 border-caffe/15 space-y-0.5">
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
