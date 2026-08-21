@extends('layouts.account')

@section('title', 'Aggiungi un servizio — '.$defunto->nomeCompleto().' — MemorAI')
@section('titolo', 'Aggiungi un servizio')
@section('sottotitolo', $defunto->nomeCompleto())

@section('account')
<div class="max-w-2xl">

    @if (session('stato'))
        <p class="mb-6 border-l-2 border-oro-scuro bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 border border-caffe/15 bg-panna/40 px-6 py-5">
        <div>
            <p class="font-sans text-[11px] tracking-[0.2em] uppercase text-oro-scuro">Crediti servizi</p>
            <p class="mt-1 font-serif text-2xl tabular-nums">{{ $creditiSaldo }}</p>
        </div>
    </div>

    @if ($servizi->isEmpty())
        <p class="mt-8 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Tutti i servizi digitali sono già disponibili per questa persona.
        </p>
    @else
        <form method="POST" action="{{ route('defunti.aggiungi-servizio.store', $defunto) }}" class="mt-8 space-y-6">
            @csrf

            <div>
                <x-input-label value="Occasione" />
                <p class="mt-1 font-sans font-light text-[12px] text-testo-soft">
                    Solo un'etichetta: decide la dicitura del necrologio quando lo pubblicherete, non cosa potete attivare.
                </p>
                <div class="mt-2 flex flex-wrap gap-4">
                    @foreach (['funerale' => 'Funerale', 'trigesimo' => 'Trigesimo', 'anniversario' => 'Anniversario'] as $valore => $etichetta)
                        <label class="inline-flex items-center gap-2 font-sans font-light text-[14px]">
                            <input type="radio" name="occasione" value="{{ $valore }}"
                                onchange="document.getElementById('aggiungi-servizio-numero-anniversario').style.display = this.value === 'anniversario' ? 'block' : 'none'"
                                @checked(old('occasione', 'trigesimo') === $valore)>
                            {{ $etichetta }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('occasione')" class="mt-2" />

                <div id="aggiungi-servizio-numero-anniversario" class="mt-3 max-w-[10rem]" style="display: {{ old('occasione') === 'anniversario' ? 'block' : 'none' }}">
                    <x-input-label for="numero_anniversario" value="Quale anniversario" />
                    <x-text-input id="numero_anniversario" name="numero_anniversario" type="number" min="1" max="99"
                        :value="old('numero_anniversario')" />
                    <x-input-error :messages="$errors->get('numero_anniversario')" />
                </div>
            </div>

            <div>
                <x-input-label value="Quale servizio attivare" />
                <div class="mt-2 flex flex-wrap gap-4">
                    @foreach ($servizi as $servizio)
                        <label class="inline-flex items-center gap-2 font-sans font-light text-[14px] border border-caffe/15 px-4 py-2.5">
                            <input type="checkbox" name="servizi[]" value="{{ $servizio->codice }}"
                                @checked(collect(old('servizi', []))->contains($servizio->codice))>
                            {{ $servizio->etichetta }} ({{ $servizio->costo_crediti }} crediti)
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('servizi')" class="mt-2" />
            </div>

            <x-primary-button>Attiva il servizio</x-primary-button>
        </form>
    @endif

    <p class="mt-10">
        <a href="{{ route('defunti.show', $defunto) }}"
           class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
            ← Torna alla scheda del defunto
        </a>
    </p>
</div>
@endsection
