@extends('layouts.account')

@php $nuovo = ! $necrologio->exists; @endphp

@section('title', ($nuovo ? 'Nuovo necrologio' : 'Necrologio').' — MemorAI')
@section('titolo', $nuovo ? 'Nuovo necrologio' : ($necrologio->defunto?->nomeCompleto() ?? 'Necrologio'))
@section('sottotitolo', 'La card che condividete, con l\'orario del trigesimo e il manifesto.')

@section('account')
@if (session('stato'))
    <p class="mb-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
@endif

<div class="space-y-px bg-caffe/15 border border-caffe/15">

    {{-- ============ contenuto ============ --}}
    <section class="bg-bianco px-7 py-8">
        <h2 class="font-serif text-2xl font-medium">Cosa dice la card</h2>

        <form method="POST" enctype="multipart/form-data" class="mt-6 max-w-2xl space-y-6"
              action="{{ $nuovo ? route('necrologi.salva') : route('necrologi.aggiorna', $necrologio) }}">
            @csrf
            @unless ($nuovo) @method('patch') @endunless

            @if ($nuovo)
                <div>
                    <x-input-label for="defunto_id" value="Per chi" />
                    <select id="defunto_id" name="defunto_id" required
                            class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                   focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                        <option value="">— scegli —</option>
                        @foreach ($defunti as $d)
                            <option value="{{ $d->id }}" @selected(old('defunto_id', $defuntoPreselezionato) == $d->id)>{{ $d->nomeCompleto() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('defunto_id')" />
                </div>
            @endif

            <div>
                <x-input-label value="Occasione" />
                @php $occasioneAttuale = old('occasione', $necrologio->occasione?->value ?? 'trigesimo'); @endphp
                <div class="mt-1 flex flex-wrap gap-4">
                    @foreach (['funerale' => 'Funerale', 'trigesimo' => 'Trigesimo', 'anniversario' => 'Anniversario'] as $valore => $etichetta)
                        <label class="inline-flex items-center gap-2 font-sans font-light text-[14px]">
                            <input type="radio" name="occasione" value="{{ $valore }}"
                                onchange="document.getElementById('campo-numero-anniversario').style.display = this.value === 'anniversario' ? 'block' : 'none'"
                                @checked($occasioneAttuale === $valore)>
                            {{ $etichetta }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('occasione')" />

                <div id="campo-numero-anniversario" class="mt-3 max-w-[10rem]" style="display: {{ $occasioneAttuale === 'anniversario' ? 'block' : 'none' }}">
                    <x-input-label for="numero_anniversario" value="Quale anniversario" />
                    <x-text-input id="numero_anniversario" name="numero_anniversario" type="number" min="1" max="99"
                        :value="old('numero_anniversario', $necrologio->numero_anniversario)" />
                    <x-input-error :messages="$errors->get('numero_anniversario')" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="trigesimo_at" value="Quando è il trigesimo" />
                    <x-text-input id="trigesimo_at" name="trigesimo_at" type="datetime-local"
                        :value="old('trigesimo_at', $necrologio->trigesimo_at?->format('Y-m-d\TH:i'))" />
                    <x-input-error :messages="$errors->get('trigesimo_at')" />
                </div>
                <div>
                    <x-input-label for="trigesimo_luogo" value="Dove" />
                    <x-text-input id="trigesimo_luogo" name="trigesimo_luogo" placeholder="Chiesa di San Carlo"
                        :value="old('trigesimo_luogo', $necrologio->trigesimo_luogo)" />
                    <x-input-error :messages="$errors->get('trigesimo_luogo')" />
                </div>
            </div>

            <div>
                <x-input-label for="trigesimo_indirizzo" value="Indirizzo" />
                <x-text-input id="trigesimo_indirizzo" name="trigesimo_indirizzo" placeholder="Via Roma 12, Milano"
                    :value="old('trigesimo_indirizzo', $necrologio->trigesimo_indirizzo)" />
            </div>

            <div>
                <x-input-label for="testo" value="Poche righe di annuncio" />
                <textarea id="testo" name="testo" rows="4"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                 text-[15px] leading-relaxed focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"
                >{{ old('testo', $necrologio->testo) }}</textarea>
            </div>

            <div>
                <x-input-label for="manifesto" value="Il manifesto (PDF o immagine)" />
                <input id="manifesto" name="manifesto" type="file" accept=".pdf,image/*"
                       class="block w-full font-sans font-light text-[14px] text-testo-soft
                              file:mr-4 file:border-0 file:bg-caffe file:px-5 file:py-2.5
                              file:font-sans file:text-[11px] file:uppercase file:tracking-[0.2em] file:text-bianco">
                <x-input-error :messages="$errors->get('manifesto')" />
                @if ($necrologio->manifesto)
                    <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">
                        Allegato:
                        <a href="{{ asset('storage/'.$necrologio->manifesto) }}" target="_blank" rel="noopener"
                           class="text-oro-scuro underline underline-offset-4 decoration-oro/40">guarda quello attuale</a>
                    </p>
                @endif
                @unless ($nuovo)
                    <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">
                        Oppure
                        <a href="{{ route('necrologi.manifesto', $necrologio) }}"
                           class="text-oro-scuro underline underline-offset-4 decoration-oro/40">disegnalo qui</a>,
                        formati A3/A4 e manifesto, con il QR del necrologio già pronto.
                    </p>
                @endunless
            </div>

            <x-primary-button>{{ $nuovo ? 'Crea il necrologio' : 'Salva le modifiche' }}</x-primary-button>
        </form>
    </section>

    @unless ($nuovo)
        {{-- ============ card social ============ --}}
        <section class="bg-bianco px-7 py-8">
            <h2 class="font-serif text-2xl font-medium">La card</h2>
            <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                Quello che si vede su WhatsApp e Facebook prima ancora di aprire il link.
            </p>

            <div class="mt-5 flex flex-wrap items-end gap-6">
                @if ($necrologio->og_image)
                    <img src="{{ asset('storage/'.$necrologio->og_image) }}" alt="Anteprima della card"
                         class="h-32 w-auto border border-caffe/15">
                @else
                    <div class="flex h-32 w-60 items-center justify-center border border-dashed border-caffe/25 bg-panna/40
                                font-sans text-[11px] text-testo-soft text-center px-3">
                        Nessuna card ancora
                    </div>
                @endif

                <a href="{{ route('necrologi.designer', $necrologio) }}"
                   class="inline-flex items-center justify-center font-sans uppercase text-[11px]
                          tracking-[0.2em] px-6 py-3 bg-caffe text-bianco
                          hover:bg-oro-scuro transition-colors duration-300">
                    {{ $necrologio->og_image ? 'Modifica la card' : 'Disegna la card' }}
                </a>
            </div>
        </section>

        {{-- ============ consenso ============ --}}
        <section class="bg-bianco px-7 py-8">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="font-serif text-2xl font-medium">Il consenso alla pubblicazione</h2>
                @if ($necrologio->pubblicazione_consenso)
                    <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Registrato</span>
                @endif
            </div>

            <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                È un consenso <strong class="font-normal text-testo">diverso</strong> da quello raccolto per
                realizzare gli articoli: lì la fotografia resta in famiglia, qui va online e la vede
                chiunque abbia l'indirizzo.
            </p>
            <p class="mt-3 max-w-2xl border-l-2 border-errore/40 pl-4 font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                Ditelo alla famiglia con parole vostre: quando la card gira su WhatsApp o Facebook,
                quelle piattaforme ne tengono una copia. Ritirando la pagina noi la spegniamo subito,
                ma quello che è già circolato nelle chat non torna indietro.
            </p>

            @if ($necrologio->pubblicazione_consenso)
                <p class="mt-5 font-sans font-light text-[14px] text-testo">
                    Autorizzato da <strong class="font-normal">{{ $necrologio->pubblicazione_autorizzata_da }}</strong>
                    ({{ $necrologio->pubblicazione_parentela }})
                    il {{ $necrologio->pubblicazione_autorizzata_at?->format('d/m/Y H:i') }}.
                </p>
                <form method="POST" action="{{ route('necrologi.revoca', $necrologio) }}" class="mt-5">
                    @csrf
                    <x-danger-button>Revoca il consenso</x-danger-button>
                </form>
            @else
                <form method="POST" action="{{ route('necrologi.consenso', $necrologio) }}" class="mt-6 max-w-xl space-y-5">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="autorizzata_da" value="Chi autorizza" />
                            <x-text-input id="autorizzata_da" name="autorizzata_da" required :value="old('autorizzata_da')" />
                            <x-input-error :messages="$errors->get('autorizzata_da')" />
                        </div>
                        <div>
                            <x-input-label for="parentela" value="Parentela" />
                            <x-text-input id="parentela" name="parentela" required placeholder="figlia, coniuge…"
                                :value="old('parentela')" />
                            <x-input-error :messages="$errors->get('parentela')" />
                        </div>
                    </div>

                    <label for="conferma" class="flex items-start gap-3 cursor-pointer">
                        <input id="conferma" name="conferma" type="checkbox" value="1" class="mt-1 h-4 w-4 accent-oro">
                        <span class="font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                            Confermo che il familiare ha autorizzato la pubblicazione online e che gli è
                            stato spiegato cosa comporta la condivisione sui social.
                        </span>
                    </label>
                    <x-input-error :messages="$errors->get('conferma')" />

                    <x-primary-button>Registra il consenso</x-primary-button>
                </form>
            @endif
        </section>

        {{-- ============ pubblicazione ============ --}}
        <section class="bg-panna/60 px-7 py-8">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="font-serif text-2xl font-medium">Online</h2>
                @if ($necrologio->pubblico())
                    <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Pubblicato</span>
                @endif
            </div>

            @if ($necrologio->pubblico())
                <p class="mt-3 font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    Indirizzo pubblico — è questo che condividete e che finisce nel QR del manifesto:
                </p>
                <p class="mt-2 break-all font-sans text-[14px] text-oro-scuro">
                    <a href="{{ $necrologio->url($agenzia->slug) }}" target="_blank" rel="noopener"
                       class="underline underline-offset-4 decoration-oro/40">{{ $necrologio->url($agenzia->slug) }}</a>
                </p>
                @if ($necrologio->pubblicato_fino_al)
                    <p class="mt-3 font-sans font-light text-[13px] text-testo-soft">
                        Si spegne da solo il {{ $necrologio->pubblicato_fino_al->format('d/m/Y') }}.
                    </p>
                @endif

                <form method="POST" action="{{ route('necrologi.ritira', $necrologio) }}" class="mt-6">
                    @csrf
                    <x-danger-button>Ritira dalla pubblicazione</x-danger-button>
                </form>
            @else
                <form method="POST" action="{{ route('necrologi.pubblica', $necrologio) }}" class="mt-4 space-y-5 max-w-md">
                    @csrf
                    <div>
                        <x-input-label for="fino_al" value="Resta online fino al" />
                        <x-text-input id="fino_al" name="fino_al" type="date"
                            :value="old('fino_al', $necrologio->scadenzaPredefinita()->format('Y-m-d'))" />
                        <p class="mt-2 font-sans font-light text-[12px] text-testo-soft">
                            Predefinito: quindici giorni dopo il trigesimo. Si può prolungare.
                        </p>
                        <x-input-error :messages="$errors->get('fino_al')" />
                    </div>

                    @if ($necrologio->pubblicazione_consenso)
                        <x-primary-button>Pubblica</x-primary-button>
                    @else
                        <p class="font-sans font-light text-[13px] text-errore">
                            Prima serve il consenso della famiglia, qui sopra.
                        </p>
                    @endif
                </form>
            @endif
        </section>

        {{-- ============ embed sul sito dell'agenzia ============ --}}
        @unless ($nuovo)
            @php
                $embedScaduto = $necrologio->embed_abilitato && $necrologio->embedScaduto();
            @endphp
            <section class="bg-bianco px-7 py-8">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 class="font-serif text-2xl font-medium">Embed sul vostro sito</h2>
                    @if ($embedScaduto)
                        <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-errore">Scaduto</span>
                    @elseif ($necrologio->embed_abilitato)
                        <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Acquistato</span>
                    @endif
                </div>
                <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                    L'indirizzo qui sopra si condivide già gratis su social e WhatsApp. Se avete un sito vostro e
                    volete la card del necrologio incorporata dentro una sua pagina, questo è il codice da incollare.
                </p>

                @if ($necrologio->embeddabile())
                    <div class="mt-6 max-w-2xl">
                        <p class="font-sans text-[11px] tracking-[0.2em] uppercase text-oro-scuro">
                            Incollate questo nella pagina del vostro sito
                        </p>
                        <textarea readonly rows="4" onclick="this.select()"
                                  class="mt-2 w-full bg-panna/40 border border-caffe/15 px-4 py-3 font-mono text-[12px] text-testo"
                        ><iframe src="{{ $necrologio->url($agenzia->slug) }}" id="memorai-necrologio-{{ $necrologio->id }}" style="width:100%;border:0;display:block;" title="Necrologio"></iframe>
<script>window.addEventListener('message',function(e){if(e.data&&e.data.memorai&&e.data.tipo==='necrologio-altezza'){var f=document.getElementById('memorai-necrologio-{{ $necrologio->id }}');if(f)f.style.height=e.data.altezza+'px';}});</script></textarea>
                        <p class="mt-2 font-sans font-light text-[12px] text-testo-soft">
                            @if ($necrologio->embed_scaduto_il)
                                Attivo fino al {{ $necrologio->embed_scaduto_il->format('d/m/Y') }}.
                            @else
                                Senza scadenza.
                            @endif
                            Niente barre di scorrimento: si ridimensiona da solo, l'altezza segue il contenuto.
                        </p>
                    </div>
                @else
                    @if ($embedScaduto)
                        <p class="mt-5 font-sans font-light text-[13px] text-errore">
                            Era a termine ed è scaduto il {{ $necrologio->embed_scaduto_il->format('d/m/Y') }}. Rinnovatelo qui sotto.
                        </p>
                    @elseif ($necrologio->embed_abilitato)
                        <p class="mt-5 font-sans font-light text-[13px] text-testo-soft italic">
                            Acquistato: il codice compare qui appena il necrologio è pubblicato online (qui sopra).
                        </p>
                    @endif

                    @if ($servizioEmbed && (! $necrologio->embed_abilitato || $embedScaduto))
                        @php
                            $costoTermine = $servizioEmbed->costo_crediti_a_termine;
                            $costoMinimo = $costoTermine !== null ? min($costoTermine, $servizioEmbed->costo_crediti) : $servizioEmbed->costo_crediti;
                        @endphp
                        <form method="POST" action="{{ route('necrologi.embed', $necrologio) }}" class="mt-6 max-w-md space-y-4">
                            @csrf
                            <p class="font-sans text-[13px] text-testo-soft">Avete {{ $creditiSaldo }} crediti.</p>

                            <div class="space-y-3">
                                @if ($costoTermine !== null)
                                    <label class="flex items-start gap-3 font-sans text-[13px] text-testo">
                                        <input type="radio" name="tipo" value="termine" class="mt-1" checked>
                                        <span class="block w-full">
                                            <strong class="font-normal">A termine</strong> — {{ $costoTermine }} crediti.
                                            <span class="block mt-2">
                                                <x-input-label for="embed_fino_al" value="Resta attivo fino al" />
                                                <x-text-input id="embed_fino_al" name="fino_al" type="date"
                                                    max="{{ $embedTermineMax }}" :value="old('fino_al')" class="mt-1" />
                                                <span class="block mt-1 font-sans font-light text-[11px] text-testo-soft">
                                                    Non oltre sei mesi da oggi.
                                                </span>
                                                <x-input-error :messages="$errors->get('fino_al')" />
                                            </span>
                                        </span>
                                    </label>
                                @endif

                                <label class="flex items-start gap-3 font-sans text-[13px] text-testo">
                                    <input type="radio" name="tipo" value="perpetuo" class="mt-1" @if($costoTermine === null) checked @endif>
                                    <span>
                                        <strong class="font-normal">Perpetuo</strong> — {{ $servizioEmbed->costo_crediti }} crediti
                                        (garantito fino a tre anni).
                                    </span>
                                </label>
                            </div>

                            @if ($creditiSaldo >= $costoMinimo)
                                <x-button type="submit" class="mt-3">{{ $necrologio->embed_abilitato ? 'Rinnova embed' : 'Acquista embed' }}</x-button>
                            @else
                                <p class="mt-3 font-sans font-light text-[13px] text-errore">
                                    Crediti insufficienti — <a href="{{ route('servizi') }}" class="underline underline-offset-4 decoration-oro/40">ricaricate da qui</a>.
                                </p>
                            @endif
                        </form>
                    @endif
                @endif
            </section>
        @endunless

        {{-- ============ messaggi di cordoglio ============ --}}
        @if ($necrologio->messaggiCordoglio->isNotEmpty())
            <section class="bg-bianco px-7 py-8">
                <h2 class="font-serif text-2xl font-medium">Messaggi di cordoglio</h2>
                <p class="mt-2 font-sans font-light text-[13px] text-testo-soft">
                    Lasciati senza account sulla pagina del manifesto. Puoi toglierne uno se fuori luogo.
                </p>
                <div class="mt-5 max-w-2xl space-y-4">
                    @foreach ($necrologio->messaggiCordoglio as $messaggio)
                        <div class="flex items-start justify-between gap-4 border border-caffe/15 px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-sans text-[13px] font-medium">{{ $messaggio->nome }}</p>
                                <p class="mt-1 font-sans font-light text-[14px] leading-relaxed text-testo-soft whitespace-pre-line">{{ $messaggio->messaggio }}</p>
                                <p class="mt-1 font-sans text-[11px] text-testo-soft/70">{{ $messaggio->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <form method="POST" action="{{ route('necrologi.messaggi.elimina', [$necrologio, $messaggio]) }}"
                                  onsubmit="return confirm('Eliminare questo messaggio?')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="font-sans text-[11px] uppercase tracking-[0.15em] text-errore hover:underline">
                                    Elimina
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endunless
</div>

<p class="mt-8">
    <a href="{{ route('necrologi.index') }}"
       class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Tutti i necrologi
    </a>
</p>
@endsection
