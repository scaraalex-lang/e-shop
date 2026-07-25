<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="google" content="notranslate">
<meta name="robots" content="noindex, nofollow">
<title>Il tuo ricordino | MemorAI</title>

{{-- Font del canvas (stessi degli editor) + Jost per l'interfaccia.
     Tutto ospitato in casa: nessuna chiamata esterna dal browser. --}}
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
<script src="/vendor/libs/fabric.min.js"></script>
<style>
@font-face{font-family:'Jost';font-style:normal;font-weight:300;font-display:swap;src:url('/fonts/jost-v20-latin-300.woff2') format('woff2')}
@font-face{font-family:'Jost';font-style:normal;font-weight:400;font-display:swap;src:url('/fonts/jost-v20-latin-regular.woff2') format('woff2')}
@font-face{font-family:'Jost';font-style:normal;font-weight:500;font-display:swap;src:url('/fonts/jost-v20-latin-500.woff2') format('woff2')}

/* Palette della vetrina: questa pagina la vede il cliente, non lo studio. */
:root{
  --bianco:#fdfcfa; --panna:#faf6ec; --caffe:#3a2e22; --oro:#c2a35a;
  --oro-scuro:#a5863f; --testo:#3a2e22; --soft:#6b6152; --bordo:rgba(58,46,34,.15);
}
*{box-sizing:border-box;margin:0;padding:0}
/* le regole display: sotto vincerebbero sull'attributo hidden del browser */
[hidden]{display:none !important}
html,body{height:100%}
body{
  background:var(--bianco);color:var(--testo);
  font-family:'Jost',system-ui,sans-serif;font-weight:300;
  display:flex;flex-direction:column;min-height:100dvh;
  -webkit-font-smoothing:antialiased;
}
h1,h2,h3{font-family:'Cormorant Garamond',Georgia,serif;font-weight:500;color:var(--caffe)}

/* ---------- intestazione ---------- */
header{
  background:var(--caffe);color:var(--bianco);flex-shrink:0;
  padding:.85rem 1.15rem calc(.85rem + env(safe-area-inset-top)) 1.15rem;
}
.marchio{font-family:'Cormorant Garamond',serif;color:var(--oro);font-size:1.15rem;letter-spacing:.28em;padding-left:.28em}
.pratica{display:block;margin-top:.2rem;font-size:.72rem;letter-spacing:.16em;text-transform:uppercase;color:rgba(253,252,250,.6)}

/* ---------- passi ---------- */
.passi{display:flex;border-bottom:2px solid var(--caffe);flex-shrink:0;background:var(--bianco)}
.passo{
  flex:1;padding:.7rem .3rem;text-align:center;font-size:.66rem;letter-spacing:.16em;
  text-transform:uppercase;color:var(--soft);border-bottom:3px solid transparent;
}
.passo[data-attivo]{color:var(--oro-scuro);border-bottom-color:var(--oro)}
.passo{cursor:pointer;-webkit-tap-highlight-color:rgba(194,163,90,.2)}
.passo b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500}

main{flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:1.15rem 1.15rem 1.5rem}
section[hidden]{display:none}
.titolo{font-size:1.6rem;line-height:1.15}
.testo{margin-top:.5rem;color:var(--soft);font-size:.92rem;line-height:1.55}
.corsivo-oro{font-style:italic;color:var(--oro)}

/* ---------- pulsanti ---------- */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:.5rem;width:100%;
  padding:.95rem 1rem;font-family:'Jost',sans-serif;font-size:.75rem;letter-spacing:.2em;
  text-transform:uppercase;border:2px solid var(--caffe);background:transparent;color:var(--caffe);
  cursor:pointer;text-decoration:none;transition:background .25s,color .25s;
}
.btn-oro{background:var(--oro);border-color:var(--oro);color:var(--bianco)}
.btn-oro:disabled{opacity:.45}
.btn-piatto{border:none;padding:.7rem;color:var(--soft);letter-spacing:.14em}
.azioni{display:flex;gap:.7rem;margin-top:1.4rem}
.azioni .btn{flex:1}

/* ---------- passo foto ---------- */
.riquadro-foto{
  margin:1.2rem auto 0;border:2px solid var(--caffe);background:var(--panna);
  max-width:320px;position:relative;overflow:hidden;touch-action:none;
}
.riquadro-foto canvas{display:block}
.vuoto{
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6rem;
  padding:2.6rem 1rem;color:var(--soft);text-align:center;font-size:.85rem;
}
.zoom{display:flex;align-items:center;gap:.8rem;margin-top:1rem;max-width:320px;margin-left:auto;margin-right:auto}
.zoom input[type=range]{flex:1;accent-color:var(--oro-scuro)}
.zoom span{font-size:.7rem;letter-spacing:.14em;text-transform:uppercase;color:var(--soft)}
input[type=file]{display:none}

.nota{
  margin-top:1.6rem;border:2px solid var(--caffe);background:var(--panna);padding:1rem 1.1rem;
}
.nota h3{font-size:1.15rem}
.nota p{margin-top:.4rem;font-size:.85rem;line-height:1.55;color:var(--soft)}
.nota a{
  display:inline-block;margin-top:.7rem;color:var(--oro-scuro);font-size:.72rem;
  letter-spacing:.16em;text-transform:uppercase;text-decoration:none;border-bottom:1px solid var(--oro)
}

/* ---------- passo testi ---------- */
.scheda{border:2px solid var(--caffe);margin-top:1.2rem}
.riga{display:flex;justify-content:space-between;gap:1rem;padding:.75rem 1rem;border-bottom:1px solid var(--bordo)}
.riga:last-child{border-bottom:none}
.riga dt{font-size:.66rem;letter-spacing:.2em;text-transform:uppercase;color:var(--soft);padding-top:.2rem}
.riga dd{font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:var(--caffe);text-align:right}
.campo{margin-top:1.4rem}
.campo label{display:block;font-size:.66rem;letter-spacing:.2em;text-transform:uppercase;color:var(--oro-scuro);margin-bottom:.45rem}
.campo textarea{
  width:100%;border:2px solid var(--caffe);background:var(--bianco);padding:.8rem;
  font-family:'Jost',sans-serif;font-size:.95rem;font-weight:300;color:var(--testo);resize:vertical;
}
.campo textarea:focus{outline:none;border-color:var(--oro-scuro)}
.preghiera-scelta{
  border:2px solid var(--caffe);background:var(--panna);padding:.9rem 1rem;
  font-family:'Cormorant Garamond',serif;font-size:1.05rem;line-height:1.5;color:var(--caffe);
  white-space:pre-line;
}

/* ---------- passo anteprima ---------- */
.facciate{
  display:flex;gap:1rem;overflow-x:auto;scroll-snap-type:x mandatory;
  padding:1.2rem .2rem;margin:0 -1.15rem;padding-left:1.15rem;padding-right:1.15rem;
  -webkit-overflow-scrolling:touch;
}
.facciata{scroll-snap-align:center;flex:0 0 100%;max-width:100%}
.facciata figure{border:2px solid var(--caffe);background:#fff;overflow:hidden;min-height:6rem}
.facciata img{display:block;width:100%;height:auto}
.facciata figcaption{
  margin-top:.5rem;text-align:center;font-size:.66rem;letter-spacing:.22em;
  text-transform:uppercase;color:var(--soft)
}
.punti{display:flex;align-items:center;justify-content:center;gap:.55rem;margin-top:.2rem;flex-wrap:wrap}
.suggerimento{width:100%;text-align:center;margin-top:.5rem;font-size:.66rem;
  letter-spacing:.18em;text-transform:uppercase;color:var(--soft)}
.diagnosi{
  margin-top:1.2rem;border:2px solid var(--caffe);background:var(--panna);padding:.8rem;
  font-family:ui-monospace,monospace;font-size:.7rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;
}
.punto{width:8px;height:8px;border-radius:50%;border:2px solid var(--caffe);background:transparent}
.punto[data-attivo]{background:var(--caffe)}

/* ---------- avvisi ---------- */
.avviso{
  border:2px solid var(--oro-scuro);background:var(--panna);padding:.9rem 1rem;margin-top:1.2rem;
  font-size:.85rem;line-height:1.5;color:var(--testo)
}
.avviso strong{font-weight:500}
.avviso input{
  width:100%;margin-top:.6rem;border:2px solid var(--caffe);background:var(--bianco);
  padding:.7rem;font-family:'Jost',sans-serif;font-size:.9rem;font-weight:300
}
.avviso input:focus{outline:none;border-color:var(--oro-scuro)}

/* ---------- modale preghiere ---------- */
.modale{position:fixed;inset:0;z-index:80;display:none}
.modale[data-aperto]{display:block}
.velo{position:absolute;inset:0;background:rgba(58,46,34,.55)}
.foglio{
  position:absolute;left:0;right:0;bottom:0;max-height:88dvh;background:var(--bianco);
  border-top:2px solid var(--caffe);display:flex;flex-direction:column;
}
.foglio header{background:var(--bianco);color:var(--testo);border-bottom:2px solid var(--caffe);
  display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.15rem}
.foglio h2{font-size:1.35rem}
.chiudi{background:none;border:none;font-size:1.6rem;line-height:1;color:var(--caffe);cursor:pointer;padding:0 .2rem}
.elenco{overflow-y:auto;padding:.6rem 1.15rem 1.6rem}
.gruppo{margin-top:1.1rem;font-size:.66rem;letter-spacing:.22em;text-transform:uppercase;color:var(--oro-scuro)}
.voce{
  width:100%;text-align:left;border:2px solid var(--caffe);background:var(--bianco);
  padding:.85rem 1rem;margin-top:.6rem;cursor:pointer;font-family:'Jost',sans-serif;
}
.voce b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:500;color:var(--caffe)}
.voce span{display:block;margin-top:.25rem;font-size:.82rem;color:var(--soft);line-height:1.45}

/* ---------- esito ---------- */
.esito{text-align:center;padding:2.5rem 0}
.esito .segno{font-family:'Cormorant Garamond',serif;font-size:3rem;color:var(--oro)}

.caricamento{text-align:center;color:var(--soft);font-size:.85rem;padding:2rem 0}
</style>
</head>
<body>

<header>
    <span class="marchio">MemorAI</span>
    <span class="pratica">Ricordino di {{ $defunto->nomeCompleto() }} · {{ $formato }} cm</span>
</header>

<nav class="passi" aria-label="Avanzamento">
    <div class="passo" data-indicatore="foto" data-attivo><b>1</b>Foto</div>
    <div class="passo" data-indicatore="testi"><b>2</b>Testi</div>
    <div class="passo" data-indicatore="anteprima"><b>3</b>Conferma</div>
</nav>

<main>

    {{-- ============ PASSO 1 · FOTO ============ --}}
    <section data-passo="foto">
        <h1 class="titolo">La <span class="corsivo-oro">fotografia</span></h1>
        <p class="testo">
            Scegli lo scatto e spostalo dentro la cornice finché sta come vuoi tu.
            Se preferisci un ricordino di solo testo, puoi proseguire senza foto.
        </p>

        <div class="riquadro-foto" id="cornice-foto">
            <div class="vuoto" id="foto-vuota">
                <span style="font-family:'Cormorant Garamond',serif;font-size:2rem;color:var(--oro)">✛</span>
                Nessuna foto scelta
            </div>
            <canvas id="canvas-crop" hidden></canvas>
        </div>

        <div class="zoom" id="zona-zoom" hidden>
            <span>Zoom</span>
            <input type="range" id="zoom-foto" min="100" max="320" value="100" aria-label="Ingrandimento della foto">
        </div>

        <label for="file-foto" class="btn btn-oro" style="margin-top:1.2rem" id="etichetta-file">Scegli una foto</label>
        <input type="file" id="file-foto" accept="image/*">

        <div class="nota">
            <h3>Serve un ritocco più deciso?</h3>
            <p>
                Scontorno, restauro di una foto rovinata, sfondo da rifare: quel lavoro si fa
                nella web app Kerachrom, disponibile anche su iOS e Android. Elabora lì
                l'immagine, scaricala sul telefono e torna qui a importarla.
            </p>
            <a href="{{ $appKerachrom }}" target="_blank" rel="noopener noreferrer">Apri Kerachrom ↗</a>
        </div>

        <div class="azioni">
            <button type="button" class="btn" data-vai="testi">Continua</button>
        </div>
    </section>

    {{-- ============ PASSO 2 · TESTI ============ --}}
    <section data-passo="testi" hidden>
        <h1 class="titolo">Le <span class="corsivo-oro">parole</span></h1>
        <p class="testo">
            Nome, date ed età arrivano dalla scheda che hai compilato: non devi riscriverli.
            Restano da sistemare la frase e la preghiera.
        </p>

        <dl class="scheda">
            <div class="riga">
                <dt>Persona</dt>
                <dd>{{ $defunto->nomeCompleto() }}</dd>
            </div>
            <div class="riga">
                <dt>Date</dt>
                <dd>{{ $praticaData['data_nascita'] ?? '—' }} — {{ $praticaData['data_morte'] ?? '—' }}</dd>
            </div>
            <div class="riga">
                <dt>Età</dt>
                <dd>{{ $praticaData['anni'] ? 'di anni ' . $praticaData['anni'] : '—' }}</dd>
            </div>
        </dl>

        <div class="campo">
            <label for="campo-frase">Frase di ricordo</label>
            <textarea id="campo-frase" rows="2">{{ $praticaData['frase'] }}</textarea>
        </div>

        <div class="campo">
            <label>Preghiera</label>
            <div class="preghiera-scelta" id="preghiera-testo">{{ $praticaData['prayer'] }}</div>
            <button type="button" class="btn" style="margin-top:.7rem" id="apri-preghiere">
                Scegli dall'archivio
            </button>
        </div>

        <div class="azioni">
            <button type="button" class="btn" data-vai="foto">Indietro</button>
            <button type="button" class="btn btn-oro" data-vai="anteprima">Vedi il ricordino</button>
        </div>
    </section>

    {{-- ============ PASSO 3 · ANTEPRIMA E CONFERMA ============ --}}
    <section data-passo="anteprima" hidden>
        <h1 class="titolo">Le due <span class="corsivo-oro">facciate</span></h1>
        <p class="testo">Scorri per vedere fronte e retro. Se qualcosa non va, torna indietro e correggi.</p>

        <div class="caricamento" id="composizione">Composizione in corso…</div>

        {{-- Le facciate si compongono FUORI schermo e si mostrano come
             immagini: una tela viva appesa al DOM, su iOS, può uscire bianca
             sotto pressione di memoria. Un'immagine no. --}}
        <div class="facciate" id="facciate" hidden>
            <div class="facciata">
                <figure><img id="img-fronte" alt="Fronte del ricordino"></figure>
                <figcaption>Fronte</figcaption>
            </div>
            <div class="facciata">
                <figure><img id="img-retro" alt="Retro del ricordino"></figure>
                <figcaption>Retro</figcaption>
            </div>
        </div>
        <div class="punti" id="punti" hidden>
            <span class="punto" data-attivo></span><span class="punto"></span>
            <span class="suggerimento" id="suggerimento">scorri per il retro →</span>
        </div>

        <pre class="diagnosi" id="diagnosi" hidden></pre>

        @if (! $gdpr['consenso'])
            <div class="avviso" id="blocco-gdpr">
                <strong>Manca l'autorizzazione.</strong>
                Per stampare l'immagine e i dati di {{ $gdpr['defunto'] }} serve il consenso di un
                familiare. Indica chi autorizza: lo registriamo insieme al ricordino.
                <input type="text" id="gdpr-nome" placeholder="Nome di chi autorizza" autocomplete="name">
                <input type="text" id="gdpr-parentela" placeholder="Parentela (figlia, coniuge…)">
            </div>
        @endif

        <div class="azioni">
            <button type="button" class="btn" data-vai="testi">Indietro</button>
            <button type="button" class="btn btn-oro" id="conferma">Conferma</button>
        </div>
    </section>

    {{-- ============ ESITO ============ --}}
    <section data-passo="fatto" hidden>
        <div class="esito">
            <div class="segno">✦</div>
            <h1 class="titolo" style="margin-top:.6rem">Ricordino <span class="corsivo-oro">confermato</span></h1>
            <p class="testo" id="esito-testo">
                L'abbiamo messo in lavorazione. Ti avvisiamo appena è pronto per la stampa.
            </p>
            <a href="{{ url('/') }}" class="btn" style="margin-top:1.6rem">Torna al sito</a>
        </div>
    </section>
</main>

{{-- ============ MODALE ARCHIVIO PREGHIERE ============ --}}
<div class="modale" id="modale-preghiere" role="dialog" aria-modal="true" aria-label="Archivio preghiere">
    <div class="velo" data-chiudi-modale></div>
    <div class="foglio">
        <header>
            <h2>Archivio preghiere</h2>
            <button type="button" class="chiudi" data-chiudi-modale aria-label="Chiudi">×</button>
        </header>
        <div class="elenco">
            @forelse ($preghiere as $categoria => $gruppo)
                <div class="gruppo">{{ $categoria }}</div>
                @foreach ($gruppo as $p)
                    <button type="button" class="voce" data-testo="{{ $p->testo }}">
                        <b>{{ $p->titolo }}</b>
                        <span>{{ $p->estratto() }}</span>
                    </button>
                @endforeach
            @empty
                <p class="testo" style="margin-top:1.2rem">
                    L'archivio è vuoto: si popola da <em>Gestione → Preghiere</em>.
                </p>
            @endforelse
        </div>
    </div>
</div>

<script>
// ─────────────────────────────────────────────────────────────────────────
//  Designer Smart — percorso da telefono.
//  Non è l'editor completo con i pannelli tolti: qui il layout è deciso
//  (template scelto in dashboard), i dati arrivano dalla pratica e alla
//  persona restano tre gesti. Il canvas serve solo a comporre e mostrare.
// ─────────────────────────────────────────────────────────────────────────
const PRATICA_ID   = {{ $praticaId }};
const FORMATO      = @json($formato);
const CANVAS_W     = {{ $canvasW }};
const CANVAS_H     = {{ $canvasH }};
const TEMPLATE     = { fronte: @json($template->fronte), retro: @json($template->retro) };
const STUDIO_TOKEN = '{{ config('photoprint.studio_token') }}';
const GDPR_OK      = @json((bool) $gdpr['consenso']);

// Dati della pratica: nome, date, età arrivano di qui e non si riscrivono.
const praticaData = @json($praticaData);

// ── Blocchi personali: stessa convenzione del designer completo.
// Se cambia testoPersonale() là, va cambiata anche qui (i due blade sono
// indipendenti di proposito, vedi skill studio-editor).
const BLOCCHI_PERSONALI = ['nome', 'eta', 'date', 'frase', 'preghiera'];

function testoPersonale(tipo, d) {
  d = d || {};
  switch (tipo) {
    case 'nome':      return (d.cognome || 'COGNOME') + '\n' + (d.nome || 'Nome');
    case 'eta':       return d.anni ? 'di anni ' + d.anni : 'di anni ___';
    case 'date':      return (d.data_nascita || '__/__/____') + ' — ' + (d.data_morte || '__/__/____');
    case 'frase':     return d.frase || "È mancato all'affetto dei suoi cari";
    case 'preghiera': return (d.prayer && d.prayer.trim()) ? d.prayer
                        : "Signore, accogli nella tua pace\nl'anima del tuo servo.\nAmen.";
  }
  return '';
}

// ── Sede della foto dichiarata dal template (rettangolo invisibile).
const SLOT = (TEMPLATE.fronte.objects || []).find(o => o.customType === 'photo-slot') || null;

// ─────────────────────────── navigazione a passi ─────────────────────────
const sezioni = document.querySelectorAll('[data-passo]');
const indicatori = document.querySelectorAll('[data-indicatore]');

function vaiA(passo) {
  sezioni.forEach(s => { s.hidden = s.dataset.passo !== passo; });
  indicatori.forEach(i => i.toggleAttribute('data-attivo', i.dataset.indicatore === passo));
  document.querySelector('main').scrollTop = 0;
  if (passo === 'anteprima') componi();
}

document.querySelectorAll('[data-vai]').forEach(b =>
  b.addEventListener('click', () => vaiA(b.dataset.vai)));

// Anche i tre numeri in alto portano al loro passo: da telefono il pulsante
// "Vedi il ricordino" sta in fondo alla pagina e si può non trovarlo.
indicatori.forEach(i => i.addEventListener('click', () => vaiA(i.dataset.indicatore)));

// ───────────────────────────── passo 1: foto ─────────────────────────────
// Il ritaglio è la cornice stessa: si trascina e si ingrandisce, quello che
// esce dai bordi è tagliato. Nessun rettangolo di selezione da capire.
let cropCanvas = null, cropImg = null, scalaBase = 1, fotoRitagliata = null;

const cornice   = document.getElementById('cornice-foto');
const elVuota   = document.getElementById('foto-vuota');
const elCanvasC = document.getElementById('canvas-crop');
const zonaZoom  = document.getElementById('zona-zoom');
const cursore   = document.getElementById('zoom-foto');

function misureCornice() {
  const rapporto = SLOT ? (SLOT.height / SLOT.width) : 4 / 3;
  const larghezza = Math.min(320, cornice.clientWidth || 320);
  return { w: larghezza, h: Math.round(larghezza * rapporto) };
}

document.getElementById('file-foto').addEventListener('change', function (e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;

  const lettore = new FileReader();
  lettore.onload = ev => riduci(ev.target.result, 1600).then(caricaNellaCornice);
  lettore.readAsDataURL(file);
  e.target.value = '';
});

/**
 * Riduce lo scatto prima di lavorarlo.
 *
 * Una foto da telefono è da 12 megapixel: tenerla a piena risoluzione su una
 * tela costa ~50 MB e Safari, sotto pressione di memoria, svuota le tele senza
 * dire niente. Al ritaglio bastano 1600px di lato lungo. Di passaggio,
 * riscrive in JPEG anche gli HEIC dell'iPhone, così a valle è tutto uguale.
 */
function riduci(dataUrl, latoMax) {
  return new Promise(risolvi => {
    const im = new Image();

    im.onload = () => {
      const fattore = Math.min(1, latoMax / Math.max(im.width, im.height));
      if (fattore >= 1) { risolvi(dataUrl); return; }

      try {
        const c = document.createElement('canvas');
        c.width = Math.round(im.width * fattore);
        c.height = Math.round(im.height * fattore);
        c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
        risolvi(c.toDataURL('image/jpeg', .92));
      } catch (errore) {
        console.warn('Riduzione non riuscita, si usa l\'originale:', errore);
        risolvi(dataUrl);
      }
    };

    im.onerror = () => risolvi(dataUrl);
    im.src = dataUrl;
  });
}

function caricaNellaCornice(dataUrl) {
  const { w, h } = misureCornice();

  if (!cropCanvas) {
    elCanvasC.hidden = false;
    cropCanvas = new fabric.Canvas('canvas-crop', {
      width: w, height: h, selection: false, backgroundColor: '#faf6ec',
    });
  } else {
    cropCanvas.clear();
    cropCanvas.setWidth(w); cropCanvas.setHeight(h);
  }

  fabric.Image.fromURL(dataUrl, img => {
    if (!img || !img.width) {
      elVuota.hidden = false;
      elVuota.textContent = "Questa immagine non si apre. Provane un'altra.";
      return;
    }

    // "cover": la foto riempie sempre la cornice, non si vedono bordi vuoti
    scalaBase = Math.max(w / img.width, h / img.height);
    img.set({
      originX: 'left', originY: 'top',
      scaleX: scalaBase, scaleY: scalaBase,
      hasControls: false, hasBorders: false, lockRotation: true,
      left: (w - img.width * scalaBase) / 2,
      top: (h - img.height * scalaBase) / 2,
    });
    cropImg = img;
    cropCanvas.add(img);
    cropCanvas.setActiveObject(img);
    img.on('moving', trattieni);
    trattieni();
    cropCanvas.renderAll();

    elVuota.hidden = true;
    zonaZoom.hidden = false;
    cursore.value = 100;
    document.getElementById('etichetta-file').textContent = 'Cambia foto';
  });
}

/** Impedisce che la foto scopra un angolo della cornice. */
function trattieni() {
  if (!cropImg || !cropCanvas) return;
  const w = cropCanvas.getWidth(), h = cropCanvas.getHeight();
  const iw = cropImg.getScaledWidth(), ih = cropImg.getScaledHeight();
  cropImg.set({
    left: Math.min(0, Math.max(cropImg.left, w - iw)),
    top:  Math.min(0, Math.max(cropImg.top,  h - ih)),
  });
  cropImg.setCoords();
}

cursore.addEventListener('input', () => {
  if (!cropImg) return;
  const centro = {
    x: cropImg.left + cropImg.getScaledWidth() / 2,
    y: cropImg.top + cropImg.getScaledHeight() / 2,
  };
  const s = scalaBase * (parseInt(cursore.value, 10) / 100);
  cropImg.set({ scaleX: s, scaleY: s });
  cropImg.set({
    left: centro.x - cropImg.getScaledWidth() / 2,
    top:  centro.y - cropImg.getScaledHeight() / 2,
  });
  trattieni();
  cropCanvas.renderAll();
});

/**
 * Esporta il ritaglio alla risoluzione della sede sul ricordino.
 * Se l'esportazione non riesce (succede sui browser che considerano "sporca"
 * la tela) si prosegue senza foto: meglio un ricordino di solo testo che un
 * percorso che si pianta.
 */
function ritaglio() {
  if (!cropCanvas || !cropImg) return null;

  try {
    const moltiplicatore = SLOT ? (SLOT.width / cropCanvas.getWidth()) : 3;
    const dati = cropCanvas.toDataURL({ format: 'jpeg', quality: .92, multiplier: moltiplicatore });

    return (typeof dati === 'string' && dati.length > 100) ? dati : null;
  } catch (e) {
    console.error('Ritaglio non esportabile:', e);
    return null;
  }
}

// ──────────────────────── passo 2: testi e preghiere ─────────────────────
const modale = document.getElementById('modale-preghiere');
const boxPreghiera = document.getElementById('preghiera-testo');

document.getElementById('apri-preghiere').addEventListener('click', () => modale.setAttribute('data-aperto', ''));
document.querySelectorAll('[data-chiudi-modale]').forEach(el =>
  el.addEventListener('click', () => modale.removeAttribute('data-aperto')));
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') modale.removeAttribute('data-aperto');
});

document.querySelectorAll('.voce').forEach(v => v.addEventListener('click', () => {
  praticaData.prayer = v.dataset.testo;
  boxPreghiera.textContent = v.dataset.testo;
  modale.removeAttribute('data-aperto');
}));

document.getElementById('campo-frase').addEventListener('input', e => {
  praticaData.frase = e.target.value;
});

// ─────────────────────── passo 3: composizione e conferma ────────────────
let cFronte = null, cRetro = null;

/** Non lasciare che una promessa che non arriva blocchi tutto il percorso. */
function conScadenza(promessa, ms, etichetta) {
  return Promise.race([
    promessa,
    new Promise((_, rifiuta) => setTimeout(() => rifiuta(new Error(etichetta + ': tempo scaduto')), ms)),
  ]);
}

/** I font devono essere caricati prima di disegnare, o il testo esce storto. */
async function fontiPronte() {
  try {
    await conScadenza(Promise.all([
      document.fonts.load("30px 'Cormorant Garamond'"),
      document.fonts.load("italic 30px 'Cormorant Garamond'"),
      document.fonts.load("bold 30px 'Cormorant Garamond'"),
    ]).then(() => document.fonts.ready), 4000, 'font');
  } catch (e) {
    // I font di sistema bastano: meglio un ricordino con la grazia sbagliata
    // che una schermata ferma su "composizione in corso".
    console.warn('Font non pronti, si prosegue:', e.message);
  }
}

function nuovoLato(statoJson) {
  // Elemento creato al volo e mai appeso alla pagina: serve solo a disegnare.
  const elemento = document.createElement('canvas');
  elemento.width = CANVAS_W;
  elemento.height = CANVAS_H;

  const canvas = new fabric.StaticCanvas(elemento, {
    width: CANVAS_W, height: CANVAS_H, backgroundColor: '#ffffff',
  });

  return conScadenza(new Promise(risolvi => {
    canvas.loadFromJSON(JSON.parse(JSON.stringify(statoJson)), () => {
      // I segnaposto del template diventano i dati di questa persona.
      canvas.getObjects().forEach(o => {
        // Un testo arrivato da JSON senza "styles" fa esplodere la successiva
        // toJSON() di Fabric: si normalizza qui, all'ingresso (stessa cura del
        // designer completo, vedi riempiConDefunto).
        if ((o.type === 'textbox' || o.type === 'text') && !o.styles) o.styles = {};

        if (BLOCCHI_PERSONALI.indexOf(o.customType) !== -1) {
          o.set('text', testoPersonale(o.customType, praticaData));
        }
      });
      // loadFromJSON sostituisce anche le proprietà della tela: senza sfondo
      // il JPEG (che non ha trasparenza) esce nero. Si rimette dopo il carico,
      // non prima.
      canvas.setBackgroundColor('#ffffff', () => {
        canvas.renderAll();
        risolvi(canvas);
      });
    });
  }), 15000, 'composizione della facciata');
}

/** Mette la foto ritagliata nella sede prevista dal template. */
function applicaFoto(canvas) {
  if (!SLOT || !fotoRitagliata) return Promise.resolve();

  return new Promise(risolvi => {
    fabric.Image.fromURL(fotoRitagliata, img => {
      if (!img || !img.width) {
        console.warn('Foto ritagliata non caricabile: ricordino di solo testo.');
        risolvi();
        return;
      }

      const s = SLOT.width / img.width;
      img.set({
        left: SLOT.left, top: SLOT.top, originX: 'left', originY: 'top',
        scaleX: s, scaleY: s, selectable: false, evented: false, customType: 'photo',
      });

      // maschera: la stessa forma che il designer completo chiama "ovale"
      const comuni = { left: SLOT.left, top: SLOT.top, originX: 'left', originY: 'top', absolutePositioned: true };
      img.clipPath = SLOT.maschera === 'ovale'
        ? new fabric.Ellipse({ rx: SLOT.width / 2, ry: SLOT.height / 2, ...comuni })
        : new fabric.Rect({ width: SLOT.width, height: SLOT.height, ...comuni });

      canvas.add(img);

      // filo dorato attorno alla foto
      const bordo = SLOT.maschera === 'ovale'
        ? new fabric.Ellipse({ rx: SLOT.width / 2, ry: SLOT.height / 2, ...comuni, absolutePositioned: false })
        : new fabric.Rect({ width: SLOT.width, height: SLOT.height, ...comuni, absolutePositioned: false });
      bordo.set({ fill: 'transparent', stroke: '#c2a35a', strokeWidth: 3, selectable: false, evented: false });
      canvas.add(bordo);

      canvas.renderAll();
      risolvi();
    });
  });
}

/** Esporta un lato come immagine per l'anteprima a schermo. */
function immagineDi(canvas) {
  // ~2x la larghezza della card: nitido sul retina, leggero in memoria.
  return canvas.toDataURL({ format: 'jpeg', quality: .85, multiplier: 800 / CANVAS_W });
}

/**
 * Referto tecnico a schermo, solo con ?diagnosi=1 nell'indirizzo: da un
 * telefono altrui la console non si legge, uno screenshot sì.
 */
const MOSTRA_DIAGNOSI = new URLSearchParams(location.search).get('diagnosi') === '1';

function diagnostica(dati) {
  if (!MOSTRA_DIAGNOSI) return;

  const box = document.getElementById('diagnosi');
  box.hidden = false;
  box.textContent = Object.entries(dati).map(([k, v]) => k + ': ' + v).join('\n')
    + '\nschermo: ' + window.innerWidth + 'x' + window.innerHeight
    + ' dpr' + (window.devicePixelRatio || 1);
}

let composizioneInCorso = false;

async function componi() {
  if (composizioneInCorso) return;
  composizioneInCorso = true;

  const avviso = document.getElementById('composizione');
  avviso.textContent = 'Composizione in corso…';
  avviso.hidden = false;
  document.getElementById('facciate').hidden = true;
  document.getElementById('punti').hidden = true;

  try {
    fotoRitagliata = ritaglio();

    await fontiPronte();

    if (cFronte) { cFronte.dispose(); cFronte = null; }
    if (cRetro)  { cRetro.dispose();  cRetro = null; }

    cFronte = await nuovoLato(TEMPLATE.fronte);
    await applicaFoto(cFronte);
    cRetro = await nuovoLato(TEMPLATE.retro);

    const fronteJpg = immagineDi(cFronte);
    const retroJpg  = immagineDi(cRetro);

    document.getElementById('img-fronte').src = fronteJpg;
    document.getElementById('img-retro').src  = retroJpg;

    document.getElementById('facciate').hidden = false;
    document.getElementById('punti').hidden = false;
    avviso.hidden = true;

    diagnostica({
      'fabric':   fabric.version,
      'tela':     CANVAS_W + 'x' + CANVAS_H,
      'oggetti':  cFronte.getObjects().length + ' fronte / ' + cRetro.getObjects().length + ' retro',
      'foto':     fotoRitagliata ? (Math.round(fotoRitagliata.length / 1024) + ' KB') : 'nessuna',
      'anteprime': Math.round(fronteJpg.length / 1024) + ' KB / ' + Math.round(retroJpg.length / 1024) + ' KB',
    });
  } catch (errore) {
    // Una composizione che non riesce deve dirlo, non restare a fissare il
    // vuoto: il messaggio serve anche a noi per capire cosa è successo.
    console.error('Composizione fallita:', errore);
    avviso.hidden = false;
    avviso.innerHTML = 'Non riesco a comporre il ricordino su questo telefono.<br>'
      + '<span style="font-size:.78rem;opacity:.75">' + (errore && errore.message ? errore.message : errore) + '</span>';
    document.getElementById('conferma').disabled = true;
    diagnostica({ 'errore': (errore && errore.message) ? errore.message : String(errore) });
  } finally {
    composizioneInCorso = false;
  }
}

// pallini che seguono lo scorrimento fra le due facciate
const nastro = document.getElementById('facciate');
nastro.addEventListener('scroll', () => {
  const indice = nastro.scrollLeft > nastro.clientWidth / 3 ? 1 : 0;
  document.querySelectorAll('#punti .punto').forEach((p, i) =>
    p.toggleAttribute('data-attivo', i === indice));
  document.getElementById('suggerimento').style.visibility = indice ? 'hidden' : 'visible';
});

// ─────────────────────────────── conferma ────────────────────────────────
async function chiamata(url, corpo) {
  const risposta = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Studio-Token': STUDIO_TOKEN },
    body: JSON.stringify(corpo),
  });
  if (!risposta.ok) throw new Error('Risposta ' + risposta.status);
  return risposta.json();
}

document.getElementById('conferma').addEventListener('click', async function () {
  const bottone = this;
  if (!cFronte || !cRetro) return;

  // Senza consenso non si stampa nulla: è il vincolo, non un passaggio in più.
  const blocco = document.getElementById('blocco-gdpr');
  if (blocco) {
    const chi = document.getElementById('gdpr-nome').value.trim();
    if (!chi) {
      document.getElementById('gdpr-nome').focus();
      return;
    }
  }

  bottone.disabled = true;
  bottone.textContent = 'Salvataggio…';

  try {
    if (blocco) {
      await chiamata('/admin/api/defunto/' + PRATICA_ID + '/gdpr', {
        autorizzato_da: document.getElementById('gdpr-nome').value.trim(),
        parentela: document.getElementById('gdpr-parentela').value.trim() || null,
        note: 'Consenso raccolto dal Designer Smart.',
      });
    }

    await chiamata('/admin/api/defunto/' + PRATICA_ID + '/ricordino', {
      format: FORMATO,
      canvas_fronte: JSON.stringify(cFronte.toJSON(['customType', 'maschera'])),
      canvas_retro: JSON.stringify(cRetro.toJSON(['customType', 'maschera'])),
      preview: cFronte.toDataURL({ format: 'jpeg', quality: .82, multiplier: .45 }),
      preview_retro: cRetro.toDataURL({ format: 'jpeg', quality: .82, multiplier: .45 }),
      stato: 'in_approvazione',
    });

    vaiA('fatto');
  } catch (errore) {
    console.error('Conferma ricordino non riuscita:', errore);
    bottone.disabled = false;
    bottone.textContent = 'Conferma';
    document.getElementById('blocco-gdpr')?.scrollIntoView({ behavior: 'smooth' });
    alert('Non siamo riusciti a salvare il ricordino. Riprova fra poco.');
  }
});

// Le anteprime sono immagini a larghezza piena: la rotazione le adatta da sé.
</script>
</body>
</html>
