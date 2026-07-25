@extends('layouts.gestione')

@section('title', 'Slide della home')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif text-4xl text-caffe">Slide della home</h1>
        <p class="mt-3 font-sans font-light text-testo-soft leading-relaxed max-w-2xl">
            L'ordine qui è l'ordine del carosello. Le slide nascoste restano archiviate.
            Se nessuna è pubblicata, la home torna all'apertura statica di sempre.
        </p>
    </div>
    <x-button variant="piena" :href="route('gestione.slide.create')">Nuova slide</x-button>
</div>

@if ($slide->isEmpty())
    <p class="mt-10 font-sans font-light text-testo-soft">Nessuna slide. Creane una per accendere il carosello.</p>
@else
    <ul class="mt-10 space-y-5">
        @foreach ($slide as $s)
            <li class="border-2 border-caffe bg-bianco flex flex-col sm:flex-row">

                <figure class="sm:w-40 shrink-0 bg-panna aspect-[4/3] sm:aspect-auto sm:h-auto overflow-hidden border-b-2 sm:border-b-0 sm:border-r-2 border-caffe">
                    @if ($s->immagineUrl())
                        <img src="{{ $s->immagineUrl() }}" alt="" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full min-h-24 flex items-center justify-center font-serif text-caffe/50">
                            senza immagine
                        </div>
                    @endif
                </figure>

                <div class="flex-1 min-w-0 px-5 py-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-sans text-[11px] tracking-[0.24em] uppercase {{ $s->is_active ? 'text-oro-scuro' : 'text-testo-soft' }}">
                            {{ $s->is_active ? 'pubblicata' : 'nascosta' }}
                        </span>
                        <span class="font-sans text-[11px] tracking-[0.16em] uppercase text-testo-soft">
                            posizione {{ $s->sort_order }}
                        </span>
                    </div>

                    <h2 class="mt-2 font-serif text-2xl text-caffe">
                        {{ $s->titolo }}
                        @if ($s->titolo_corsivo)<em class="italic text-oro">{{ ' ' . $s->titolo_corsivo }}</em>@endif
                    </h2>

                    @if ($s->testo)
                        <p class="mt-1 font-sans font-light text-[14px] text-testo-soft max-w-xl">
                            {{ \Illuminate\Support\Str::limit($s->testo, 140) }}
                        </p>
                    @endif

                    <p class="mt-3 font-sans text-[12px] text-testo-soft">
                        @if ($s->cta_label)
                            <span class="text-caffe">{{ $s->cta_label }}</span> → {{ $s->cta_href ?: '—' }}
                        @endif
                        @if ($s->cta2_label)
                            <span class="mx-2 text-caffe/30">|</span>
                            <span class="text-caffe">{{ $s->cta2_label }}</span> → {{ $s->cta2_href ?: '—' }}
                        @endif
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-4 font-sans text-[12px] tracking-[0.14em] uppercase">
                        <a href="{{ route('gestione.slide.edit', $s) }}"
                           class="text-oro-scuro hover:text-caffe transition-colors">Modifica</a>

                        <form method="POST" action="{{ route('gestione.slide.attiva', $s) }}">
                            @csrf
                            <button type="submit" class="text-oro-scuro hover:text-caffe transition-colors cursor-pointer">
                                {{ $s->is_active ? 'Nascondi' : 'Pubblica' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('gestione.slide.sposta', [$s, 'su']) }}">
                            @csrf
                            <button type="submit" class="text-testo-soft hover:text-caffe transition-colors cursor-pointer">↑ Su</button>
                        </form>

                        <form method="POST" action="{{ route('gestione.slide.sposta', [$s, 'giu']) }}">
                            @csrf
                            <button type="submit" class="text-testo-soft hover:text-caffe transition-colors cursor-pointer">↓ Giù</button>
                        </form>

                        <form method="POST" action="{{ route('gestione.slide.destroy', $s) }}"
                              onsubmit="return confirm('Eliminare la slide «{{ $s->titolo }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-testo-soft hover:text-oro-scuro transition-colors cursor-pointer">Elimina</button>
                        </form>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
@endsection
