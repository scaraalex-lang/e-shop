@extends('layouts.gestione')

@section('title', 'Pannello')

@section('content')
<h1 class="font-serif text-4xl text-caffe">Pannello</h1>
<p class="mt-3 font-sans font-light text-testo-soft leading-relaxed max-w-2xl">
    Da qui si governa cosa vede chi arriva in home e si seguono le pratiche aperte.
</p>

{{-- ---------- numeri ---------- --}}
<div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-px bg-caffe/20 border-2 border-caffe">
    @foreach ([
        ['Slide pubblicate', $slideAttive . ' / ' . $slideTotali, route('gestione.slide.index')],
        ['Pratiche', $pratiche, route('gestione.pratiche')],
        ['Ricordini salvati', $ricordini, null],
        ['Senza consenso', $senzaConsenso, route('gestione.pratiche')],
    ] as [$etichetta, $valore, $href])
        <div class="bg-bianco px-5 py-6">
            <span class="font-sans text-[11px] tracking-[0.24em] uppercase text-testo-soft">{{ $etichetta }}</span>
            <p class="mt-2 font-serif text-4xl text-caffe">{{ $valore }}</p>
            @if ($href)
                <a href="{{ $href }}" class="mt-2 inline-block font-sans text-[11px] tracking-[0.16em] uppercase text-oro-scuro hover:text-caffe transition-colors">
                    Apri →
                </a>
            @endif
        </div>
    @endforeach
</div>

{{-- ---------- scorciatoie ---------- --}}
<h2 class="mt-14 font-serif text-2xl text-caffe">Scorciatoie</h2>
<div class="mt-5 flex flex-wrap gap-4">
    <x-button variant="piena" :href="route('gestione.slide.create')">Nuova slide</x-button>
    <x-button variant="contornata" :href="url('/prenota/ricordino')">Apri una pratica</x-button>
    <x-button variant="contornata" :href="url('/studio/foto')">Foto Manager</x-button>
    <x-button variant="contornata" :href="url('/studio/ricordino')">Ricordino Designer</x-button>
</div>

{{-- ---------- ultime pratiche ---------- --}}
<h2 class="mt-14 font-serif text-2xl text-caffe">Ultime pratiche</h2>

@if ($ultime->isEmpty())
    <p class="mt-4 font-sans font-light text-testo-soft">Ancora nessuna pratica registrata.</p>
@else
    <div class="mt-5 overflow-x-auto border-2 border-caffe">
        <table class="w-full text-left font-sans text-[14px]">
            <thead class="bg-panna">
                <tr class="text-[11px] tracking-[0.2em] uppercase text-testo-soft">
                    <th class="px-4 py-3 font-normal">Persona</th>
                    <th class="px-4 py-3 font-normal">Consenso</th>
                    <th class="px-4 py-3 font-normal">Ricordini</th>
                    <th class="px-4 py-3 font-normal">Aperta il</th>
                    <th class="px-4 py-3 font-normal text-right">Editor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ultime as $p)
                    <tr class="border-t border-caffe/15">
                        <td class="px-4 py-3 font-serif text-lg text-caffe">{{ $p->nomeCompleto() }}</td>
                        <td class="px-4 py-3">
                            @if ($p->gdpr_consenso)
                                <span class="text-oro-scuro">registrato</span>
                            @else
                                <span class="text-testo-soft">mancante</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $p->ricordini_count }}</td>
                        <td class="px-4 py-3 text-testo-soft">{{ $p->created_at?->format('d/m/Y') }}</td>
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
@endif
@endsection
