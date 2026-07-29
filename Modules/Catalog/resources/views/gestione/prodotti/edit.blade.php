@extends('layouts.gestione')

@section('title', $prodotto->name.' — Gestione MemorAI')
@section('titolo', $prodotto->name)

@section('gestione')
    <div class="flex flex-wrap items-center justify-between gap-x-8 gap-y-3">
        <a href="{{ route('gestione.prodotti.index') }}"
           class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
            ← Tutti i prodotti
        </a>

        <nav aria-label="Scorri i prodotti" class="flex items-center gap-6 font-sans text-[11px] tracking-[0.18em] uppercase">
            @if ($precedente)
                <a href="{{ route('gestione.prodotti.edit', $precedente) }}"
                   class="text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                    ← {{ \Illuminate\Support\Str::limit($precedente->name, 28) }}
                </a>
            @endif
            @if ($successivo)
                <a href="{{ route('gestione.prodotti.edit', $successivo) }}"
                   class="text-testo-soft hover:text-oro-scuro transition-colors duration-300">
                    {{ \Illuminate\Support\Str::limit($successivo->name, 28) }} →
                </a>
            @endif
        </nav>
    </div>

    <form method="POST" action="{{ route('gestione.prodotti.update', $prodotto) }}" enctype="multipart/form-data" class="mt-8">
        @csrf
        @method('PUT')
        @include('catalog::gestione.prodotti._form', ['prodotto' => $prodotto])
    </form>
@endsection
