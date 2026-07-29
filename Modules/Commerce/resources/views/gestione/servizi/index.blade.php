@extends('layouts.gestione')

@section('title', 'Servizi editor — Gestione MemorAI')
@section('titolo', 'Servizi editor')

@section('gestione')
    <p class="font-sans font-light text-[14px] text-testo-soft max-w-2xl">
        I servizi che un'agenzia può attivare a crediti da "Nuovo ordine": ricordini, manifesti, necrologi. Righe
        fisse — qui si cambia solo il costo in crediti e se il servizio è proponibile.
    </p>

    @if (session('stato'))
        <p class="mt-6 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
    @endif

    <div class="mt-8 overflow-x-auto border border-caffe/15">
        <table class="w-full min-w-[36rem] border-collapse">
            <thead>
                <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                    <th class="px-5 py-4 font-normal">Servizio</th>
                    <th class="px-5 py-4 font-normal">Costo in crediti</th>
                    <th class="px-5 py-4 font-normal">Attivo</th>
                    <th class="px-5 py-4 font-normal"></th>
                </tr>
            </thead>
            <tbody class="font-sans font-light text-[14px]">
                @foreach ($servizi as $servizio)
                    @php $formId = 'servizio-'.$servizio->id; @endphp
                    <tr class="border-t border-caffe/10">
                        <td class="px-5 py-4">
                            <span class="font-serif text-[17px] text-testo">{{ $servizio->etichetta }}</span>
                            <span class="block font-sans text-[11px] text-testo-soft">{{ $servizio->codice }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <input type="number" form="{{ $formId }}" name="costo_crediti" min="0" value="{{ old('costo_crediti', $servizio->costo_crediti) }}"
                                class="w-24 border-caffe/25 focus:border-oro focus:ring-oro rounded-md shadow-sm text-sm">
                        </td>
                        <td class="px-5 py-4">
                            <input type="checkbox" form="{{ $formId }}" name="attivo" value="1" @checked($servizio->attivo)>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <form id="{{ $formId }}" method="POST" action="{{ route('gestione.servizi.aggiorna', $servizio) }}">
                                @csrf
                                @method('put')
                            </form>
                            <x-button type="submit" form="{{ $formId }}">Salva</x-button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
