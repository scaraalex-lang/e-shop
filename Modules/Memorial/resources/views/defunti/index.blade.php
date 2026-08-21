@extends('layouts.account')

@section('title', 'I miei defunti — MemorAI')
@section('titolo', 'I miei defunti')
@section('sottotitolo', 'La scheda di ogni persona già registrata: foto, manifesto, necrologio, ricordino, video memoriale.')

@section('account')
@if ($defunti->isEmpty())
    <div class="mt-8 border border-caffe/15 bg-panna/50 px-8 py-14 text-center">
        <p class="font-serif text-2xl">Nessun defunto registrato</p>
        <p class="mt-3 font-sans font-light text-[15px] text-testo-soft">
            La prima scheda si crea dai dati della persona in un ordine, in fase di lavorazione.
        </p>
    </div>
@else
    <div class="mt-8 border border-caffe/15">
        @foreach ($defunti as $defunto)
            <article class="flex flex-wrap items-center gap-x-8 gap-y-2 px-6 py-5 border-b border-caffe/10 last:border-b-0">
                <div class="min-w-[12rem] flex-1">
                    <a href="{{ route('defunti.show', $defunto) }}"
                       class="font-serif text-lg hover:text-oro-scuro transition-colors duration-300">
                        {{ $defunto->nomeCompleto() ?: 'Senza nome' }}
                    </a>
                    <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft tabular-nums">
                        @if ($defunto->data_morte)
                            {{ $defunto->data_morte->format('d/m/Y') }}
                        @endif
                        @if ($defunto->eta())
                            &middot; {{ $defunto->eta() }} anni
                        @endif
                    </p>
                </div>

                <a href="{{ route('defunti.show', $defunto) }}"
                   class="font-sans text-[10px] tracking-[0.2em] uppercase text-oro-scuro hover:text-caffe transition-colors duration-300">
                    Apri →
                </a>
            </article>
        @endforeach
    </div>

    <div class="mt-8">{{ $defunti->links() }}</div>
@endif
@endsection
