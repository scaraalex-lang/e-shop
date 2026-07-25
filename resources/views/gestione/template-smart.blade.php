@extends('layouts.gestione')

@section('title', 'Impaginazione Smart')

@section('content')
<h1 class="font-serif text-4xl text-caffe">Impaginazione del Designer Smart</h1>
<p class="mt-3 font-sans font-light text-testo-soft leading-relaxed max-w-2xl">
    Da telefono il cliente non sceglie il layout: usa quello che decidi qui, uno per formato.
    Nel designer completo restano disponibili tutti. L'impaginazione <em>Smart</em> è l'unica
    con la sede della foto già prevista: sceglierne un'altra significa ricordino di solo testo.
</p>

<form method="POST" action="{{ route('gestione.template-smart.aggiorna') }}" class="mt-10 space-y-12">
    @csrf
    @method('PUT')

    @foreach ($formati as $formato)
        <section>
            <h2 class="font-sans text-[11px] tracking-[0.28em] uppercase text-oro-scuro">Formato {{ $formato }} cm</h2>

            @php $gruppo = $template[$formato] ?? collect(); @endphp

            @if ($gruppo->isEmpty())
                <p class="mt-4 font-sans font-light text-testo-soft">
                    Nessuna impaginazione per questo formato: lancia il seeder dei template.
                </p>
            @else
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach ($gruppo as $t)
                        @php $conFoto = collect($t->fronte['objects'] ?? [])->contains('customType', 'photo-slot'); @endphp
                        <label class="border-2 cursor-pointer transition-colors
                                      {{ $t->is_smart_default ? 'border-oro-scuro bg-panna' : 'border-caffe bg-bianco hover:bg-panna/50' }}">
                            <div class="px-4 py-4">
                                <div class="flex items-start gap-3">
                                    <input type="radio" name="scelta[{{ $formato }}]" value="{{ $t->id }}"
                                           @checked($t->is_smart_default)
                                           class="mt-1 h-4 w-4 accent-[#a5863f]">
                                    <span>
                                        <span class="block font-serif text-xl text-caffe">{{ $t->nome }}</span>
                                        <span class="block mt-1 font-sans text-[11px] tracking-[0.16em] uppercase text-testo-soft">
                                            {{ $t->is_predefinito ? 'predefinito MemorAI' : 'salvato' }}
                                        </span>
                                    </span>
                                </div>

                                <p class="mt-3 font-sans font-light text-[13px] leading-relaxed
                                          {{ $conFoto ? 'text-oro-scuro' : 'text-testo-soft' }}">
                                    {{ $conFoto ? 'Con sede per la foto' : 'Solo testo' }}
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach

    <div class="flex flex-wrap items-center gap-4">
        <x-button variant="piena" type="submit">Salva la scelta</x-button>
        <a href="{{ url('/studio/ricordino/smart') }}" target="_blank"
           class="font-sans text-[12px] tracking-[0.16em] uppercase text-testo-soft hover:text-oro-scuro transition-colors">
            Prova il Designer Smart ↗
        </a>
    </div>
</form>
@endsection
