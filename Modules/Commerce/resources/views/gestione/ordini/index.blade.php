@extends('layouts.gestione')

@section('title', 'Ordini — Gestione MemorAI')
@section('titolo', 'Ordini')

@section('gestione')
    @php
        use Modules\Commerce\Enums\StatoOrdine;

        $filtriStato = [['Tutti', null]];
        foreach (StatoOrdine::cases() as $caso) {
            $filtriStato[] = [$caso->etichetta(), $caso];
        }

        $filtriProvenienza = [
            ['Tutte', null],
            ['B2B', 'b2b'],
            ['B2C', 'b2c'],
        ];
    @endphp

    {{-- filtri per stato, con il conteggio --}}
    <nav aria-label="Filtra per stato" class="flex flex-wrap gap-x-7 gap-y-2 font-sans text-[12px] tracking-[0.14em] uppercase">
        @foreach ($filtriStato as [$etichetta, $caso])
            @php
                $attivo = $statoAttivo === $caso;
                $totale = $caso ? ($conteggi[$caso->value] ?? 0) : $conteggi->sum();
            @endphp
            <a href="{{ route('gestione.ordini.index', array_filter(['stato' => $caso?->value, 'provenienza' => $provenienzaAttiva])) }}"
               @class([
                   'transition-colors duration-300',
                   'text-oro-scuro' => $attivo,
                   'text-testo-soft hover:text-oro-scuro' => ! $attivo,
               ])>
                {{ $etichetta }} <span class="text-testo-soft/60">({{ $totale }})</span>
            </a>
        @endforeach
    </nav>

    {{-- filtro provenienza --}}
    <nav aria-label="Filtra per provenienza" class="mt-4 flex flex-wrap gap-x-6 gap-y-2 font-sans text-[11px] tracking-[0.14em] uppercase">
        @foreach ($filtriProvenienza as [$etichetta, $valore])
            @php $attivo = $provenienzaAttiva === $valore; @endphp
            <a href="{{ route('gestione.ordini.index', array_filter(['stato' => $statoAttivo?->value, 'provenienza' => $valore])) }}"
               @class([
                   'transition-colors duration-300',
                   'text-caffe font-medium' => $attivo,
                   'text-testo-soft/70 hover:text-caffe' => ! $attivo,
               ])>
                {{ $etichetta }}
            </a>
        @endforeach
    </nav>

    @if ($ordini->isEmpty())
        <p class="mt-10 font-sans font-light text-[15px] text-testo-soft">
            Nessun ordine in questo elenco.
        </p>
    @else
        <div class="mt-8 overflow-x-auto border border-caffe/15">
            <table class="w-full min-w-[62rem] border-collapse">
                <thead>
                    <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                        <th class="px-5 py-4 font-normal">Numero</th>
                        <th class="px-5 py-4 font-normal">Data</th>
                        <th class="px-5 py-4 font-normal">Provenienza</th>
                        <th class="px-5 py-4 font-normal">Cliente</th>
                        <th class="px-5 py-4 font-normal">Contenuto</th>
                        <th class="px-5 py-4 font-normal">Stato</th>
                        <th class="px-5 py-4 font-normal">Totale</th>
                    </tr>
                </thead>
                <tbody class="font-sans font-light text-[14px]">
                    @foreach ($ordini as $ordine)
                        <tr class="border-t border-caffe/10 hover:bg-panna/50 transition-colors duration-200">
                            <td class="px-5 py-4">
                                <a href="{{ route('gestione.ordini.show', $ordine) }}"
                                   class="font-serif text-[17px] text-testo hover:text-oro-scuro transition-colors duration-300 tabular-nums">
                                    {{ $ordine->numero }}
                                </a>
                            </td>
                            <td class="px-5 py-4 tabular-nums text-testo-soft">{{ $ordine->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'font-sans text-[10px] tracking-[0.16em] uppercase px-2 py-0.5 border',
                                    'border-oro-scuro text-oro-scuro' => $ordine->agenzia_id,
                                    'border-caffe/30 text-testo-soft' => ! $ordine->agenzia_id,
                                ])>
                                    {{ $ordine->agenzia_id ? 'B2B' : 'B2C' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-testo-soft">
                                {{ $ordine->agenzia?->ragione_sociale ?? $ordine->user?->name }}
                            </td>
                            <td class="px-5 py-4 text-testo-soft">
                                {{ $ordine->righe->count() }} {{ $ordine->righe->count() === 1 ? 'articolo' : 'articoli' }}
                                @if ($ordine->richiede_lavorazione)
                                    <span class="text-oro-scuro">· con foto</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'font-sans text-[10px] tracking-[0.18em] uppercase',
                                    'text-successo' => in_array($ordine->stato, [StatoOrdine::Spedito, StatoOrdine::Consegnato], true),
                                    'text-errore' => $ordine->stato === StatoOrdine::Annullato,
                                    'text-oro-scuro' => ! in_array($ordine->stato, [StatoOrdine::Spedito, StatoOrdine::Consegnato, StatoOrdine::Annullato], true),
                                ])>
                                    {{ $ordine->stato->etichetta() }}
                                </span>
                            </td>
                            <td class="px-5 py-4"><x-prezzo :centesimi="$ordine->totale" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $ordini->links() }}
        </div>
    @endif
@endsection
