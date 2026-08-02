@extends('layouts.gestione')

@section('title', 'Contenuti home — Gestione MemorAI')
@section('titolo', 'Contenuti home')

@section('gestione')
    @php
        // Piccolo helper locale: valore corrente (old() in caso di errori di
        // validazione, altrimenti quello salvato) per una chiave.
        $v = fn (string $chiave) => old("valori.{$chiave}", $valori[$chiave] ?? '');
    @endphp

    <p class="font-sans font-light text-[14px] text-testo-soft max-w-xl">
        I testi dell'hero, della fascia valori e della CTA della home pubblica,
        più la topbar e il testo del footer — nello stesso ordine in cui compaiono in pagina.
    </p>

    <form method="POST" action="{{ route('gestione.contenuti.update') }}" class="mt-8 max-w-3xl space-y-10">
        @csrf
        @method('PUT')

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">Topbar</legend>
            <div>
                <x-input-label for="topbar_testo" value="Testo (scorre via allo scroll)" />
                <x-text-input id="topbar_testo" name="valori[topbar.testo]" value="{{ $v('topbar.testo') }}" />
                <x-input-error :messages="$errors->get('valori.topbar.testo')" class="mt-2" />
            </div>
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">Hero</legend>
            <p class="font-sans font-light text-[12px] text-testo-soft">
                L'immagine si sceglie dalla scheda del prodotto ("Immagine hero in home"), qui solo i testi.
            </p>
            <div>
                <x-input-label for="hero_occhiello" value="Occhiello" />
                <x-text-input id="hero_occhiello" name="valori[hero.occhiello]" value="{{ $v('hero.occhiello') }}" />
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="hero_titolo_intro" value="Titolo" />
                    <x-text-input id="hero_titolo_intro" name="valori[hero.titolo_intro]" value="{{ $v('hero.titolo_intro') }}" />
                </div>
                <div>
                    <x-input-label for="hero_titolo_enfasi" value="...seguito dalla parola in evidenza (oro, corsivo)" />
                    <x-text-input id="hero_titolo_enfasi" name="valori[hero.titolo_enfasi]" value="{{ $v('hero.titolo_enfasi') }}" />
                </div>
            </div>
            <div>
                <x-input-label for="hero_sottotitolo" value="Sottotitolo" />
                <textarea id="hero_sottotitolo" name="valori[hero.sottotitolo]" rows="2"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ $v('hero.sottotitolo') }}</textarea>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="hero_bottone_primario" value="Bottone primario — etichetta" />
                    <x-text-input id="hero_bottone_primario" name="valori[hero.bottone_primario]" value="{{ $v('hero.bottone_primario') }}" />
                </div>
                <div>
                    <x-input-label for="hero_bottone_primario_url" value="...indirizzo" />
                    <x-text-input id="hero_bottone_primario_url" name="valori[hero.bottone_primario_url]" value="{{ $v('hero.bottone_primario_url') }}" />
                </div>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="hero_bottone_secondario" value="Bottone secondario — etichetta" />
                    <x-text-input id="hero_bottone_secondario" name="valori[hero.bottone_secondario]" value="{{ $v('hero.bottone_secondario') }}" />
                </div>
                <div>
                    <x-input-label for="hero_bottone_secondario_url" value="...indirizzo" />
                    <x-text-input id="hero_bottone_secondario_url" name="valori[hero.bottone_secondario_url]" value="{{ $v('hero.bottone_secondario_url') }}" />
                </div>
            </div>
            <p class="font-sans font-light text-[12px] text-testo-soft">
                Il bottone "Scopri le collezioni" in testa alla pagina (sopra il menu) usa lo stesso indirizzo del bottone primario qui sopra.
            </p>
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">Collezioni</legend>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="collezioni_occhiello" value="Occhiello" />
                    <x-text-input id="collezioni_occhiello" name="valori[collezioni.occhiello]" value="{{ $v('collezioni.occhiello') }}" />
                </div>
                <div>
                    <x-input-label for="collezioni_titolo" value="Titolo" />
                    <x-text-input id="collezioni_titolo" name="valori[collezioni.titolo]" value="{{ $v('collezioni.titolo') }}" />
                </div>
            </div>
            <div>
                <x-input-label for="collezioni_testo" value="Testo" />
                <x-text-input id="collezioni_testo" name="valori[collezioni.testo]" value="{{ $v('collezioni.testo') }}" />
            </div>
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">In evidenza</legend>
            <p class="font-sans font-light text-[12px] text-testo-soft">
                I prodotti mostrati si scelgono dalla scheda di ciascun prodotto ("In evidenza in home"), qui solo i testi della sezione.
            </p>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="evidenza_occhiello" value="Occhiello" />
                    <x-text-input id="evidenza_occhiello" name="valori[evidenza.occhiello]" value="{{ $v('evidenza.occhiello') }}" />
                </div>
                <div>
                    <x-input-label for="evidenza_titolo" value="Titolo" />
                    <x-text-input id="evidenza_titolo" name="valori[evidenza.titolo]" value="{{ $v('evidenza.titolo') }}" />
                </div>
            </div>
            <div>
                <x-input-label for="evidenza_bottone_url" value='Indirizzo del bottone "Vedi tutto il catalogo"' />
                <x-text-input id="evidenza_bottone_url" name="valori[evidenza.bottone_url]" value="{{ $v('evidenza.bottone_url') }}" />
            </div>
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-6">
            <legend class="font-serif text-xl font-medium px-1">Fascia valori (i tre blocchi)</legend>
            @for ($i = 1; $i <= 3; $i++)
                <div class="grid gap-5 sm:grid-cols-2 {{ $i < 3 ? 'pb-6 border-b border-caffe/10' : '' }}">
                    <div>
                        <x-input-label for="valori_{{ $i }}_titolo" value="Blocco {{ $i }} — titolo" />
                        <x-text-input id="valori_{{ $i }}_titolo" name="valori[valori.{{ $i }}.titolo]" value="{{ $v('valori.'.$i.'.titolo') }}" />
                    </div>
                    <div>
                        <x-input-label for="valori_{{ $i }}_testo" value="...testo" />
                        <x-text-input id="valori_{{ $i }}_testo" name="valori[valori.{{ $i }}.testo]" value="{{ $v('valori.'.$i.'.testo') }}" />
                    </div>
                </div>
            @endfor
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">CTA finale</legend>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="cta_titolo_intro" value="Titolo" />
                    <x-text-input id="cta_titolo_intro" name="valori[cta.titolo_intro]" value="{{ $v('cta.titolo_intro') }}" />
                </div>
                <div>
                    <x-input-label for="cta_titolo_enfasi" value="...seguito dalla parola in evidenza" />
                    <x-text-input id="cta_titolo_enfasi" name="valori[cta.titolo_enfasi]" value="{{ $v('cta.titolo_enfasi') }}" />
                </div>
            </div>
            <div>
                <x-input-label for="cta_testo" value="Testo" />
                <textarea id="cta_testo" name="valori[cta.testo]" rows="2"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ $v('cta.testo') }}</textarea>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="cta_bottone_primario" value="Bottone primario — etichetta" />
                    <x-text-input id="cta_bottone_primario" name="valori[cta.bottone_primario]" value="{{ $v('cta.bottone_primario') }}" />
                </div>
                <div>
                    <x-input-label for="cta_bottone_primario_url" value="...indirizzo" />
                    <x-text-input id="cta_bottone_primario_url" name="valori[cta.bottone_primario_url]" value="{{ $v('cta.bottone_primario_url') }}" />
                </div>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="cta_bottone_secondario" value="Bottone secondario — etichetta" />
                    <x-text-input id="cta_bottone_secondario" name="valori[cta.bottone_secondario]" value="{{ $v('cta.bottone_secondario') }}" />
                </div>
                <div>
                    <x-input-label for="cta_bottone_secondario_url" value="...indirizzo" />
                    <x-text-input id="cta_bottone_secondario_url" name="valori[cta.bottone_secondario_url]" value="{{ $v('cta.bottone_secondario_url') }}" />
                </div>
            </div>
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">Footer</legend>
            <div>
                <x-input-label for="footer_testo_brand" value="Testo sotto il logo" />
                <textarea id="footer_testo_brand" name="valori[footer.testo_brand]" rows="2"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ $v('footer.testo_brand') }}</textarea>
            </div>
        </fieldset>

        <fieldset class="border border-caffe/15 bg-bianco px-7 py-7 space-y-5">
            <legend class="font-serif text-xl font-medium px-1">Photoceramiche — avviso app</legend>
            <p class="font-sans font-light text-[12px] text-testo-soft">
                Mostrato nella categoria Photoceramiche e nella scheda dei suoi prodotti.
                Lascia "Testo" vuoto per non mostrare nulla.
            </p>
            <div>
                <x-input-label for="photoceramiche_avviso_titolo" value="Titolo" />
                <x-text-input id="photoceramiche_avviso_titolo" name="valori[photoceramiche.avviso_titolo]" value="{{ $v('photoceramiche.avviso_titolo') }}" />
            </div>
            <div>
                <x-input-label for="photoceramiche_avviso_testo" value="Testo" />
                <textarea id="photoceramiche_avviso_testo" name="valori[photoceramiche.avviso_testo]" rows="2"
                          class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[15px]
                                 focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">{{ $v('photoceramiche.avviso_testo') }}</textarea>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="photoceramiche_avviso_bottone" value="Bottone — etichetta" />
                    <x-text-input id="photoceramiche_avviso_bottone" name="valori[photoceramiche.avviso_bottone]" value="{{ $v('photoceramiche.avviso_bottone') }}" />
                </div>
                <div>
                    <x-input-label for="photoceramiche_avviso_url" value="...indirizzo dell'app" />
                    <x-text-input id="photoceramiche_avviso_url" name="valori[photoceramiche.avviso_url]" value="{{ $v('photoceramiche.avviso_url') }}" />
                </div>
            </div>
        </fieldset>

        <x-primary-button>Salva i contenuti</x-primary-button>
    </form>
@endsection
