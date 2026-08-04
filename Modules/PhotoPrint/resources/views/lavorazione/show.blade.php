@extends('layouts.account')

@section('title', 'Lavorazione ordine '.$ordine->numero.' — MemorAI')
@section('titolo', 'La fotografia del ricordo')
@section('sottotitolo', 'Ordine '.$ordine->numero.' · qui prepariamo insieme il ritratto e le parole.')

@section('account')

@if (session('stato'))
    <p class="mb-8 border-l-2 border-successo bg-panna px-5 py-4 font-sans text-[13px]">{{ session('stato') }}</p>
@endif

@php
    $passi = [
        ['n' => 1, 'titolo' => 'I dati della persona', 'fatto' => (bool) $defunto],
        ['n' => 2, 'titolo' => 'La fotografia', 'fatto' => $foto->isNotEmpty()],
        ['n' => 3, 'titolo' => 'Il ricordino e la preghiera', 'fatto' => (bool) $ricordino],
        ['n' => 4, 'titolo' => 'La tua approvazione', 'fatto' => $ricordino?->stato === 'approvato'],
    ];
@endphp

{{-- ============ i quattro passi ============ --}}
<ol class="flex flex-wrap gap-x-8 gap-y-3 font-sans text-[11px] tracking-[0.16em] uppercase">
    @foreach ($passi as $passo)
        <li @class(['flex items-center gap-2', 'text-successo' => $passo['fatto'], 'text-testo-soft/60' => ! $passo['fatto']])>
            <span aria-hidden="true" class="w-[0.45rem] h-[0.45rem] rotate-45 {{ $passo['fatto'] ? 'bg-successo' : 'bg-caffe/25' }}"></span>
            {{ $passo['titolo'] }}
        </li>
    @endforeach
</ol>

<div class="mt-10 space-y-px bg-caffe/15 border border-caffe/15">

    {{-- ============ 1. il defunto ============ --}}
    <section class="bg-bianco px-7 py-8">
        <header class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-serif text-2xl font-medium">Dati del defunto</h2>
            @if ($defunto)
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Registrato</span>
            @endif
        </header>

        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Questi dati compongono il ricordino: nome, date e la frase che volete accanto al ritratto.
        </p>

        <form method="POST" action="{{ route('lavorazione.defunto', $ordine) }}" class="mt-6 max-w-2xl space-y-6">
            @csrf

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="nome" value="Nome" />
                    <x-text-input id="nome" name="nome" required :value="old('nome', $defunto?->nome)" />
                    <x-input-error :messages="$errors->get('nome')" />
                </div>
                <div>
                    <x-input-label for="cognome" value="Cognome" />
                    <x-text-input id="cognome" name="cognome" required :value="old('cognome', $defunto?->cognome)" />
                    <x-input-error :messages="$errors->get('cognome')" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="sesso" value="Sesso" />
                    <select id="sesso" name="sesso"
                        class="mt-1 block w-full border-caffe/25 focus:border-oro focus:ring-oro rounded-md shadow-sm">
                        <option value="">Non indicato</option>
                        <option value="M" @selected(old('sesso', $defunto?->sesso) === 'M')>Maschile</option>
                        <option value="F" @selected(old('sesso', $defunto?->sesso) === 'F')>Femminile</option>
                    </select>
                    <x-input-error :messages="$errors->get('sesso')" />
                    <p class="mt-1 text-xs text-testo-soft">Serve solo per coniugare correttamente il necrologio ("è venuto/a a mancare").</p>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="data_nascita" value="Data di nascita" />
                    <x-text-input id="data_nascita" name="data_nascita" type="date"
                        :value="old('data_nascita', $defunto?->data_nascita?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('data_nascita')" />
                </div>
                <div>
                    <x-input-label for="data_morte" value="Data del decesso" />
                    <x-text-input id="data_morte" name="data_morte" type="date"
                        :value="old('data_morte', $defunto?->data_morte?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('data_morte')" />
                </div>
            </div>

            <div>
                <x-input-label for="frase" value="Frase di apertura" />
                <x-text-input id="frase" name="frase"
                    :value="old('frase', $defunto?->frase ?? 'È mancata all\'affetto dei suoi cari')" />
                <x-input-error :messages="$errors->get('frase')" />
            </div>

            {{-- la cerimonia: da dove parte, quando, dove si celebra, dove si tumula --}}
            <div class="border-l-2 border-caffe/15 bg-panna/40 px-5 py-5 space-y-6">
                <h3 class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">La cerimonia</h3>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="luogo_partenza" value="Luogo di partenza" />
                        <select id="luogo_partenza" name="luogo_partenza"
                                class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                       text-[15px] text-testo focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                            <option value="">— Seleziona —</option>
                            @foreach (\Modules\Memorial\Models\Defunto::LUOGHI_PARTENZA as $opzione)
                                <option value="{{ $opzione }}" @selected(old('luogo_partenza', $defunto?->luogo_partenza) === $opzione)>
                                    {{ $opzione }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('luogo_partenza')" />
                    </div>
                    <div>
                        <x-input-label for="cerimonia_at" value="Data e ora della cerimonia" />
                        <x-text-input id="cerimonia_at" name="cerimonia_at" type="datetime-local"
                            :value="old('cerimonia_at', $defunto?->cerimonia_at?->format('Y-m-d\TH:i'))" />
                        <x-input-error :messages="$errors->get('cerimonia_at')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="indirizzo_cerimonia" value="Indirizzo della cerimonia (partenza)" />
                    <x-text-input id="indirizzo_cerimonia" name="indirizzo_cerimonia" autocomplete="off"
                        placeholder="Via, numero civico, città"
                        :value="old('indirizzo_cerimonia', $defunto?->indirizzo_cerimonia)" />
                    <x-input-error :messages="$errors->get('indirizzo_cerimonia')" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="chiesa" value="Chiesa" />
                        <x-text-input id="chiesa" name="chiesa" autocomplete="off"
                            placeholder="Nome della chiesa"
                            :value="old('chiesa', $defunto?->chiesa)" />
                        <x-input-error :messages="$errors->get('chiesa')" />
                    </div>
                    <div>
                        <x-input-label for="indirizzo_chiesa" value="Indirizzo chiesa" />
                        <x-text-input id="indirizzo_chiesa" name="indirizzo_chiesa" autocomplete="off"
                            placeholder="Via, numero civico, città"
                            :value="old('indirizzo_chiesa', $defunto?->indirizzo_chiesa)" />
                        <x-input-error :messages="$errors->get('indirizzo_chiesa')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="cimitero" value="Cimitero" />
                    <x-text-input id="cimitero" name="cimitero" autocomplete="off"
                        placeholder="Nome e indirizzo del cimitero"
                        :value="old('cimitero', $defunto?->cimitero)" />
                    <x-input-error :messages="$errors->get('cimitero')" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-[1fr_6rem]">
                <div>
                    <x-input-label for="citta" value="Città della cerimonia" />
                    {{-- Si autocompila scegliendo un indirizzo qui sopra da Google
                         Places; resta comunque un campo modificabile a mano. --}}
                    <x-text-input id="citta" name="citta" autocomplete="off"
                        placeholder="Es. Boscoreale"
                        :value="old('citta', $defunto?->citta)" />
                    <x-input-error :messages="$errors->get('citta')" />
                </div>
                <div>
                    <x-input-label for="provincia" value="Prov." />
                    <x-text-input id="provincia" name="provincia" autocomplete="off" maxlength="2"
                        placeholder="NA" style="text-transform:uppercase"
                        :value="old('provincia', $defunto?->provincia)" />
                    <x-input-error :messages="$errors->get('provincia')" />
                </div>
            </div>

            <div>
                <x-input-label for="preghiera" value="Preghiera o dedica (sul retro)" />
                <textarea id="preghiera" name="preghiera" rows="4"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light
                                 text-[15px] leading-relaxed focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"
                >{{ old('preghiera', $defunto?->preghiera ?? "L'eterno riposo dona a lei, o Signore,\ne splenda a lei la luce perpetua.\nRiposi in pace. Amen.") }}</textarea>
                <x-input-error :messages="$errors->get('preghiera')" />
            </div>

            {{-- consenso: senza, non si lavora la fotografia --}}
            <div class="border-l-2 border-oro bg-panna/60 px-5 py-5 space-y-4">
                <h3 class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">Consenso</h3>

                <div>
                    <x-input-label for="gdpr_parentela" value="Che parentela hai con la persona" />
                    <x-text-input id="gdpr_parentela" name="gdpr_parentela" required
                        placeholder="figlia, nipote, coniuge…"
                        :value="old('gdpr_parentela', $defunto?->gdpr_parentela)" />
                    <x-input-error :messages="$errors->get('gdpr_parentela')" />
                </div>

                <label for="gdpr_consenso" class="flex items-start gap-3 cursor-pointer">
                    <input id="gdpr_consenso" name="gdpr_consenso" type="checkbox" value="1"
                           class="mt-1 h-4 w-4 accent-oro" @checked(old('gdpr_consenso', $defunto?->gdpr_consenso))>
                    <span class="font-sans font-light text-[13px] leading-relaxed text-testo-soft">
                        Autorizzo MemorAI a usare la fotografia e i dati qui indicati per realizzare
                        gli articoli di questo ordine. Sono un familiare e posso darne il consenso.
                    </span>
                </label>
                <x-input-error :messages="$errors->get('gdpr_consenso')" />
            </div>

            <x-primary-button>{{ $defunto ? 'Aggiorna i dati' : 'Salva e prosegui' }}</x-primary-button>
        </form>

        {{--
            Autocomplete Google Places sui campi di luogo: deroga isolata e
            esplicita alla convenzione self-hosted del progetto (font, script
            sempre locali), giustificata dal requisito esplicito del cliente.
            Circoscritta a questa sola vista; senza chiave configurata i campi
            restano input di testo normali e non parte nessuna richiesta esterna.
        --}}
        @if (config('services.google.maps_key'))
            <script>
                // `loading=async` carica le librerie di Google in background: a
                // DOMContentLoaded "places" potrebbe non essere pronto ancora.
                // Il callback ufficiale parte solo a libreria caricata davvero.
                window.inizializzaAutocompleteLuoghi = function () {
                    var opzioni = {
                        componentRestrictions: { country: 'it' },
                        fields: ['name', 'formatted_address', 'address_components'],
                    };

                    // Città e sigla provincia per il testo del manifesto (formato
                    // "Via Roma 1, Boscoreale, NA"): li estrae dal primo indirizzo
                    // scelto e li scrive nei due campi sotto, che restano comunque
                    // editabili a mano — l'ultima scelta vince, ma in pratica
                    // partenza/chiesa/cimitero sono quasi sempre nello stesso comune.
                    function riempiCittaProvincia(componenti) {
                        if (!componenti) return;
                        var elCitta = document.getElementById('citta');
                        var elProvincia = document.getElementById('provincia');
                        (componenti || []).forEach(function (c) {
                            if (elCitta && (c.types.indexOf('locality') !== -1 || c.types.indexOf('administrative_area_level_3') !== -1)) {
                                elCitta.value = c.long_name;
                            }
                            if (elProvincia && c.types.indexOf('administrative_area_level_2') !== -1) {
                                elProvincia.value = (c.short_name || '').toUpperCase();
                            }
                        });
                    }

                    // Solo via e civico: "formatted_address" arriva già con città,
                    // CAP e "Italia" in coda, che finirebbero ripetuti nel testo del
                    // manifesto visto che città/provincia ora sono campi a parte.
                    // Senza una via riconosciuta (raro ma capita) torna null e il
                    // chiamante lascia l'indirizzo completo così com'è.
                    function indirizzoBreve(componenti) {
                        var via = null, civico = null;
                        (componenti || []).forEach(function (c) {
                            if (c.types.indexOf('route') !== -1) via = c.long_name;
                            if (c.types.indexOf('street_number') !== -1) civico = c.long_name;
                        });
                        return via ? (civico ? via + ', ' + civico : via) : null;
                    }

                    // Indirizzo di partenza e indirizzo chiesa: qui conta la via breve,
                    // sono entrambi nel testo del manifesto ("Partenza da Via Roma 1,
                    // Boscoreale, NA" / "Parrocchia X, Via Chiesa 2, Boscoreale, NA") —
                    // vedi GeneratoreTestoFunerale.
                    ['indirizzo_cerimonia', 'indirizzo_chiesa'].forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) {
                            var autocomplete = new google.maps.places.Autocomplete(el, opzioni);
                            autocomplete.addListener('place_changed', function () {
                                var luogo = autocomplete.getPlace();
                                var breve = indirizzoBreve(luogo.address_components);
                                if (breve) el.value = breve;
                                riempiCittaProvincia(luogo.address_components);
                            });
                        }
                    });

                    // "Cimitero": molti luoghi su Google Places non hanno una via
                    // propria, solo la città — qui si preferisce il nome del posto
                    // (es. "Cimitero di Boscoreale"), con via e civico aggiunti se ci
                    // sono; solo se manca anche il nome si tiene l'indirizzo completo
                    // (ripulito da "Italia" comunque, sia qui che lato server).
                    var elCimitero = document.getElementById('cimitero');
                    if (elCimitero) {
                        var autocompleteCimitero = new google.maps.places.Autocomplete(elCimitero, opzioni);
                        autocompleteCimitero.addListener('place_changed', function () {
                            var luogo = autocompleteCimitero.getPlace();
                            var breve = indirizzoBreve(luogo.address_components);
                            if (luogo.name) {
                                elCimitero.value = breve ? luogo.name + ', ' + breve : luogo.name;
                            } else if (breve) {
                                elCimitero.value = breve;
                            }
                            riempiCittaProvincia(luogo.address_components);
                        });
                    }

                    // "Chiesa": si cerca il nome del luogo, non il suo indirizzo — che
                    // si scrive da solo nel campo accanto (via breve, non l'indirizzo
                    // completo — vedi indirizzoBreve), così non si ridigita lo stesso
                    // posto due volte.
                    var elChiesa = document.getElementById('chiesa');
                    var elIndirizzoChiesa2 = document.getElementById('indirizzo_chiesa');
                    if (elChiesa && elIndirizzoChiesa2) {
                        var autocompleteChiesa = new google.maps.places.Autocomplete(elChiesa, opzioni);
                        autocompleteChiesa.addListener('place_changed', function () {
                            var luogo = autocompleteChiesa.getPlace();
                            if (luogo.name) elChiesa.value = luogo.name;
                            var breve = indirizzoBreve(luogo.address_components);
                            if (breve) elIndirizzoChiesa2.value = breve;
                            riempiCittaProvincia(luogo.address_components);
                        });
                    }
                };
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=inizializzaAutocompleteLuoghi&loading=async" async defer></script>
        @endif
    </section>

    {{-- ============ 2. la foto ============ --}}
    {{-- Solo B2C: per le agenzie questa sezione duplica il riquadro "1. Foto"
         della Scheda del defunto, che per loro è il percorso canalizzato. --}}
    @if (! $ordine->agenzia_id)
    <section class="bg-bianco px-7 py-8 {{ $defunto ? '' : 'opacity-45 pointer-events-none' }}">
        <header class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-serif text-2xl font-medium">La fotografia</h2>
            @if ($foto->isNotEmpty())
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">
                    {{ $foto->count() }} {{ $foto->count() === 1 ? 'immagine' : 'immagini' }}
                </span>
            @endif
        </header>

        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Carica il ritratto e lascialo elaborare: sfondo pulito, luce uniforme, volto rispettato.
            Se non sei soddisfatto puoi rifarlo quante volte vuoi.
        </p>

        @if ($foto->isNotEmpty())
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($foto->take(6) as $immagine)
                    <figure class="w-24 border {{ $immagine->is_principale ? 'border-oro border-2' : 'border-caffe/25' }} bg-panna aspect-[4/5] overflow-hidden">
                        <img src="{{ $immagine->url() }}" alt="" class="h-full w-full object-cover">
                    </figure>
                @endforeach
            </div>
        @endif

        <div class="mt-6">
            <x-button :href="route('studio.foto')">
                {{ $foto->isEmpty() ? 'Apri il Foto Manager' : 'Riprendi la fotografia' }}
            </x-button>
        </div>
    </section>
    @endif

    {{-- ============ 3. il ricordino ============ --}}
    {{-- Solo B2C: per le agenzie questa scorciatoia scavalcherebbe la
         canalizzazione, che per loro passa dalla Scheda del defunto qui sotto. --}}
    @if ($ordine->designerAbilitato('ricordini') && ! $ordine->agenzia_id)
    <section class="bg-bianco px-7 py-8 {{ $foto->isNotEmpty() ? '' : 'opacity-45 pointer-events-none' }}">
        <header class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 class="font-serif text-2xl font-medium">Il ricordino</h2>
            @if ($ricordino)
                <span class="font-sans text-[10px] tracking-[0.2em] uppercase text-successo">Bozza salvata</span>
            @endif
        </header>

        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Componi fronte e retro: il ritratto, le date, la preghiera. Puoi partire da uno dei
            nostri modelli e cambiare quello che vuoi.
        </p>

        @if ($ricordino)
            <x-bozza-ricordino :ricordino="$ricordino" class="mt-5" />

            {{-- I giri di approvazione con la famiglia --}}
            @php $revisioni = $ricordino->revisioni; @endphp
            @if ($revisioni->isNotEmpty())
                <section class="mt-7 border-l-2 border-caffe/20 pl-5">
                    <h3 class="font-sans text-[11px] tracking-[0.22em] uppercase text-oro-scuro">
                        Con la famiglia
                    </h3>
                    <ol class="mt-3 space-y-3">
                        @foreach ($revisioni as $revisione)
                            <li class="font-sans font-light text-[13px] leading-relaxed">
                                <span class="text-testo-soft">
                                    {{ $revisione->inviata_at->format('d/m/Y H:i') }} ·
                                    inviata a {{ $revisione->inviata_a }} ·
                                </span>
                                <span @class([
                                    'text-successo' => $revisione->esito === \Modules\Memorial\Models\RevisioneRicordino::APPROVATA,
                                    'text-oro-scuro' => $revisione->esito === \Modules\Memorial\Models\RevisioneRicordino::MODIFICHE,
                                    'text-testo-soft' => $revisione->inAttesa(),
                                ])>{{ $revisione->esitoLeggibile() }}</span>

                                @if ($revisione->nota)
                                    <span class="mt-1 block text-testo">“{{ $revisione->nota }}”</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @endif

        <div class="mt-6">
            <x-button :href="route('studio.ricordino')">
                {{ $ricordino ? 'Riprendi la bozza' : 'Apri il Designer' }}
            </x-button>
        </div>
    </section>
    @endif

    {{-- ============ scheda defunto (solo agenzie) ============ --}}
    @if ($ordine->agenzia_id && $defunto)
        <section class="bg-bianco px-7 py-8">
            <h2 class="font-serif text-2xl font-medium">Scheda del defunto</h2>
            <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
                Il percorso canalizzato: foto, manifesto, necrologio e ricordino da un solo posto.
            </p>

            <div class="mt-6">
                <x-button :href="route('defunti.show', $defunto)">Vai alla scheda del defunto</x-button>
            </div>
        </section>
    @endif

    {{-- ============ 4. approvazione ============ --}}
    <section class="bg-panna/60 px-7 py-8">
        <h2 class="font-serif text-2xl font-medium">La tua approvazione</h2>
        <p class="mt-2 max-w-2xl font-sans font-light text-[14px] leading-relaxed text-testo-soft">
            Quando la bozza ti convince, approvala: da quel momento andiamo in stampa e non
            si può più correggere. Prenditi il tempo che ti serve.
        </p>

        @if ($ricordino)
            <form method="POST" action="{{ route('lavorazione.approva', $ordine) }}" class="mt-6">
                @csrf
                <x-primary-button>Approvo, mandate in stampa</x-primary-button>
            </form>
        @else
            <p class="mt-6 font-sans font-light text-[13px] text-testo-soft">
                Il pulsante compare quando c'è una bozza da approvare.
            </p>
        @endif
    </section>
</div>

<p class="mt-8">
    <a href="{{ route('ordine', $ordine) }}"
       class="font-sans text-[11px] tracking-[0.2em] uppercase text-testo-soft hover:text-oro-scuro transition-colors duration-300">
        ← Torna all'ordine
    </a>
</p>
@endsection
