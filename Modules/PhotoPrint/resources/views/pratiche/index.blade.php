@extends('layouts.account')

@section('title', 'Pratiche — MemorAI')
@section('titolo', 'Le pratiche')
@section('sottotitolo', 'Scegli su quale lavorare: si entra nella sua lavorazione, poi negli editor.')

@section('account')
@if ($ordini->isEmpty())
    <div class="border border-caffe/15 bg-panna/50 px-8 py-14 text-center">
        <p class="font-serif text-2xl">Nessuna pratica ancora</p>
        <p class="mt-3 font-sans font-light text-[15px] text-testo-soft">
            Le pratiche nascono da un ordine col kit trigesimo: appariranno qui non appena ce n'è uno da lavorare.
        </p>
    </div>
@else
    <div class="border border-caffe/15">
        @foreach ($ordini as $ordine)
            <a href="{{ route('lavorazione', $ordine) }}"
               class="flex flex-wrap items-center gap-x-8 gap-y-2 px-6 py-5
                      border-b border-caffe/10 last:border-b-0
                      hover:bg-panna/40 transition-colors duration-200">
                <div class="min-w-[9rem]">
                    <span class="font-serif text-lg text-testo tabular-nums">{{ $ordine->numero }}</span>
                    <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft tabular-nums">
                        {{ $ordine->created_at->format('d/m/Y') }}
                    </p>
                </div>

                <p class="flex-1 min-w-[10rem] font-sans font-light text-[13px] text-testo-soft">
                    {{ $defunti->get($ordine->defunto_id)?->nomeCompleto() ?? 'Defunto non ancora registrato' }}
                </p>

                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-oro-scuro min-w-[8rem]">
                    {{ $ordine->stato->etichetta() }}
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $ordini->links() }}</div>
@endif

<div class="mt-10 border-t border-caffe/10 pt-6">
    <p class="font-sans font-light text-[12px] text-testo-soft">
        Serve una pratica nuova? Vai su
        <a href="{{ route('ordini.nuovo') }}" class="text-oro-scuro underline underline-offset-4 decoration-oro/40">Nuovo ordine</a>.
        Solo per una prova rapida, senza una pratica vera:
        <a href="{{ route('studio.ricordino', ['demo' => 1]) }}"
           class="text-oro-scuro underline underline-offset-4 decoration-oro/40">Designer ricordini</a>
        ·
        <a href="{{ route('studio.foto', ['demo' => 1]) }}"
           class="text-oro-scuro underline underline-offset-4 decoration-oro/40">Foto Manager</a>
    </p>
</div>
@endsection
