{{--
    L'estratto conto: un evento per riga (fattura emessa, pagamento
    ricevuto, crediti usati), filtrabile per mese. Condiviso fra
    gestione/agenzie/movimenti (staff) e account/fatture (agenzia) — chi
    include passa $urlPrecedente/$urlSuccessivo già costruiti, perché la
    rotta è diversa nei due casi.

    Variabili attese: $periodo, $periodoPrecedente, $periodoSuccessivo,
    $urlPrecedente, $urlSuccessivo, $eventi, $totalePagatoPeriodo,
    $totaleFatturatoPeriodo, $totaleCreditiUsatiPeriodo.
--}}
<div class="mt-10 flex items-center justify-between">
    <a href="{{ $urlPrecedente }}"
       class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← {{ $periodoPrecedente->translatedFormat('F Y') }}
    </a>
    <h2 class="font-serif text-xl font-medium capitalize">{{ $periodo->translatedFormat('F Y') }}</h2>
    <a href="{{ $urlSuccessivo }}"
       class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        {{ $periodoSuccessivo->translatedFormat('F Y') }} →
    </a>
</div>

<div class="mt-6 grid gap-6 sm:grid-cols-3">
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Pagato nel mese</p>
        <x-prezzo :centesimi="$totalePagatoPeriodo" class="mt-2 block font-serif text-2xl" />
    </div>
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Fatturato nel mese</p>
        <x-prezzo :centesimi="$totaleFatturatoPeriodo" class="mt-2 block font-serif text-2xl" />
    </div>
    <div class="border border-caffe/15 bg-panna/50 px-6 py-5">
        <p class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">Crediti usati nel mese</p>
        <p class="mt-2 font-serif text-2xl tabular-nums">{{ $totaleCreditiUsatiPeriodo }}</p>
    </div>
</div>

<div class="mt-6 border border-caffe/15">
    @forelse ($eventi as $evento)
        <a href="{{ route($rottaOrdine, $evento->ordine) }}"
           class="flex flex-wrap items-center gap-x-8 gap-y-2 px-6 py-5
                  border-b border-caffe/10 last:border-b-0
                  hover:bg-panna/40 transition-colors duration-200">
            <p class="min-w-[6rem] font-sans font-light text-[12px] text-testo-soft tabular-nums">
                {{ $evento->data->format('d/m/Y') }}
            </p>

            <div class="flex-1 min-w-[14rem]">
                <p class="font-sans text-[14px]">{{ $evento->etichetta }}</p>
                <p class="mt-0.5 font-sans font-light text-[12px] text-testo-soft tabular-nums">
                    {{ $evento->ordine->numero }}
                    @if ($evento->riferimento)
                        · {{ $evento->riferimento }}
                    @endif
                </p>
            </div>

            <div class="text-right min-w-[6rem]">
                @if ($evento->importoCrediti !== null)
                    <p class="font-serif text-lg tabular-nums">{{ $evento->importoCrediti }} crediti</p>
                @else
                    <x-prezzo :centesimi="$evento->importoDenaro" class="font-serif text-lg" />
                @endif
            </div>
        </a>
    @empty
        <p class="px-6 py-10 text-center font-sans font-light text-[14px] text-testo-soft">
            Nessun movimento in questo mese.
        </p>
    @endforelse
</div>
