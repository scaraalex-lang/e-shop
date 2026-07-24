@props([
    'categorie' => null,   // Collection opzionale; se null legge le radici attive dal DB
])

@php
    use Modules\Catalog\Models\Category;

    // Legge dal database le categorie radice attive, ordinate per sort_order.
    $items = $categorie ?? Category::query()
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();
@endphp

@if ($items->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12']) }}>
        @foreach ($items as $categoria)
            <x-category-card :categoria="$categoria" />
        @endforeach
    </div>
@endif
