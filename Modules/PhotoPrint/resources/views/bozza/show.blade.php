@extends('layouts.app')

@section('title', 'Il ricordino di '.($defunto?->nomeCompleto() ?? 'famiglia').' — MemorAI')
@section('senza-sidebar', 1)

@section('content')
@php
    use Modules\Memorial\Models\RevisioneRicordino;

    $facciate = array_filter([
        'Fronte' => $revisione->anteprima ?: $ricordino->anteprima_fronte,
        'Retro' => $ricordino->anteprima_retro,
    ]);
@endphp

<div class="mx-auto w-full max-w-3xl">

    <header class="text-center">
        <span class="font-sans text-[11px] tracking-[0.35em] uppercase text-oro-scuro">Da guardare insieme</span>
        <h1 class="mt-4 font-serif text-3xl md:text-4xl font-medium leading-tight">
            Il ricordino di {{ $defunto?->nomeCompleto() }}
        </h1>
        <span class="mx-auto mt-5 block h-px w-16 bg-oro"></span>
        <p class="mx-auto mt-6 max-w-xl font-sans font-light text-[15px] leading-relaxed text-testo-soft">
            Prima di stamparlo vorremmo che lo vedeste voi. Guardatelo con calma:
            se qualcosa non vi convince, scrivetecelo e lo correggiamo.
        </p>
    </header>

    {{-- ============ le due facciate ============ --}}
    <div class="mt-12 flex flex-wrap justify-center gap-8">
        @forelse ($facciate as $lato => $percorso)
            <figure>
                <img src="{{ asset('storage/'.$percorso) }}"
                     alt="Ricordino, {{ strtolower($lato) }}"
                     class="w-64 border border-caffe/20 bg-bianco shadow-[0_18px_50px_rgba(58,46,34,0.12)]">
                <figcaption class="mt-3 text-center font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                    {{ $lato }}
                </figcaption>
            </figure>
        @empty
            <p class="font-sans font-light text-testo-soft">L'anteprima non è disponibile.</p>
        @endforelse
    </div>

    {{-- ============ la risposta ============ --}}
    @if ($revisione->esito === RevisioneRicordino::APPROVATA)
        <div class="mt-14 border-l-2 border-successo bg-panna/60 px-7 py-6 text-center">
            <p class="font-serif text-2xl">Grazie.</p>
            <p class="mt-2 font-sans font-light text-[15px] leading-relaxed text-testo-soft">
                Avete approvato il ricordino il {{ $revisione->risposta_at?->format('d/m/Y') }}.
                Adesso lo stampiamo con cura.
            </p>
        </div>

    @elseif ($revisione->esito === RevisioneRicordino::MODIFICHE)
        <div class="mt-14 border-l-2 border-oro bg-panna/60 px-7 py-6">
            <p class="font-serif text-2xl">Abbiamo ricevuto la vostra richiesta.</p>
            <p class="mt-2 font-sans font-light text-[15px] leading-relaxed text-testo-soft">
                Ci mettiamo mano e vi rimandiamo il ricordino corretto.
            </p>
            <p class="mt-4 border-l-2 border-caffe/20 pl-4 font-sans font-light text-[14px] leading-relaxed text-testo">
                “{{ $revisione->nota }}”
            </p>
        </div>

    @else
        <div class="mt-14 grid gap-px bg-caffe/15 border border-caffe/15 md:grid-cols-2">

            {{-- va bene --}}
            <section class="bg-bianco px-7 py-8 flex flex-col">
                <h2 class="font-serif text-2xl font-medium">Va bene così</h2>
                <p class="mt-3 flex-1 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    Se il ricordino vi sembra giusto, confermatelo: da quel momento andiamo
                    in stampa e non si può più correggere.
                </p>
                <form method="POST" action="{{ route('bozza.approva', $revisione->token) }}" class="mt-6">
                    @csrf
                    <x-primary-button class="w-full">Approvo il ricordino</x-primary-button>
                </form>
            </section>

            {{-- da correggere --}}
            <section class="bg-bianco px-7 py-8">
                <h2 class="font-serif text-2xl font-medium">C'è qualcosa da cambiare</h2>
                <p class="mt-3 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    Ditecelo con parole vostre: una data sbagliata, un nome, la fotografia.
                </p>

                <form method="POST" action="{{ route('bozza.modifiche', $revisione->token) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="nota" class="sr-only">Cosa vorreste cambiare</label>
                        <textarea id="nota" name="nota" rows="4" required
                                  placeholder="Per esempio: la data di nascita è 10 ottobre, non 10 novembre."
                                  class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                         text-[15px] leading-relaxed placeholder:text-testo-soft/50
                                         focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ old('nota') }}</textarea>
                        <x-input-error :messages="$errors->get('nota')" />
                    </div>
                    <x-button type="submit" variant="contornata" class="w-full">Chiedo una correzione</x-button>
                </form>
            </section>
        </div>
    @endif

    {{-- ============ i giri precedenti ============ --}}
    @if ($precedenti->isNotEmpty())
        <section class="mt-14">
            <h2 class="font-sans text-[11px] tracking-[0.25em] uppercase text-oro-scuro">Cosa ci siamo detti</h2>

            <ol class="mt-5 space-y-4">
                @foreach ($precedenti as $passata)
                    <li class="border-l-2 border-caffe/20 pl-5">
                        <p class="font-sans text-[11px] tracking-[0.14em] uppercase text-testo-soft">
                            {{ $passata->inviata_at->format('d/m/Y') }} · {{ $passata->esitoLeggibile() }}
                        </p>
                        @if ($passata->nota)
                            <p class="mt-1 font-sans font-light text-[14px] leading-relaxed text-testo">
                                “{{ $passata->nota }}”
                            </p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <p class="mt-14 text-center font-sans font-light text-[12px] leading-relaxed text-testo-soft">
        Questa pagina è riservata a voi. Non serve alcuna registrazione e resta
        raggiungibile finché la lavorazione è aperta.
    </p>
</div>
@endsection
