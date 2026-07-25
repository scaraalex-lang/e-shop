@extends('layouts.gestione')

@section('title', 'Agenzie — Gestione MemorAI')
@section('titolo', 'Agenzie')

@section('gestione')
    @php
        use Modules\Commerce\Enums\StatoAgenzia;

        $filtri = [['Tutte', null]];
        foreach (StatoAgenzia::cases() as $caso) {
            $filtri[] = [$caso->etichetta(), $caso];
        }
    @endphp

    {{-- filtri per stato, con il conteggio --}}
    <nav aria-label="Filtra per stato" class="flex flex-wrap gap-x-7 gap-y-2 font-sans text-[12px] tracking-[0.14em] uppercase">
        @foreach ($filtri as [$etichetta, $caso])
            @php
                $attivo = $statoAttivo === $caso;
                $totale = $caso ? ($conteggi[$caso->value] ?? 0) : $conteggi->sum();
            @endphp
            <a href="{{ route('gestione.agenzie.index', $caso ? ['stato' => $caso->value] : []) }}"
               @class([
                   'transition-colors duration-300',
                   'text-oro-scuro' => $attivo,
                   'text-testo-soft hover:text-oro-scuro' => ! $attivo,
               ])>
                {{ $etichetta }} <span class="text-testo-soft/60">({{ $totale }})</span>
            </a>
        @endforeach
    </nav>

    @if ($agenzie->isEmpty())
        <p class="mt-10 font-sans font-light text-[15px] text-testo-soft">
            Nessuna agenzia in questo elenco.
        </p>
    @else
        <div class="mt-8 overflow-x-auto border border-caffe/15">
            <table class="w-full min-w-[52rem] border-collapse">
                <thead>
                    <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                        <th class="px-5 py-4 font-normal">Ragione sociale</th>
                        <th class="px-5 py-4 font-normal">Partita IVA</th>
                        <th class="px-5 py-4 font-normal">Sede</th>
                        <th class="px-5 py-4 font-normal">Referente</th>
                        <th class="px-5 py-4 font-normal">Richiesta</th>
                        <th class="px-5 py-4 font-normal">Stato</th>
                    </tr>
                </thead>
                <tbody class="font-sans font-light text-[14px]">
                    @foreach ($agenzie as $agenzia)
                        <tr class="border-t border-caffe/10 hover:bg-panna/50 transition-colors duration-200">
                            <td class="px-5 py-4">
                                <a href="{{ route('gestione.agenzie.show', $agenzia) }}"
                                   class="font-serif text-[17px] text-testo hover:text-oro-scuro transition-colors duration-300">
                                    {{ $agenzia->ragione_sociale }}
                                </a>
                            </td>
                            <td class="px-5 py-4 tabular-nums text-testo-soft">{{ $agenzia->partita_iva }}</td>
                            <td class="px-5 py-4 text-testo-soft">{{ $agenzia->citta }} ({{ $agenzia->provincia }})</td>
                            <td class="px-5 py-4 text-testo-soft">{{ $agenzia->user?->name ?? '—' }}</td>
                            <td class="px-5 py-4 tabular-nums text-testo-soft">{{ $agenzia->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'font-sans text-[10px] tracking-[0.18em] uppercase',
                                    'text-successo' => $agenzia->stato === StatoAgenzia::Approvata,
                                    'text-errore' => in_array($agenzia->stato, [StatoAgenzia::Rifiutata, StatoAgenzia::Sospesa], true),
                                    'text-oro-scuro' => $agenzia->stato === StatoAgenzia::InAttesa,
                                ])>
                                    {{ $agenzia->stato->etichetta() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $agenzie->links() }}
        </div>
    @endif
@endsection
