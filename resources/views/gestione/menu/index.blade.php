@extends('layouts.gestione')

@section('title', 'Menu — Gestione MemorAI')
@section('titolo', 'Menu e footer')

@section('gestione')
    <p class="font-sans font-light text-[14px] text-testo-soft max-w-xl">
        Le voci del menu principale e delle colonne del footer del sito pubblico.
    </p>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('gestione.menu.create') }}"
           class="inline-flex items-center justify-center gap-2 cursor-pointer select-none
                  font-sans uppercase text-[12px] tracking-[0.22em] px-8 py-3.5
                  bg-oro text-bianco hover:bg-oro-scuro transition-all duration-300 ease-out">
            Nuova voce
        </a>
    </div>

    <div class="mt-8 space-y-10">
        @foreach ($zone as $zona)
            <section>
                <h2 class="font-serif text-xl font-medium">{{ $zona->etichetta() }}</h2>

                @php $voci = $perZona->get($zona->value, collect()); @endphp

                @if ($voci->isEmpty())
                    <p class="mt-3 font-sans font-light text-[14px] text-testo-soft">Nessuna voce.</p>
                @else
                    <div class="mt-4 overflow-x-auto border border-caffe/15">
                        <table class="w-full min-w-[38rem] border-collapse">
                            <thead>
                                <tr class="bg-panna text-left font-sans text-[10px] tracking-[0.22em] uppercase text-testo-soft">
                                    <th class="px-5 py-3 font-normal">Etichetta</th>
                                    <th class="px-5 py-3 font-normal">Indirizzo</th>
                                    <th class="px-5 py-3 font-normal">Stato</th>
                                    <th class="px-5 py-3 font-normal"></th>
                                </tr>
                            </thead>
                            <tbody class="font-sans font-light text-[14px]">
                                @foreach ($voci as $voce)
                                    <tr class="border-t border-caffe/10">
                                        <td class="px-5 py-3">
                                            <a href="{{ route('gestione.menu.edit', $voce) }}" class="hover:text-oro-scuro transition-colors duration-300">
                                                {{ $voce->etichetta }}
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-testo-soft">{{ $voce->url }}</td>
                                        <td class="px-5 py-3">
                                            <span class="font-sans text-[10px] tracking-[0.18em] uppercase {{ $voce->is_active ? 'text-successo' : 'text-errore' }}">
                                                {{ $voce->is_active ? 'Attiva' : 'Disattiva' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <form method="POST" action="{{ route('gestione.menu.destroy', $voce) }}"
                                                  onsubmit="return confirm('Eliminare questa voce?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-sans text-[11px] tracking-[0.14em] uppercase text-errore hover:underline">
                                                    Elimina
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    </div>
@endsection
