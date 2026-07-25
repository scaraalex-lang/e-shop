@extends('layouts.gestione')

@section('title', 'Pratiche')

@section('content')
<h1 class="font-serif text-4xl text-caffe">Pratiche</h1>
<p class="mt-3 font-sans font-light text-testo-soft leading-relaxed max-w-2xl">
    Ogni pratica è una persona da ricordare, nata dal modulo di prenotazione.
    Da qui si entra nel Foto Manager e nel Designer già agganciati ai suoi dati.
</p>

@if ($pratiche->isEmpty())
    <p class="mt-10 font-sans font-light text-testo-soft">Ancora nessuna pratica registrata.</p>
@else
    <div class="mt-8 overflow-x-auto border-2 border-caffe">
        <table class="w-full text-left font-sans text-[14px]">
            <thead class="bg-panna">
                <tr class="text-[11px] tracking-[0.2em] uppercase text-testo-soft">
                    <th class="px-4 py-3 font-normal">Persona</th>
                    <th class="px-4 py-3 font-normal">Date</th>
                    <th class="px-4 py-3 font-normal">Consenso</th>
                    <th class="px-4 py-3 font-normal">Ricordini</th>
                    <th class="px-4 py-3 font-normal">Aperta il</th>
                    <th class="px-4 py-3 font-normal text-right">Editor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pratiche as $p)
                    <tr class="border-t border-caffe/15 align-top">
                        <td class="px-4 py-3">
                            <span class="font-serif text-lg text-caffe">{{ $p->nomeCompleto() }}</span>
                            @if ($p->frase)
                                <p class="mt-1 font-light text-[13px] text-testo-soft max-w-xs">{{ \Illuminate\Support\Str::limit($p->frase, 90) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-testo-soft whitespace-nowrap">
                            {{ $p->data_nascita?->format('d/m/Y') ?? '—' }}
                            <span class="text-caffe/30">→</span>
                            {{ $p->data_morte?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($p->gdpr_consenso)
                                <span class="text-oro-scuro">registrato</span>
                                @if ($p->gdpr_autorizzato_da)
                                    <p class="mt-1 font-light text-[12px] text-testo-soft">
                                        {{ $p->gdpr_autorizzato_da }}@if ($p->gdpr_parentela) ({{ $p->gdpr_parentela }})@endif
                                    </p>
                                @endif
                            @else
                                <span class="text-testo-soft">mancante</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $p->ricordini_count }}</td>
                        <td class="px-4 py-3 text-testo-soft whitespace-nowrap">{{ $p->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('studio.foto', ['defunto' => $p->id]) }}"
                               class="text-oro-scuro hover:text-caffe transition-colors">Foto</a>
                            <span class="text-caffe/30 mx-2">·</span>
                            <a href="{{ route('studio.ricordino', ['defunto' => $p->id]) }}"
                               class="text-oro-scuro hover:text-caffe transition-colors">Ricordino</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $pratiche->links() }}
    </div>
@endif
@endsection
