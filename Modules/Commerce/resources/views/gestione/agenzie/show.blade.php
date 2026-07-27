@extends('layouts.gestione')

@section('title', $agenzia->ragione_sociale.' — Gestione MemorAI')
@section('titolo', $agenzia->ragione_sociale)

@section('gestione')
    @php
        use Modules\Commerce\Enums\StatoAgenzia;

        $dati = [
            'Partita IVA' => $agenzia->partita_iva,
            'Codice fiscale' => $agenzia->codice_fiscale,
            'Codice SdI' => $agenzia->codice_sdi,
            'PEC' => $agenzia->pec,
            'Sede' => $agenzia->indirizzoCompleto(),
            'Telefono' => $agenzia->telefono,
        ];

        $referente = [
            'Nome' => $agenzia->user?->name,
            'Email' => $agenzia->user?->email,
            'Telefono' => $agenzia->user?->telefono,
        ];
    @endphp

    <a href="{{ route('gestione.agenzie.index') }}"
       class="font-sans text-[11px] tracking-[0.22em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutte le agenzie
    </a>

    <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_22rem]">

        {{-- anagrafica --}}
        <div class="space-y-px bg-caffe/15 border border-caffe/15 self-start">
            <section class="bg-bianco px-7 py-7">
                <h2 class="font-serif text-xl font-medium">Agenzia</h2>
                <dl class="mt-5 grid gap-x-8 gap-y-3 sm:grid-cols-2 font-sans font-light text-[14px]">
                    @foreach ($dati as $etichetta => $valore)
                        <div>
                            <dt class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">{{ $etichetta }}</dt>
                            <dd class="mt-1 text-testo">{{ $valore ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="bg-bianco px-7 py-7">
                <h2 class="font-serif text-xl font-medium">Referente</h2>
                <dl class="mt-5 grid gap-x-8 gap-y-3 sm:grid-cols-2 font-sans font-light text-[14px]">
                    @foreach ($referente as $etichetta => $valore)
                        <div>
                            <dt class="font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">{{ $etichetta }}</dt>
                            <dd class="mt-1 text-testo">{{ $valore ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="bg-bianco px-7 py-7">
                <h2 class="font-serif text-xl font-medium">Agente di vendita</h2>
                <p class="mt-2 font-sans font-light text-[14px] text-testo-soft">
                    Chi segue questa agenzia — serve anche per lo sconto personalizzato.
                </p>
                <form method="POST" action="{{ route('gestione.agenzie.agente', $agenzia) }}" class="mt-4 flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="flex-1 min-w-[12rem]">
                        <select name="agente_vendita_id"
                                class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                                       focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                            <option value="">Nessuno</option>
                            @foreach ($agenti as $agente)
                                <option value="{{ $agente->id }}" @selected($agenzia->agente_vendita_id === $agente->id)>{{ $agente->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-secondary-button type="submit">Assegna</x-secondary-button>
                </form>
            </section>

            @if ($agenzia->note_interne || $agenzia->motivo_rifiuto)
                <section class="bg-bianco px-7 py-7">
                    <h2 class="font-serif text-xl font-medium">Annotazioni</h2>

                    @if ($agenzia->motivo_rifiuto)
                        <p class="mt-4 font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">
                            Motivo comunicato all'agenzia
                        </p>
                        <p class="mt-1 font-sans font-light text-[14px] leading-relaxed">{{ $agenzia->motivo_rifiuto }}</p>
                    @endif

                    @if ($agenzia->note_interne)
                        <p class="mt-4 font-sans text-[10px] tracking-[0.2em] uppercase text-testo-soft">
                            Note interne (non visibili all'agenzia)
                        </p>
                        <p class="mt-1 font-sans font-light text-[14px] leading-relaxed">{{ $agenzia->note_interne }}</p>
                    @endif
                </section>
            @endif
        </div>

        {{-- decisione --}}
        <aside class="border border-caffe/15 bg-panna/50 px-6 py-7 self-start">
            <span class="font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">Stato</span>
            <p @class([
                'mt-2 font-serif text-2xl',
                'text-successo' => $agenzia->stato === StatoAgenzia::Approvata,
                'text-errore' => in_array($agenzia->stato, [StatoAgenzia::Rifiutata, StatoAgenzia::Sospesa], true),
            ])>
                {{ $agenzia->stato->etichetta() }}
            </p>

            @if ($agenzia->stato_aggiornato_at)
                <p class="mt-2 font-sans font-light text-[12px] text-testo-soft">
                    Aggiornato il {{ $agenzia->stato_aggiornato_at->format('d/m/Y H:i') }}
                </p>
            @endif

            <hr class="my-6 h-px w-full border-0 bg-caffe/15">

            @if ($agenzia->stato !== StatoAgenzia::Approvata)
                <form method="POST" action="{{ route('gestione.agenzie.approva', $agenzia) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="note_approva" value="Note interne (facoltative)" />
                        <textarea id="note_approva" name="note_interne" rows="2"
                                  class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                         text-[14px] focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"></textarea>
                        <x-input-error :messages="$errors->get('note_interne')" />
                    </div>
                    <x-primary-button class="w-full">Approva</x-primary-button>
                </form>
            @endif

            @if ($agenzia->stato === StatoAgenzia::Approvata)
                <form method="POST" action="{{ route('gestione.agenzie.sospendi', $agenzia) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="note_sospendi" value="Note interne (facoltative)" />
                        <textarea id="note_sospendi" name="note_interne" rows="2"
                                  class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                         text-[14px] focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"></textarea>
                    </div>
                    <x-danger-button class="w-full">Sospendi</x-danger-button>
                </form>
            @endif

            @if ($agenzia->stato === StatoAgenzia::InAttesa)
                <hr class="my-6 h-px w-full border-0 bg-caffe/15">

                <form method="POST" action="{{ route('gestione.agenzie.rifiuta', $agenzia) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="motivo_rifiuto" value="Motivo (lo vede l'agenzia)" />
                        <textarea id="motivo_rifiuto" name="motivo_rifiuto" rows="3" required
                                  class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                         text-[14px] focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"></textarea>
                        <x-input-error :messages="$errors->get('motivo_rifiuto')" />
                    </div>
                    <x-danger-button class="w-full">Non approvare</x-danger-button>
                </form>
            @endif
        </aside>
    </div>
@endsection
