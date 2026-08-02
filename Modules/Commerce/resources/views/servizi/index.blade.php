@extends('layouts.account')

@section('title', 'Acquisto Servizi — MemorAI')
@section('titolo', 'Acquisto Servizi')
@section('sottotitolo', 'Attiva ricordini, manifesti o necrologio pagando in crediti: apre subito la lavorazione, senza passare dal kit fisico.')

@section('account')
<div class="space-y-px bg-caffe/15 border border-caffe/15">
    <section class="bg-bianco px-7 py-8">
        @if (session('stato'))
            <div class="mb-6 border border-oro-scuro/40 bg-panna/60 px-5 py-4 font-sans text-[13px] text-testo">
                {{ session('stato') }}
            </div>
        @endif

        @if (! is_null($creditiSaldo))
            <div class="flex flex-wrap items-center justify-between gap-4 border border-caffe/15 bg-panna/40 px-6 py-5">
                <div>
                    <p class="font-sans text-[11px] tracking-[0.2em] uppercase text-oro-scuro">Crediti servizi</p>
                    <p class="mt-1 font-serif text-2xl tabular-nums" data-saldo-crediti>{{ $creditiSaldo }}</p>
                </div>
                @if ($prodottoCrediti)
                    <form method="POST" action="{{ route('carrello.aggiungi') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $prodottoCrediti->id }}">
                        <input type="hidden" name="quantita" value="1">
                        <x-button type="submit">Ricarica crediti</x-button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('ordini.servizio') }}" class="mt-6 max-w-2xl space-y-6" data-form-attiva-servizi>
                @csrf

                <p class="hidden font-sans text-[13px] text-[#8a3b2c]" data-errore-attiva-servizi></p>

                <div>
                    <x-input-label value="Occasione" />
                    <p class="mt-1 font-sans font-light text-[12px] text-testo-soft">
                        Solo un'etichetta: decide la dicitura del necrologio quando lo pubblicherete, non cosa potete attivare.
                    </p>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach (['funerale' => 'Funerale', 'trigesimo' => 'Trigesimo', 'anniversario' => 'Anniversario'] as $valore => $etichetta)
                            <label class="inline-flex items-center gap-2 font-sans font-light text-[14px]">
                                <input type="radio" name="occasione" value="{{ $valore }}"
                                    onchange="document.getElementById('nuovo-ordine-numero-anniversario').style.display = this.value === 'anniversario' ? 'block' : 'none'"
                                    @checked(old('occasione', 'trigesimo') === $valore)>
                                {{ $etichetta }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('occasione')" class="mt-2" />

                    <div id="nuovo-ordine-numero-anniversario" class="mt-3 max-w-[10rem]" style="display: {{ old('occasione') === 'anniversario' ? 'block' : 'none' }}">
                        <x-input-label for="numero_anniversario" value="Quale anniversario" />
                        <x-text-input id="numero_anniversario" name="numero_anniversario" type="number" min="1" max="99"
                            :value="old('numero_anniversario')" />
                        <x-input-error :messages="$errors->get('numero_anniversario')" />
                    </div>
                </div>

                @if ($servizi && $servizi->isNotEmpty())
                    <div>
                        <x-input-label value="Quali servizi attivare" />
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

                    <x-button type="submit" data-submit-attiva-servizi>Acquisto servizio digitale</x-button>
                @else
                    <p class="font-sans font-light text-[13px] text-testo-soft italic">
                        Nessun servizio attivo al momento.
                    </p>
                @endif
            </form>
        @else
            <p class="font-sans font-light text-[13px] text-testo-soft italic">
                Il servizio a crediti è riservato alle agenzie.
            </p>
        @endif
    </section>
</div>

@if (! is_null($creditiSaldo) && $servizi && $servizi->isNotEmpty())
    <script>
        (function () {
            const form = document.querySelector('[data-form-attiva-servizi]');
            if (! form) return;

            const bottone = form.querySelector('[data-submit-attiva-servizi]');
            const errore = form.querySelector('[data-errore-attiva-servizi]');

            form.addEventListener('submit', async function (evento) {
                evento.preventDefault();
                errore.classList.add('hidden');
                bottone.disabled = true;

                try {
                    const risposta = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: new FormData(form),
                    });
                    const dati = await risposta.json();

                    if (! risposta.ok) {
                        errore.textContent = dati.errore || 'Qualcosa non ha funzionato: riprova.';
                        errore.classList.remove('hidden');
                        bottone.disabled = false;
                        return;
                    }

                    // Il saldo si aggiorna a vista subito, prima ancora del
                    // passaggio alla lavorazione: la sottrazione si vede.
                    document.querySelectorAll('[data-saldo-crediti]').forEach(function (nodo) {
                        nodo.textContent = dati.saldo;
                    });

                    window.setTimeout(function () {
                        window.location = dati.redirect;
                    }, 600);
                } catch (e) {
                    errore.textContent = 'Connessione non riuscita: riprova.';
                    errore.classList.remove('hidden');
                    bottone.disabled = false;
                }
            });
        })();
    </script>
@endif
@endsection
