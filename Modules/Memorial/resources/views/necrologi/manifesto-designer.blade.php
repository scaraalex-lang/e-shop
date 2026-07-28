<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="google" content="notranslate">
<title>Designer Manifesti | MemorAI</title>
{{-- Font self-hosted (GDPR): niente Google Fonts da CDN, le 16 famiglie usate
     qui sotto sono già dentro questo CSS. --}}
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
{{-- Fabric.js, jsPDF e il generatore QR self-hosted: niente cdnjs, niente
     servizio QR esterno (vedi addQRNecrologio più sotto). --}}
<script src="/vendor/libs/fabric.min.js"></script>
<script src="/vendor/libs/jspdf.umd.min.js"></script>
<script src="/vendor/libs/qrcode.js"></script>
<style>
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Regular.ttf') format('truetype'); font-weight:normal; font-style:normal; font-display:swap; }
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Regular-Italic.ttf') format('truetype'); font-weight:normal; font-style:italic; font-display:swap; }
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Bold.ttf') format('truetype'); font-weight:bold; font-style:normal; font-display:swap; }
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Bold-Italic.ttf') format('truetype'); font-weight:bold; font-style:italic; font-display:swap; }
:root{
  --ink:#1a1a2e;--gold:#c8a96e;--cream:#f5f0e8;--cream-dark:#ede6d8;
  --white:#fdfaf5;--gray:#8a7f72;--border:#ddd8d0;--green:#3a7a5a;--red:#c44b3a;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);height:100vh;overflow:hidden;display:flex;flex-direction:column}
nav{background:var(--ink);padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;flex-shrink:0;z-index:100}
.logo{color:#fff;font-size:1rem;font-weight:600;font-family:'Cormorant Garamond',serif}
.nav-links{display:flex;align-items:center;gap:.5rem}
.nav-btn{padding:.4rem .9rem;border-radius:6px;font-size:.8rem;font-weight:500;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none}
.btn-gold{background:var(--gold);color:#fff}
.btn-ghost{background:rgba(255,255,255,.1);color:#fff}
.btn-ghost:hover{background:rgba(255,255,255,.2)}
.btn-green{background:var(--green);color:#fff}

/* LAYOUT */
.designer-layout{display:flex;flex:1;overflow:hidden;min-height:0}
.sidebar{width:280px;background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0;min-height:0}
.canvas-area{flex:1;display:flex;align-items:center;justify-content:center;background:#2a2a3a;overflow:auto;position:relative;min-height:0}
.props-panel{width:260px;background:var(--white);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0;min-height:0}

/* SIDEBAR */
.panel-title{font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);padding:.75rem 1rem .5rem;border-bottom:1px solid var(--border);font-weight:500}
/* Sezioni a fisarmonica: <details> nativo, stato ricordato in localStorage
   (stesso schema del Ricordino Designer). */
.acc{flex-shrink:0}
.acc>summary{display:flex;align-items:center;gap:.4rem;cursor:pointer;list-style:none;user-select:none}
.acc>summary::-webkit-details-marker{display:none}
.acc>summary:hover{background:rgba(200,169,110,.07)}
.acc>summary .acc-lbl{flex:1}
.acc>summary .acc-arrow{font-size:.7rem;opacity:.7;transition:transform .15s;line-height:1}
.acc[open]>summary .acc-arrow{transform:rotate(180deg)}
.template-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;padding:.75rem}
.template-thumb{border:2px solid var(--border);border-radius:6px;overflow:hidden;cursor:pointer;transition:all .2s;aspect-ratio:3/4;background:#f0ece4;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--gray);text-align:center;padding:.5rem;position:relative}
.template-thumb:hover{border-color:var(--gold)}
.template-thumb.active{border-color:var(--gold);box-shadow:0 0 0 2px rgba(200,169,110,.3)}
.template-thumb img{width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0}
.template-label{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.6);color:#fff;font-size:.6rem;padding:3px;text-align:center}

.blocks-list{padding:.5rem}
.block-btn{width:100%;padding:.6rem .85rem;border:1px solid var(--border);border-radius:6px;margin-bottom:.4rem;cursor:pointer;font-size:.8rem;text-align:left;background:var(--cream);color:var(--ink);font-family:'DM Sans',sans-serif;display:flex;align-items:center;gap:.5rem;transition:all .15s}
.block-btn:hover{border-color:var(--gold);background:rgba(200,169,110,.05)}
.block-icon{font-size:1rem;width:20px;text-align:center}

.upload-area{padding:.75rem;border-top:1px solid var(--border)}
.upload-btn{width:100%;padding:.6rem;border:2px dashed var(--border);border-radius:8px;background:none;color:var(--gray);font-size:.8rem;cursor:pointer;font-family:'DM Sans',sans-serif;text-align:center;transition:all .2s}
.upload-btn:hover{border-color:var(--gold);color:var(--gold)}

/* PROPS PANEL */
.prop-group{padding:.75rem 1rem;border-bottom:1px solid var(--border)}
.prop-label{font-size:.7rem;font-weight:500;color:var(--gray);display:block;margin-bottom:.35rem}
.prop-input{width:100%;padding:.4rem .6rem;border:1px solid var(--border);border-radius:4px;font-size:.82rem;font-family:'DM Sans',sans-serif;background:var(--cream)}
.prop-input:focus{outline:none;border-color:var(--gold)}
.prop-row{display:flex;gap:.5rem}
.prop-row .prop-input{flex:1}
.color-row{display:flex;align-items:center;gap:.5rem}
.color-swatch{width:28px;height:28px;border-radius:4px;border:1px solid var(--border);cursor:pointer;flex-shrink:0}
.font-select{width:100%;padding:.4rem .6rem;border:1px solid var(--border);border-radius:4px;font-size:.82rem;font-family:'DM Sans',sans-serif;background:var(--cream)}
.btn-group-row{display:flex;gap:.4rem;margin-top:.4rem;flex-wrap:wrap}
.btn-sm{padding:.35rem .65rem;border-radius:4px;font-size:.75rem;cursor:pointer;border:1px solid var(--border);background:var(--cream);font-family:'DM Sans',sans-serif}
.btn-sm.active{background:var(--ink);color:#fff;border-color:var(--ink)}
.btn-danger{background:var(--red);color:#fff;border:none;width:100%;padding:.5rem;border-radius:6px;font-size:.82rem;cursor:pointer;margin-top:.5rem;font-family:'DM Sans',sans-serif}

.no-selection{padding:1.5rem;text-align:center;color:var(--gray);font-size:.82rem;font-style:italic}

/* CANVAS WRAPPER */
.canvas-wrapper{position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
canvas{display:block}

/* ZOOM */
.zoom-btn{width:26px;height:26px;border-radius:5px;background:rgba(255,255,255,.15);color:#fff;border:none;font-size:.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.zoom-btn:hover{background:rgba(255,255,255,.25)}
.zoom-label{background:rgba(255,255,255,.15);color:#fff;border-radius:6px;padding:0 .6rem;font-size:.75rem;display:flex;align-items:center}
.tool-btn{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.15);border-radius:4px;padding:.25rem .5rem;font-size:.72rem;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap}
.tool-btn:hover{background:rgba(200,169,110,.3);border-color:var(--gold)}
.layers-box{margin:.6rem;border:1px solid var(--border);border-radius:6px;overflow:hidden;background:var(--cream)}
.layers-actions{display:flex;gap:.3rem}
.layers-actions button{font-size:.62rem;padding:.15rem .4rem;border:1px solid var(--border);border-radius:3px;background:var(--cream);color:var(--gray);cursor:pointer;font-family:'DM Sans',sans-serif}
.layers-actions button:hover{background:#fff;color:var(--ink)}
.layers-list{max-height:180px;overflow-y:auto}
.layer-row{display:flex;align-items:center;gap:.5rem;padding:.35rem .6rem;font-size:.75rem;cursor:pointer;border-bottom:1px solid rgba(0,0,0,.04);transition:background .12s}
.layer-row:hover{background:#f5f0e8}
.layer-row.active{background:#e8dcc4}
.layer-row input[type=checkbox]{width:14px;height:14px;cursor:pointer;flex-shrink:0;margin:0}
.layer-icon{font-size:.85rem;width:16px;text-align:center;flex-shrink:0;color:var(--gray)}
.layer-name{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--ink)}
.layers-hint{padding:.4rem .6rem;font-size:.65rem;color:var(--gray);font-style:italic;line-height:1.4;border-top:1px solid rgba(0,0,0,.05)}
.layers-empty{padding:.6rem;text-align:center;color:var(--gray);font-size:.7rem;font-style:italic}
</style>
</head>
<body>
<script>
// Rifletti oggetto (usato dai bottoni Flip H/V della toolbar). Sta qui, prima
// della creazione del canvas, quindi legge sempre window.canvas per evitare
// problemi di scope.
function flipOggetto(asse) {
  var c = window.canvas;
  if (!c) { alert('Canvas non pronto'); return; }
  var obj = c.getActiveObject();
  if (!obj) { alert('Seleziona prima un elemento'); return; }
  if (asse === 'X') obj.set('flipX', !obj.flipX);
  if (asse === 'Y') obj.set('flipY', !obj.flipY);
  obj.setCoords();
  c.requestRenderAll();
  if (typeof saveState === 'function') saveState();
}
</script>

<nav>
  <div class="logo">MemorAI — Designer Manifesti</div>
  <div style="display:flex;align-items:center;gap:.5rem">
    <span style="color:rgba(255,255,255,.5);font-size:.75rem">Formato:</span>
    <select id="formato-select" onchange="cambiaFormato(this.value)" style="background:#fff;color:#1a1a2e;border:1px solid rgba(255,255,255,.3);border-radius:5px;padding:.3rem .6rem;font-size:.78rem;cursor:pointer">
      <option value="a4p" @selected($savedFormato=='a4p')>A4 Portrait (21x29.7cm)</option>
      <option value="a3p" @selected($savedFormato=='a3p')>A3 Portrait (29.7x42cm)</option>
      <option value="a4l" @selected($savedFormato=='a4l')>A4 Landscape (29.7x21cm)</option>
      <option value="50x70" @selected($savedFormato=='50x70')>50x70cm</option>
      <option value="70x100" @selected($savedFormato=='70x100')>70x100cm</option>
      <option value="a3l" @selected($savedFormato=='a3l')>A3 Landscape (42x29.7cm)</option>
      <option value="61x45" @selected($savedFormato=='61x45')>Manifesto (61x45.7cm)</option>
      <option value="50x32" @selected($savedFormato=='50x32')>Manifesto (50x32cm)</option>
    </select>
    <div style="display:flex;align-items:center;gap:.3rem;margin-left:.6rem">
      <button class="zoom-btn" onclick="setZoom(-0.1)" title="Riduci">−</button>
      <div class="zoom-label" id="zoom-label">100%</div>
      <button class="zoom-btn" onclick="setZoom(0.1)" title="Ingrandisci">+</button>
      <button class="zoom-btn" onclick="resetZoom()" title="Adatta alla finestra">⌂</button>
    </div>
  </div>
  <div class="nav-links">
    <span style="color:rgba(255,255,255,.6);font-size:.8rem">{{ $defunto->nomeCompleto() }}</span>
    <button class="nav-btn btn-ghost" onclick="clearCanvas()">🗑 Pulisci</button>
    <button class="nav-btn" style="background:#8a5c2e;color:#fff" onclick="saveAsTemplate()">📌 Salva come template</button>
    <button class="nav-btn btn-gold" onclick="exportPNG()">📥 Esporta PNG</button>
    <button class="nav-btn" style="background:#0f3460;color:#fff" onclick="exportPDF()">📄 Esporta PDF</button>
    <button class="nav-btn" style="background:rgba(255,255,255,.15);color:#fff" onclick="stampaManifesto()">🖨 Stampa</button>
    <button class="nav-btn btn-green" onclick="salvaManifesto()">💾 Salva</button>
    <a href="{{ route('necrologi.modifica', $necrologio) }}" class="nav-btn btn-ghost">← Necrologio</a>
  </div>
</nav>

<div class="designer-layout">

  <!-- SIDEBAR SINISTRA -->
  <div class="sidebar">
    <details class="acc" id="acc-sfondo" open>
      <summary class="panel-title"><span class="acc-lbl">Template Sfondo</span><span class="acc-arrow">▾</span></summary>
      <div class="template-grid">
        <div class="template-thumb active" onclick="loadTemplate('blank')" id="tpl-blank">
          <span>Bianco<br>Vuoto</span>
          <div class="template-label">Classico</div>
        </div>
      </div>
      <div class="upload-area">
        <input type="file" id="bg-upload" accept="image/*" style="display:none" onchange="uploadBackground(this)">
        <button class="upload-btn" onclick="document.getElementById('bg-upload').click()">
          📁 Carica sfondo personalizzato
        </button>
      </div>
    </details>

    <details class="acc" id="acc-blocchi" open>
      <summary class="panel-title"><span class="acc-lbl">Blocchi Testo</span><span class="acc-arrow">▾</span></summary>
      <div class="blocks-list">
        <button class="block-btn" onclick="addBlock('nome')"><span class="block-icon">👤</span>Nome Defunto</button>
        <button class="block-btn" onclick="addBlock('date')"><span class="block-icon">📅</span>Data di nascita</button>
        <button class="block-btn" onclick="addBlock('data_decesso')"><span class="block-icon">🕊</span>Data di decesso</button>
        <button class="block-btn" onclick="addBlock('frase')"><span class="block-icon">✨</span>Frase apertura</button>
        <button class="block-btn" onclick="addBlock('parenti')"><span class="block-icon">👨‍👩‍👧</span>Parenti</button>
        <button class="block-btn" onclick="addBlock('funerale')"><span class="block-icon">⛪</span>Info funerale</button>
        <button class="block-btn" onclick="addBlock('agenzia')"><span class="block-icon">🏢</span>Agenzia</button>
        <button class="block-btn" onclick="addBlock('eta')"><span class="block-icon">🔢</span>Età (anni vissuti)</button>
        <button class="block-btn" onclick="addBlock('testo')"><span class="block-icon">📝</span>Testo libero</button>
        <button class="block-btn" onclick="addBlock('linea')"><span class="block-icon">➖</span>Linea decorativa</button>
        <button class="block-btn" onclick="addBlock('logo')"><span class="block-icon">🖼</span>Logo agenzia</button>
        <button class="block-btn" onclick="openSantoModal()"><span class="block-icon">📿</span>Immagine Santo</button>
        <button class="block-btn" onclick="addQRNecrologio()"><span class="block-icon">📱</span>QR Necrologio</button>
      </div>
    </details>

    <details class="acc" id="acc-divisori">
      <summary class="panel-title"><span class="acc-lbl">Divisori</span><span class="acc-arrow">▾</span></summary>
      <div style="padding:.5rem">
        <button class="block-btn" onclick="addDivisore('linea')"><span class="block-icon">➖</span>Linea sottile</button>
        <button class="block-btn" onclick="addDivisore('linea_spessa')"><span class="block-icon">━</span>Linea spessa</button>
        <button class="block-btn" onclick="addDivisore('doppia')"><span class="block-icon">═</span>Doppia linea</button>
        <button class="block-btn" onclick="addDivisore('punteggiata')"><span class="block-icon">┈</span>Punteggiata</button>
        <button class="block-btn" onclick="addDivisore('rombo')"><span class="block-icon">◇</span>Linea con rombo</button>
        <button class="block-btn" onclick="addDivisore('foglie')"><span class="block-icon">🌿</span>Ramo / foglie</button>
        <button class="block-btn" onclick="addDivisore('fregio')"><span class="block-icon">❦</span>Fregio classico</button>
        <button class="block-btn" onclick="addDivisore('ornamento')"><span class="block-icon">⁂</span>Ornamento centrale</button>
        <button class="block-btn" onclick="addDivisore('croce')"><span class="block-icon">✝</span>Croce ornata</button>
      </div>
    </details>

    <details class="acc" id="acc-template">
      <summary class="panel-title"><span class="acc-lbl">I miei template</span><span class="acc-arrow">▾</span></summary>
      <div id="saved-templates-list" style="padding:.4rem">
        <div style="color:var(--gray);font-size:.75rem;font-style:italic;padding:.4rem">Caricamento...</div>
      </div>
    </details>
  </div>

  <!-- CANVAS -->
  <div style="display:flex;flex-direction:column;flex:1;overflow:hidden">
  <div style="background:#1e1e2e;border-bottom:1px solid rgba(255,255,255,.1);padding:.4rem 1rem;display:flex;align-items:center;gap:.5rem;flex-shrink:0;flex-wrap:wrap">
    <button id="btn-undo" onclick="undoAction()" title="Annulla (Ctrl+Z)" class="tool-btn" style="font-size:1rem" disabled>↩</button>
    <button id="btn-redo" onclick="redoAction()" title="Ripristina (Ctrl+Y)" class="tool-btn" style="font-size:1rem" disabled>↪</button>
    <div style="width:1px;height:20px;background:rgba(255,255,255,.15);margin:0 .25rem"></div>
    <button id="btn-guide" onclick="toggleGuide()" title="Linee guida" class="tool-btn">⊹ Guide</button>
    <div style="width:1px;height:20px;background:rgba(255,255,255,.15);margin:0 .25rem"></div>
    <span style="color:rgba(255,255,255,.4);font-size:.7rem;margin-right:.25rem">ALLINEA</span>
    <button class="tool-btn" onclick="alignObjects('left')" title="Allinea a sinistra">⬛⬜⬜</button>
    <button class="tool-btn" onclick="alignObjects('centerH')" title="Centra orizzontalmente">⬜⬛⬜</button>
    <button class="tool-btn" onclick="alignObjects('right')" title="Allinea a destra">⬜⬜⬛</button>
    <button class="tool-btn" onclick="alignObjects('top')" title="Allinea in alto">⬛</button>
    <button class="tool-btn" onclick="alignObjects('centerV')" title="Centra verticalmente">↕</button>
    <button class="tool-btn" onclick="alignObjects('bottom')" title="Allinea in basso">⬇</button>
    <div style="width:1px;height:20px;background:rgba(255,255,255,.15);margin:0 .25rem"></div>
    <button class="tool-btn" onclick="distributeObjects('h')" title="Distribuisci orizzontalmente">↔ Dist H</button>
    <button class="tool-btn" onclick="distributeObjects('v')" title="Distribuisci verticalmente">↕ Dist V</button>
    <div style="width:1px;height:20px;background:rgba(255,255,255,.15);margin:0 .25rem"></div>
    <button class="tool-btn" onclick="bringForward()" title="Porta avanti">▲</button>
    <button class="tool-btn" onclick="sendBackward()" title="Porta indietro">▼</button>
    <div style="width:1px;height:20px;background:rgba(255,255,255,.15);margin:0 .25rem"></div>
    <span style="color:rgba(255,255,255,.4);font-size:.7rem;margin-right:.25rem">FOTO</span>
    <button class="tool-btn" onclick="document.getElementById('foto-upload').click()">📷 Inserisci Foto</button>
    <input type="file" id="foto-upload" accept="image/*" style="display:none" onchange="insertPhoto(this)">
    <button class="tool-btn" id="btn-foto-pratica" onclick="inserisciFotoPrincipale()"
            style="display:inline-flex;align-items:center;gap:.4rem"
            @if(!($fotoPrincipale ?? null)) disabled title="Nessuna foto ancora caricata nella pratica" @endif>
      @if($fotoPrincipale ?? null)
        <img src="{{ $fotoPrincipale }}" style="width:22px;height:28px;object-fit:cover;border-radius:2px;border:1px solid rgba(255,255,255,.3)">
      @else
        🖼
      @endif
      Foto dell'ordine
    </button>
    <input type="file" id="santo-upload" accept="image/*" style="display:none" onchange="insertSanto(this)">
    <button class="tool-btn" onclick="flipOggetto('X')" title="Rifletti orizzontale">↔ Flip H</button>
    <button class="tool-btn" onclick="flipOggetto('Y')" title="Rifletti verticale">↕ Flip V</button>
  </div>
  <div class="canvas-area" id="canvas-area">
    <div class="canvas-wrapper" id="canvas-wrapper">
      <canvas id="manifesto-canvas"></canvas>
    </div>
  </div>
  </div>

<!-- PANEL DESTRO PROPRIETÀ -->
  <div class="props-panel" id="props-panel">
    <details class="acc" id="acc-livelli-dx" open>
      <summary class="panel-title">
        <span class="acc-lbl">Livelli</span>
        <div class="layers-actions">
          <button type="button" onclick="event.preventDefault();event.stopPropagation();selectAllLayers()">Tutti</button>
          <button type="button" onclick="event.preventDefault();event.stopPropagation();deselectAllLayers()">Nessuno</button>
        </div>
        <span class="acc-arrow">▾</span>
      </summary>
      <div class="layers-box">
        <div id="layers-list" class="layers-list"></div>
        <div class="layers-hint">Spunta 2+ livelli, poi usa i pulsanti di allineamento in alto.</div>
      </div>
    </details>

    <details class="acc" id="acc-proprieta" open>
      <summary class="panel-title"><span class="acc-lbl">Proprietà Elemento</span><span class="acc-arrow">▾</span></summary>
    <div class="no-selection" id="no-selection">Seleziona un elemento sul canvas per modificarne le proprietà</div>
    <div id="props-content" style="display:none">
      <div class="prop-group">
        <span class="prop-label">Testo</span>
        <textarea class="prop-input" id="prop-text" rows="3" oninput="updateProp('text')" style="resize:vertical"></textarea>
      </div>
      <div class="prop-group">
        <span class="prop-label">Font</span>
        <select class="font-select" id="prop-font" onchange="updateProp('font')">
          <optgroup label="— Eleganti Serif —">
            <option value="Cormorant Garamond">Cormorant Garamond</option>
            <option value="Playfair Display">Playfair Display</option>
            <option value="EB Garamond">EB Garamond</option>
            <option value="Libre Baskerville">Libre Baskerville</option>
            <option value="Lora">Lora</option>
            <option value="Crimson Text">Crimson Text</option>
            <option value="Merriweather">Merriweather</option>
            <option value="Spectral">Spectral</option>
          </optgroup>
          <optgroup label="— Classici e Formali —">
            <option value="Cinzel">Cinzel (maiuscoletto romano)</option>
            <option value="GFS Didot">GFS Didot</option>
            <option value="Philosopher">Philosopher</option>
            <option value="Goudy Bookletter 1911">Goudy Bookletter 1911</option>
            <option value="Georgia">Georgia</option>
            <option value="Times New Roman">Times New Roman</option>
          </optgroup>
          <optgroup label="— Calligrafici e Corsivi —">
            <option value="Pinyon Script">Pinyon Script</option>
            <option value="Dancing Script">Dancing Script</option>
            <option value="Monotype Corsiva">Monotype Corsiva</option>
            <option value="UnifrakturMaguntia">UnifrakturMaguntia (gotico)</option>
          </optgroup>
          <optgroup label="— Moderni e Sans —">
            <option value="DM Sans">DM Sans</option>
            <option value="Arial">Arial</option>
            <option value="Arial Black">Arial Black</option>
            <option value="Impact">Impact</option>
          </optgroup>
        </select>
      </div>
      <div class="prop-group">
        <div class="prop-row">
          <div>
            <span class="prop-label">Dimensione</span>
            <div style="display:flex;align-items:center;gap:.3rem">
              <button class="btn-sm" onclick="changeFontSize(-1)" style="width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:600">−</button>
              <input type="number" class="prop-input" id="prop-size" value="20" min="8" max="800" oninput="updateProp('size')" style="width:60px;text-align:center">
              <button class="btn-sm" onclick="changeFontSize(1)" style="width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:600">+</button>
            </div>
          </div>
          <div>
            <span class="prop-label">Colore</span>
            <div class="color-row">
              <input type="color" class="color-swatch" id="prop-color" value="#000000" oninput="updateProp('color')">
              <input type="text" class="prop-input" id="prop-color-hex" value="#000000" oninput="syncColor()" style="font-size:.75rem">
            </div>
          </div>
        </div>
        <div style="margin-top:.6rem">
          <span class="prop-label">Interlinea</span>
          <div style="display:flex;align-items:center;gap:.5rem;margin-top:.25rem">
            <input type="range" id="prop-lineh" min="0.8" max="4" step="0.1" value="1.2" oninput="updateProp('lineHeight');document.getElementById('prop-lineh-val').textContent=parseFloat(this.value).toFixed(1)" style="flex:1">
            <span id="prop-lineh-val" style="font-size:.75rem;min-width:24px;color:var(--gray)">1.2</span>
          </div>
        </div>
        <div style="margin-top:.6rem">
          <span class="prop-label">Traccia (contorno)</span>
          <div style="display:flex;align-items:center;gap:.4rem;margin-top:.25rem">
            <input type="color" class="color-swatch" id="prop-stroke" value="#ffffff" oninput="updateProp('stroke')" title="Colore traccia">
            <input type="range" id="prop-strokew" min="0" max="12" step="0.5" value="0" oninput="updateProp('strokew');document.getElementById('prop-strokew-val').textContent=this.value" style="flex:1" title="Spessore traccia">
            <span id="prop-strokew-val" style="font-size:.75rem;min-width:24px;color:var(--gray)">0</span>
          </div>
        </div>
      </div>
      <div class="prop-group">
        <span class="prop-label">Stile</span>
        <div class="btn-group-row">
          <button class="btn-sm" id="btn-bold" onclick="toggleStyle('bold')"><b>G</b></button>
          <button class="btn-sm" id="btn-italic" onclick="toggleStyle('italic')"><i>C</i></button>
          <button class="btn-sm" id="btn-left" onclick="setAlign('left')">⬅</button>
          <button class="btn-sm" id="btn-center" onclick="setAlign('center')">↔</button>
          <button class="btn-sm" id="btn-right" onclick="setAlign('right')">➡</button>
        </div>
      </div>
      <div class="prop-group">
        <span class="prop-label">Posizione (X / Y)</span>
        <div class="prop-row">
          <input type="number" class="prop-input" id="prop-x" oninput="updateProp('x')">
          <input type="number" class="prop-input" id="prop-y" oninput="updateProp('y')">
        </div>
      </div>
      <div class="prop-group">
        <span class="prop-label">Larghezza</span>
        <input type="number" class="prop-input" id="prop-width" oninput="updateProp('width')">
      </div>
      <div class="prop-group" id="border-group" style="display:none">
        <span class="prop-label">Bordo / Traccia</span>
        <div class="prop-row" style="align-items:center;margin-bottom:.5rem">
          <div>
            <span class="prop-label">Spessore</span>
            <input type="number" class="prop-input" id="prop-stroke-width" value="0" min="0" max="30" oninput="updateBorder()">
          </div>
          <div>
            <span class="prop-label">Colore</span>
            <div class="color-row">
              <input type="color" class="color-swatch" id="prop-stroke-color" value="#c8a96e" oninput="updateBorder()">
              <input type="text" class="prop-input" id="prop-stroke-hex" value="#c8a96e" oninput="syncStrokeColor()" style="font-size:.75rem">
            </div>
          </div>
        </div>
        <div class="btn-group-row">
          <button class="btn-sm" onclick="setStrokePreset(0)">Nessuno</button>
          <button class="btn-sm" onclick="setStrokePreset(2)">Sottile</button>
          <button class="btn-sm" onclick="setStrokePreset(5)">Medio</button>
          <button class="btn-sm" onclick="setStrokePreset(10)">Spesso</button>
        </div>
        <span class="prop-label" style="margin-top:.6rem;display:block">Maschera</span>
        <div class="btn-group-row">
          <button class="btn-sm" onclick="setImageMask('none')" title="Rettangolare">▭</button>
          <button class="btn-sm" onclick="setImageMask('round')" title="Angoli tondi">▢</button>
          <button class="btn-sm" onclick="setImageMask('oval')" title="Ovale">◯</button>
        </div>
        <div id="mask-radius-row" style="margin-top:.4rem;display:none">
          <span class="prop-label">Raggio angoli</span>
          <input type="range" id="prop-mask-radius" min="4" max="120" step="2" value="30" oninput="updateMaskRadius()" style="width:100%">
        </div>
      </div>
      <div class="prop-group">
        <button class="btn-danger" onclick="deleteSelected()">🗑 Elimina elemento</button>
      </div>
    </div>
    </details>
  </div>

<!-- MODAL SANTI -->
<div id="santo-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center">
  <div style="background:var(--white);border-radius:12px;padding:1.5rem;width:600px;max-width:95vw;max-height:85vh;overflow-y:auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
      <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem">Scegli Immagine Santo</div>
      <button onclick="closeSantoModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--gray)">✕</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1rem" id="santi-gallery">
      <div style="text-align:center;color:var(--gray);font-size:.8rem;grid-column:1/-1;padding:1rem">
        Nessuna immagine nella galleria.<br>Carica la prima immagine di un santo.
      </div>
    </div>
    <div style="border-top:1px solid var(--border);padding-top:1rem;display:flex;gap:.75rem">
      <button onclick="document.getElementById('santo-upload').click()" style="flex:1;padding:.6rem;background:var(--ink);color:#fff;border:none;border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif">
        📁 Carica nuova immagine santo
      </button>
    </div>
  </div>
</div>
</div>

{{-- Modale propria: al posto di prompt/confirm/alert, identica al Card Designer. --}}
<div id="app-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.72);z-index:1200;align-items:center;justify-content:center">
  <div style="background:#fdfaf5;border-radius:12px;padding:1.5rem;width:420px;max-width:94vw;box-shadow:0 18px 50px rgba(0,0,0,.35)">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.75rem">
      <div id="app-modal-title" style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:#1a1a2e;line-height:1.2"></div>
      <button onclick="chiudiModale(null)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#8a7f72;line-height:1">✕</button>
    </div>
    <div id="app-modal-text" style="font-size:.82rem;color:#8a7f72;line-height:1.5"></div>
    <div id="app-modal-field" style="display:none;margin-top:.9rem">
      <span style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#8a7f72" id="app-modal-label"></span>
      <input type="text" id="app-modal-input" style="width:100%;border:1px solid #ddd8d0;border-radius:5px;padding:.4rem .55rem;font-size:.85rem;margin-top:.3rem" autocomplete="off">
    </div>
    <div id="app-modal-actions" style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.25rem;flex-wrap:wrap"></div>
  </div>
</div>

<script>
// ── DATI PRATICA ──
const praticaData = @json($praticaData ?? []);
const agenziaData = @json($agenziaData ?? []);
const savedCanvas = @json($savedCanvas);
const fotoPrincipale = @json($fotoPrincipale ?? null);
const csrfToken = '{{ csrf_token() }}';

// Allega CSRF e sessione a tutte le chiamate /admin/api/: gli endpoint sono
// protetti dall'autenticazione dell'area studio, non da un token condiviso.
(function () {
    const _fetch = window.fetch;
    window.fetch = function (input, init) {
        const url = typeof input === 'string' ? input : (input && input.url) || '';
        if (url.indexOf('/admin/api/') !== -1) {
            init = init || {};
            init.headers = new Headers(init.headers || {});
            init.headers.set('X-CSRF-TOKEN', csrfToken);
            init.headers.set('X-Requested-With', 'XMLHttpRequest');
            init.credentials = init.credentials || 'same-origin';
        }
        return _fetch.call(this, input, init);
    };
})();

// ── FORMATI (tabella unica: px a 96dpi per il canvas, mm per l'export) ──
const FORMATI = {
  'a4p':    { wpx: 794,  hpx: 1123, wmm: 210, hmm: 297,  label: 'A4 Verticale (21×29,7 cm)' },
  'a4l':    { wpx: 1123, hpx: 794,  wmm: 297, hmm: 210,  label: 'A4 Orizzontale (29,7×21 cm)' },
  'a3p':    { wpx: 1123, hpx: 1587, wmm: 297, hmm: 420,  label: 'A3 Verticale (29,7×42 cm)' },
  'a3l':    { wpx: 1587, hpx: 1123, wmm: 420, hmm: 297,  label: 'A3 Orizzontale (42×29,7 cm)' },
  '50x70':  { wpx: 1890, hpx: 2646, wmm: 500, hmm: 700,  label: '50×70 cm' },
  '70x100': { wpx: 2646, hpx: 3780, wmm: 700, hmm: 1000, label: '70×100 cm' },
  '61x45':  { wpx: 2305, hpx: 1727, wmm: 610, hmm: 457,  label: 'Manifesto 61×45,7 cm' },
  '50x32':  { wpx: 1890, hpx: 1210, wmm: 500, hmm: 320,  label: 'Manifesto 50×32 cm' },
};

let CANVAS_W = 1587;
let CANVAS_H = 1123;
let currentFormat = @json($savedFormato ?? 'a3l');
let zoom = 1;
let canvas;

function cambiaFormato(fmt) {
  const f = FORMATI[fmt];
  if (!f) return;
  currentFormat = fmt;
  CANVAS_W = f.wpx;
  CANVAS_H = f.hpx;
  canvas.setWidth(f.wpx);
  canvas.setHeight(f.hpx);
  // Riapplica il template di sfondo attivo con le nuove dimensioni
  const activeTpl = document.querySelector('.template-thumb.active');
  if (activeTpl) {
    const tplId = activeTpl.id.replace('tpl-','');
    loadTemplate(tplId);
  }
  // Riadatta l'immagine di sfondo caricata a mano, se presente
  const bgImg = canvas.getObjects().find(o => o.customType === 'background' && o.type === 'image');
  if (bgImg) {
    bgImg.set({ scaleX: f.wpx / bgImg.width, scaleY: f.hpx / bgImg.height });
  }
  canvas.renderAll();
  autoZoom();
}

window.onload = function() {
  // Registra la proprietà custom dei blocchi testo (nome/data/frase/età/...)
  // per la serializzazione JSON: serve al sistema di template e al salvataggio.
  fabric.Object.prototype.toObject = (function(toObject) {
    return function(propertiesToInclude) {
      return fabric.util.object.extend(toObject.call(this, propertiesToInclude), {
        customBlockType: this.customBlockType
      });
    };
  })(fabric.Object.prototype.toObject);

  fabric.Object.prototype.set({
    cornerSize: 14,
    cornerColor: '#ffffff',
    cornerStrokeColor: '#1a1a2e',
    cornerStyle: 'circle',
    transparentCorners: false,
    borderColor: '#c8a96e',
    borderScaleFactor: 2,
    padding: 4
  });
  // Niente inseguimento di scroll quando si scrive dentro un blocco: la
  // textarea nascosta di Fabric riceve il focus con preventScroll.
  if (fabric.IText && fabric.IText.prototype.initHiddenTextarea) {
    const _origInitHiddenTextarea = fabric.IText.prototype.initHiddenTextarea;
    fabric.IText.prototype.initHiddenTextarea = function () {
      _origInitHiddenTextarea.call(this);
      const ta = this.hiddenTextarea;
      if (ta && !ta.__noScrollChase) {
        const _focus = ta.focus.bind(ta);
        ta.focus = function (opt) {
          _focus(Object.assign({}, opt || {}, { preventScroll: true }));
        };
        ta.__noScrollChase = true;
      }
    };
  }

  canvas = window.canvas = new fabric.Canvas('manifesto-canvas', {
    width: CANVAS_W,
    height: CANVAS_H,
    backgroundColor: '#ffffff',
    preserveObjectStacking: true,
  });

  cambiaFormato(currentFormat);
  autoZoom();

  // Stato salvato in precedenza sulla pratica: prima il formato, poi il canvas.
  if (savedCanvas && savedCanvas.objects && savedCanvas.objects.length) {
    canvas.loadFromJSON(savedCanvas, function () {
      canvas.renderAll();
      refreshLayers();
    });
  }

  // preload Monotype Corsiva (tutte le varianti) poi re-render
  if (document.fonts && document.fonts.load) {
    Promise.all([
      document.fonts.load("20px 'Monotype Corsiva'"),
      document.fonts.load("italic 20px 'Monotype Corsiva'"),
      document.fonts.load("bold 20px 'Monotype Corsiva'"),
      document.fonts.load("bold italic 20px 'Monotype Corsiva'")
    ]).then(function(){ window.canvas.renderAll(); });
  }

  loadSavedTemplates();

  // Undo/redo + pannello livelli: un'unica registrazione per evento.
  canvas.on('object:added', function() { saveState(); refreshLayers(); });
  canvas.on('object:modified', function() { saveState(); refreshLayers(); });
  canvas.on('object:removed', function() { saveState(); refreshLayers(); });
  setTimeout(function(){ saveState(); }, 800);

  canvas.on('selection:created', updatePropsPanel);
  canvas.on('selection:updated', updatePropsPanel);
  canvas.on('selection:cleared', clearPropsPanel);
  canvas.on('object:modified', updatePropsPanel);
  canvas.on('selection:created', refreshLayers);
  canvas.on('selection:updated', refreshLayers);
  canvas.on('selection:cleared', refreshLayers);
  setTimeout(refreshLayers, 900);

  // ── SNAP TO GUIDES ──
  var SNAP_THR = 8;
  canvas.on('object:moving', function(e) {
    if (!guideVisible) return;
    var obj = e.target;
    var cl = obj.left, ct = obj.top;
    var cw = obj.getScaledWidth(), ch = obj.getScaledHeight();
    var W = canvas.getWidth(), H = canvas.getHeight();
    var gH = [0, H/2, H];
    var gV = [0, W/2, W];
    var nl = cl, nt = ct;
    var snappedH = false, snappedV = false;
    gV.forEach(function(g) {
      if (Math.abs(cl - g) < SNAP_THR) { nl = g; snappedV = true; }
      else if (Math.abs(cl + cw - g) < SNAP_THR) { nl = g - cw; snappedV = true; }
      else if (Math.abs(cl + cw/2 - g) < SNAP_THR) { nl = g - cw/2; snappedV = true; }
    });
    gH.forEach(function(g) {
      if (Math.abs(ct - g) < SNAP_THR) { nt = g; snappedH = true; }
      else if (Math.abs(ct + ch - g) < SNAP_THR) { nt = g - ch; snappedH = true; }
      else if (Math.abs(ct + ch/2 - g) < SNAP_THR) { nt = g - ch/2; snappedH = true; }
    });
    if (nl !== cl || nt !== ct) { obj.set({left: nl, top: nt}); obj.setCoords(); }
    guideLines.forEach(function(gl) {
      if (!gl) return;
      var isH = gl.x1 !== gl.x2;
      var isV = gl.y1 !== gl.y2;
      var highlight = (isH && snappedH) || (isV && snappedV);
      gl.set({ stroke: highlight ? 'rgba(200,169,110,.9)' : (gl._origStroke || gl.stroke), strokeWidth: highlight ? 2 : 1 });
      if (!gl._origStroke) gl._origStroke = gl.stroke;
    });
    canvas.requestRenderAll();
  });
  canvas.on('object:modified', function() {
    guideLines.forEach(function(gl) {
      if (gl && gl._origStroke) gl.set({ stroke: gl._origStroke, strokeWidth: 1 });
    });
    canvas.requestRenderAll();
  });
  canvas.on('object:scaling', function(e){
    var o = e.target;
    var corner = e.transform && e.transform.corner;
    if (o && o.type === 'textbox' && (corner === 'mr' || corner === 'ml')) {
      if (o.splitByGrapheme !== false) o.splitByGrapheme = false;
      if (!o._origWidth) o._origWidth = o.width;
    }
  });
  canvas.on('text:changed', function(e) {
    document.getElementById('prop-text').value = e.target.text;
  });

  initAccordion();
};

// ── SEZIONI SIDEBAR (fisarmonica) ──
const ACC_KEY = 'manifesto-designer:sezioni';
function initAccordion() {
  let stato = {};
  try { stato = JSON.parse(localStorage.getItem(ACC_KEY) || '{}'); } catch (e) {}
  document.querySelectorAll('.sidebar details.acc, .props-panel details.acc').forEach(function(d) {
    if (Object.prototype.hasOwnProperty.call(stato, d.id)) d.open = !!stato[d.id];
    d.addEventListener('toggle', salvaStatoAccordion);
  });
}
function salvaStatoAccordion() {
  const stato = {};
  document.querySelectorAll('.sidebar details.acc, .props-panel details.acc').forEach(function(d) { stato[d.id] = d.open; });
  try { localStorage.setItem(ACC_KEY, JSON.stringify(stato)); } catch (e) {}
}

// ── ZOOM ──
function autoZoom() {
  zoom = 0.47; // lettura comoda del manifesto, poi regolabile con +/-
  applyZoom();
}
function setZoom(delta) {
  window._zoomManuale = true;
  zoom = Math.max(0.2, Math.min(2, zoom + delta));
  applyZoom();
}
function resetZoom() { window._zoomManuale = false; autoZoom(); }
window.addEventListener('resize', function() { if (window._zoomManuale) return; clearTimeout(window._rzTimer); window._rzTimer = setTimeout(autoZoom, 150); });
function applyZoom() {
  const wrapper = document.getElementById('canvas-wrapper');
  wrapper.style.transform = `scale(${zoom})`;
  wrapper.style.transformOrigin = 'center center';
  document.getElementById('zoom-label').textContent = Math.round(zoom * 100) + '%';
}

// ── TEMPLATE SFONDO (bordi decorativi rapidi, puramente client-side) ──
function getCanvasW(){ return canvas.getWidth(); }
function getCanvasH(){ return canvas.getHeight(); }

function loadTemplate(type) {
  document.querySelectorAll('.template-thumb').forEach(t => t.classList.remove('active'));
  document.getElementById('tpl-' + type)?.classList.add('active');

  canvas.getObjects().filter(o => o.customType === 'background').forEach(o => canvas.remove(o));

  const W = getCanvasW();
  const H = getCanvasH();

  if (type === 'blank') {
    canvas.backgroundColor = '#ffffff';
    addDecorativeBorder('#8a7f72', W, H);
  } else if (type === 'dark') {
    canvas.backgroundColor = '#1a1a2e';
    addDecorativeBorder('#c8a96e', W, H);
  } else if (type === 'floral') {
    canvas.backgroundColor = '#f8f4ee';
    addDecorativeBorder('#7a9a5a', W, H);
  } else if (type === 'minimal') {
    canvas.backgroundColor = '#fafafa';
    addMinimalBorder(W, H);
  }
  canvas.renderAll();
}

function addDecorativeBorder(color, W, H) {
  const margin = 20;
  const rect = new fabric.Rect({
    left: margin, top: margin,
    width: W - margin*2, height: H - margin*2,
    fill: 'transparent', stroke: color, strokeWidth: 2,
    selectable: false, evented: false, customType: 'background'
  });
  canvas.add(rect);
  canvas.sendToBack(rect);
}

function addMinimalBorder(W, H) {
  [20, H-20].forEach(y => {
    canvas.add(new fabric.Line([30, y, W-30, y], {
      stroke: '#ccc', strokeWidth: 1, selectable: false, evented: false, customType: 'background'
    }));
  });
}

function uploadBackground(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    fabric.Image.fromURL(e.target.result, function(img) {
      const W = canvas.getWidth();
      const H = canvas.getHeight();
      img.set({
        left: 0, top: 0,
        scaleX: W / img.width,
        scaleY: H / img.height,
        selectable: false, evented: false, customType: 'background'
      });
      canvas.getObjects().filter(o => o.customType === 'background').forEach(o => canvas.remove(o));
      canvas.add(img);
      canvas.sendToBack(img);
      canvas.renderAll();
    });
  };
  reader.readAsDataURL(input.files[0]);
}

// ── DIVISORI ──
var DIVISORI_SVG = {
  rombo: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 24"><line x1="10" y1="12" x2="180" y2="12" stroke="#000" stroke-width="1.5"/><path d="M200 4 L208 12 L200 20 L192 12 Z" fill="#000"/><line x1="220" y1="12" x2="390" y2="12" stroke="#000" stroke-width="1.5"/></svg>',
  foglie: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 40"><line x1="20" y1="20" x2="380" y2="20" stroke="#000" stroke-width="1"/><path d="M200 20 q-15 -12 -30 -4 q12 8 30 4 q15 -12 30 -4 q-12 8 -30 4Z" fill="#000"/><circle cx="165" cy="20" r="2.5" fill="#000"/><circle cx="235" cy="20" r="2.5" fill="#000"/></svg>',
  fregio: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 30"><path d="M10 15 h150 q10 0 15 -8 q5 8 15 8 q10 0 15 -8 q5 8 15 8 h150" fill="none" stroke="#000" stroke-width="1.5"/><circle cx="200" cy="7" r="3" fill="#000"/></svg>',
  ornamento: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 30"><line x1="10" y1="15" x2="160" y2="15" stroke="#000" stroke-width="1"/><text x="200" y="22" font-size="22" text-anchor="middle" fill="#000" font-family="serif">&#8258;</text><line x1="240" y1="15" x2="390" y2="15" stroke="#000" stroke-width="1"/></svg>',
  croce: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 40"><line x1="10" y1="20" x2="175" y2="20" stroke="#000" stroke-width="1"/><path d="M196 8 h8 v8 h8 v8 h-8 v8 h-8 v-8 h-8 v-8 h8 Z" fill="#000"/><line x1="225" y1="20" x2="390" y2="20" stroke="#000" stroke-width="1"/></svg>'
};
function addDivisore(tipo){
  var W = (typeof CANVAS_W !== 'undefined') ? CANVAS_W : canvas.getWidth();
  if (tipo === 'linea' || tipo === 'linea_spessa' || tipo === 'doppia' || tipo === 'punteggiata') {
    if (tipo === 'doppia') {
      var g = new fabric.Group([
        new fabric.Line([0,0,W-100,0],{stroke:'#1a1a2e',strokeWidth:1.5}),
        new fabric.Line([0,5,W-100,5],{stroke:'#1a1a2e',strokeWidth:1.5})
      ], {left:50, top:200, customType:'divisore'});
      canvas.add(g); canvas.setActiveObject(g); canvas.renderAll(); return;
    }
    var opt = {left:50, top:200, stroke:'#1a1a2e', customType:'divisore'};
    opt.strokeWidth = (tipo === 'linea_spessa') ? 4 : 1.5;
    if (tipo === 'punteggiata') opt.strokeDashArray = [4,4];
    var ln = new fabric.Line([0,0,W-100,0], opt);
    canvas.add(ln); canvas.setActiveObject(ln); canvas.renderAll(); return;
  }
  var svg = DIVISORI_SVG[tipo];
  if (!svg) return;
  fabric.loadSVGFromString(svg, function(objects, options){
    var obj = fabric.util.groupSVGElements(objects, options);
    obj.set({left:50, top:200, customType:'divisore'});
    var target = (W-100);
    if (obj.width) obj.scaleToWidth(target);
    canvas.add(obj); canvas.setActiveObject(obj); canvas.renderAll();
  });
}

// ── BLOCCHI TESTO ──
// Blocchi che contengono dati di UNA persona: vanno riempiti col defunto
// corrente e non devono mai finire dentro un template (tornano segnaposto).
const BLOCCHI_PERSONALI = ['nome', 'date', 'data_decesso', 'frase', 'eta'];

/**
 * Testo di un blocco personale per i dati passati. Senza dati (dati = null)
 * ritorna il segnaposto: è la forma con cui il blocco viene salvato nei template.
 */
function testoPersonale(tipo, dati) {
  var d = dati || {};
  switch (tipo) {
    case 'nome':         return (d.cognome || 'COGNOME') + ' ' + (d.nome || 'Nome');
    case 'date':          return d.data_nascita || '__/__/____';
    case 'data_decesso':  return d.data_morte || '__/__/____';
    case 'frase':         return d.frase || 'È mancato all\'affetto dei suoi cari';
    case 'eta':           return (d.anni !== null && d.anni !== undefined && d.anni !== '') ? 'di anni ' + Math.floor(d.anni) : 'di anni ___';
  }
  return '';
}

function addBlock(type) {
  let obj;
  const cx = CANVAS_W / 2;

  if (type === 'linea') {
    obj = new fabric.Line([50, 0, CANVAS_W-100, 0], {
      left: 50, top: 200,
      stroke: '#1a1a2e', strokeWidth: 1.5,
      selectable: true
    });
    canvas.add(obj);
    canvas.setActiveObject(obj);
    canvas.renderAll();
    return;
  }

  let text = '';
  let fontSize = 50;
  let fontFamily = 'Cormorant Garamond';
  let fontStyle = 'normal';
  let fontWeight = 'normal';
  let textAlign = 'center';
  let fill = '#1a1a2e';

  switch(type) {
    case 'nome':
      text = testoPersonale('nome', praticaData);
      fontSize = 95;
      fontWeight = 'bold';
      break;
    case 'date':
      text = testoPersonale('date', praticaData);
      fontSize = 47;
      break;
    case 'data_decesso':
      text = testoPersonale('data_decesso', praticaData);
      fontSize = 47;
      break;
    case 'frase':
      text = testoPersonale('frase', praticaData);
      fontSize = 56;
      fontWeight = 'bold';
      break;
    case 'parenti':
      // Nessuna generazione automatica: testo segnaposto da scrivere a mano,
      // stesso principio del blocco "frase".
      text = 'Ne danno il triste annuncio i familiari';
      fontSize = 47;
      fontWeight = 'bold';
      break;
    case 'funerale':
      var quando = (praticaData.cerimonia_data && praticaData.cerimonia_ora)
        ? praticaData.cerimonia_data + ' alle ore ' + praticaData.cerimonia_ora
        : '[data] alle ore [ora]';
      var partenza = praticaData.luogo_partenza
        ? praticaData.luogo_partenza + (praticaData.indirizzo_cerimonia ? ', ' + praticaData.indirizzo_cerimonia : '')
        : '[luogo]';
      var chiesaTesto = praticaData.chiesa
        ? praticaData.chiesa + (praticaData.indirizzo_chiesa ? ', ' + praticaData.indirizzo_chiesa : '')
        : '[chiesa]';
      text = 'I funerali si svolgeranno ' + quando + '\nPartenza da ' + partenza + '\n' + chiesaTesto;
      if (praticaData.cimitero) { text += '\nTumulazione: ' + praticaData.cimitero; }
      fontSize = 35;
      break;
    case 'agenzia':
      text = agenziaData.name || 'Nome Agenzia';
      fontSize = 35;
      fill = '#8a7f72';
      break;
    case 'eta':
      text = testoPersonale('eta', praticaData);
      fontSize = 47;
      fontFamily = 'GFS Didot';
      fontStyle = 'italic';
      break;
    case 'testo':
      text = 'Testo libero...';
      fontSize = 14;
      break;
    case 'logo':
      // Niente logo immagine (l'agenzia non ne ha ancora uno in questo
      // progetto): inserimento manuale del nome come blocco di testo.
      text = agenziaData.name || 'Nome Agenzia';
      fontSize = 30;
      fontWeight = 'bold';
      break;
  }

  obj = new fabric.Textbox(text, {
    left: 50,
    top: 100 + canvas.getObjects().length * 30,
    width: CANVAS_W - 100,
    fontSize: fontSize,
    fontFamily: fontFamily,
    fontStyle: fontStyle,
    fontWeight: fontWeight,
    textAlign: textAlign,
    fill: fill,
    editable: true,
    customBlockType: type,
  });

  canvas.add(obj);
  canvas.setActiveObject(obj);
  canvas.renderAll();
}

// ── QR NECROLOGIO ──
// Generato in locale (libreria kazuhikoarase/qrcode-generator, self-hosted):
// nessuna richiesta esterna, a differenza della sorgente che mandava l'URL
// (quindi il nome del defunto) a api.qrserver.com a ogni apertura.
function addQRNecrologio() {
  var url = praticaData.necrologio_url;
  if (!url) {
    modale({ titolo: 'Nessun QR disponibile', testo: 'Questo necrologio non ha ancora un indirizzo pubblico.' });
    return;
  }
  var qr = qrcode(0, 'M');
  qr.addData(url);
  qr.make();
  var qrSize = 120;
  var dataUrl = qr.createDataURL(8, 8);
  fabric.Image.fromURL(dataUrl, function(qrImg) {
    qrImg.set({
      left: CANVAS_W/2 - qrSize/2,
      top: CANVAS_H - 200,
      scaleX: qrSize/qrImg.width,
      scaleY: qrSize/qrImg.height,
      hasControls: true,
      hasBorders: true
    });
    canvas.add(qrImg);
    canvas.setActiveObject(qrImg);
    canvas.renderAll();
    pushUndo();
  });
}

// ── PANEL PROPRIETÀ ──
function updatePropsPanel() {
  const obj = canvas.getActiveObject();
  if (!obj) { clearPropsPanel(); return; }

  document.getElementById('no-selection').style.display = 'none';
  document.getElementById('props-content').style.display = 'block';

  if (obj.type === 'textbox' || obj.type === 'text') {
    document.getElementById('prop-text').value = obj.text || '';
    document.getElementById('prop-font').value = obj.fontFamily || 'Cormorant Garamond';
    document.getElementById('prop-size').value = obj.fontSize || 20;
    document.getElementById('prop-color').value = obj.fill || '#000000';
    document.getElementById('prop-color-hex').value = obj.fill || '#000000';
    document.getElementById('btn-bold').classList.toggle('active', obj.fontWeight === 'bold');
    document.getElementById('btn-italic').classList.toggle('active', obj.fontStyle === 'italic');
    document.getElementById('btn-left').classList.toggle('active', obj.textAlign === 'left');
    document.getElementById('btn-center').classList.toggle('active', obj.textAlign === 'center');
    document.getElementById('btn-right').classList.toggle('active', obj.textAlign === 'right');
    var _sw = obj.strokeWidth || 0;
    document.getElementById('prop-strokew').value = _sw;
    document.getElementById('prop-strokew-val').textContent = _sw;
    if (obj.stroke) document.getElementById('prop-stroke').value = obj.stroke;
  }

  document.getElementById('prop-x').value = Math.round(obj.left);
  document.getElementById('prop-y').value = Math.round(obj.top);
  document.getElementById('prop-width').value = Math.round(obj.width * (obj.scaleX || 1));

  const borderGroup = document.getElementById('border-group');
  if (obj.type === 'image') {
    borderGroup.style.display = 'block';
    document.getElementById('prop-stroke-width').value = obj.strokeWidth || 0;
    document.getElementById('prop-stroke-color').value = obj.stroke || '#c8a96e';
    document.getElementById('prop-stroke-hex').value = obj.stroke || '#c8a96e';
  } else {
    borderGroup.style.display = 'none';
  }
}

function clearPropsPanel() {
  document.getElementById('no-selection').style.display = 'block';
  document.getElementById('props-content').style.display = 'none';
}

function changeFontSize(delta) {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  const input = document.getElementById('prop-size');
  const current = parseInt(input.value) || 20;
  const step = current >= 100 ? 5 : current >= 50 ? 2 : 1;
  const newSize = Math.max(8, Math.min(800, current + delta * step));
  input.value = newSize;
  obj.set('fontSize', newSize);
  canvas.renderAll();
}

function updateProp(prop) {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  if (prop === 'text' && (obj.type === 'textbox' || obj.type === 'text')) {
    obj.set('text', document.getElementById('prop-text').value);
  } else if (prop === 'font') {
    obj.set('fontFamily', document.getElementById('prop-font').value);
  } else if (prop === 'size') {
    obj.set('fontSize', parseInt(document.getElementById('prop-size').value));
  } else if (prop === 'color') {
    const c = document.getElementById('prop-color').value;
    applicaColoreObj(obj, c);
    document.getElementById('prop-color-hex').value = c;
  } else if (prop === 'x') {
    obj.set('left', parseInt(document.getElementById('prop-x').value));
  } else if (prop === 'y') {
    obj.set('top', parseInt(document.getElementById('prop-y').value));
  } else if (prop === 'width') {
    if (obj.type === 'textbox') obj.set('width', parseInt(document.getElementById('prop-width').value));
  } else if (prop === 'lineHeight') {
    obj.set('lineHeight', parseFloat(document.getElementById('prop-lineh').value));
  } else if (prop === 'stroke') {
    if (obj.type === 'textbox' || obj.type === 'text') {
      obj.set('stroke', document.getElementById('prop-stroke').value);
      obj.set('paintFirst', 'stroke');
    }
  } else if (prop === 'strokew') {
    if (obj.type === 'textbox' || obj.type === 'text') {
      var w = parseFloat(document.getElementById('prop-strokew').value);
      obj.set('strokeWidth', w);
      if (w > 0) { obj.set('stroke', document.getElementById('prop-stroke').value); obj.set('paintFirst', 'stroke'); }
    }
  }
  obj.setCoords();
  canvas.renderAll();
}

function applicaColoreObj(obj, c){
  if (!obj) return;
  if (obj.type === 'line') {
    obj.set('stroke', c);
  } else if (obj.type === 'group') {
    obj.forEachObject(function(o){
      if (o.stroke) o.set('stroke', c);
      if (o.fill && o.fill !== '' && o.fill !== 'transparent') o.set('fill', c);
    });
    obj.set('dirty', true);
  } else if (obj.type === 'path') {
    if (obj.stroke) obj.set('stroke', c);
    if (obj.fill && obj.fill !== '' && obj.fill !== 'transparent') obj.set('fill', c);
  } else if (obj.type === 'textbox' && obj.isEditing && obj.selectionStart !== obj.selectionEnd) {
    obj.setSelectionStyles({ fill: c });
  } else {
    obj.set('fill', c);
  }
}
function syncColor() {
  const hex = document.getElementById('prop-color-hex').value;
  if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
    document.getElementById('prop-color').value = hex;
    const obj = canvas.getActiveObject();
    if (obj) {
      applicaColoreObj(obj, hex);
      canvas.renderAll();
    }
  }
}

function toggleStyle(style) {
  const obj = canvas.getActiveObject();
  if (!obj || (obj.type !== 'textbox' && obj.type !== 'text')) return;
  if (style === 'bold') {
    obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold');
    document.getElementById('btn-bold').classList.toggle('active');
  } else if (style === 'italic') {
    obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic');
    document.getElementById('btn-italic').classList.toggle('active');
  }
  canvas.renderAll();
}

function setAlign(align) {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  obj.set('textAlign', align);
  ['left','center','right'].forEach(a => document.getElementById('btn-'+a).classList.remove('active'));
  document.getElementById('btn-'+align).classList.add('active');
  canvas.renderAll();
}

function deleteSelected() {
  const obj = canvas.getActiveObject();
  if (obj) { canvas.remove(obj); clearPropsPanel(); canvas.renderAll(); }
}

function clearCanvas() {
  if (!confirm('Vuoi pulire il canvas?')) return;
  canvas.clear();
  canvas.backgroundColor = '#ffffff';
  loadTemplate('blank');
}

// ── GALLERIA SANTI (condivisa col Ricordino Designer, stessi endpoint) ──
function openSantoModal() {
  document.getElementById('santo-modal').style.display = 'flex';
  loadSantiGallery();
}
function closeSantoModal() {
  document.getElementById('santo-modal').style.display = 'none';
}
function eliminaSanto(id) {
  if (!confirm('Eliminare questa immagine dalla galleria?')) return;
  fetch('/admin/api/santi/' + id, { method: 'DELETE' })
    .then(r => r.json()).then(res => { if (res.success) loadSantiGallery(); });
}
function loadSantiGallery() {
  fetch('/admin/api/santi')
    .then(r => r.json())
    .then(santi => {
      const gallery = document.getElementById('santi-gallery');
      if (!santi.length) {
        gallery.innerHTML = '<div style="text-align:center;color:var(--gray);font-size:.8rem;grid-column:1/-1;padding:1rem">Nessuna immagine nella galleria.<br>Carica la prima immagine di un santo.</div>';
        return;
      }
      gallery.innerHTML = santi.map(s => `
        <div style="position:relative;cursor:pointer;border:2px solid var(--border);border-radius:8px;overflow:hidden;transition:all .2s"
             onclick="insertSantoFromGallery('${s.url}')" data-sid="${s.id}"
             onmouseover="this.style.borderColor='var(--gold)'"
             onmouseout="this.style.borderColor='var(--border)'">
          <button onclick="event.stopPropagation();eliminaSanto(${s.id})" title="Elimina"
                  style="position:absolute;top:3px;right:3px;z-index:2;background:rgba(180,30,30,.9);color:#fff;border:none;border-radius:50%;width:20px;height:20px;line-height:1;cursor:pointer;font-size:.7rem">&#10005;</button>
          <img src="${s.url}" style="width:100%;aspect-ratio:3/4;object-fit:cover">
          <div style="font-size:.68rem;padding:4px;text-align:center;color:var(--gray)">${s.name}</div>
        </div>
      `).join('');
    });
}
function insertSanto(input) {
  if (!input.files[0]) return;
  const name = prompt('Nome del santo:', input.files[0].name.replace(/\.[^.]+$/, ''));
  if (!name) return;

  const formData = new FormData();
  formData.append('image', input.files[0]);
  formData.append('name', name);

  fetch('/admin/api/santi', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        loadSantiGallery();
        insertSantoFromGallery(res.url);
      }
    });
  input.value = '';
}
function insertSantoFromGallery(url) {
  closeSantoModal();
  fabric.Image.fromURL(url, function(img) {
    const maxH = canvas.getHeight() * 0.35;
    const maxW = canvas.getWidth() * 0.3;
    const scale = Math.min(maxW / img.width, maxH / img.height);
    img.set({
      left: canvas.getWidth() / 2 - (img.width * scale) / 2,
      top: 30,
      scaleX: scale,
      scaleY: scale,
      selectable: true,
      hasControls: true,
      hasBorders: true,
      strokeWidth: 0,
      stroke: '#c8a96e',
      customType: 'santo'
    });
    canvas.add(img);
    canvas.setActiveObject(img);
    canvas.renderAll();
  });
}

// ── BORDO / MASCHERA FOTO ──
function updateBorder() {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  const sw = parseInt(document.getElementById('prop-stroke-width').value) || 0;
  const sc = document.getElementById('prop-stroke-color').value;
  obj.set({ strokeWidth: sw, stroke: sw > 0 ? sc : null });
  document.getElementById('prop-stroke-hex').value = sc;
  canvas.renderAll();
}

function syncStrokeColor() {
  const hex = document.getElementById('prop-stroke-hex').value;
  if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
    document.getElementById('prop-stroke-color').value = hex;
    updateBorder();
  }
}

function setImageMask(kind) {
  var obj = canvas.getActiveObject();
  if (!obj || obj.type !== 'image') return;
  var w = obj.width, h = obj.height;
  var radiusRow = document.getElementById('mask-radius-row');
  if (kind === 'none') {
    obj.clipPath = null;
    if (radiusRow) radiusRow.style.display = 'none';
  } else if (kind === 'oval') {
    obj.clipPath = new fabric.Ellipse({
      rx: w/2, ry: h/2, originX: 'center', originY: 'center'
    });
    if (radiusRow) radiusRow.style.display = 'none';
  } else if (kind === 'round') {
    var r = parseInt((document.getElementById('prop-mask-radius')||{}).value || 30);
    obj.clipPath = new fabric.Rect({
      width: w, height: h, rx: r, ry: r, originX: 'center', originY: 'center'
    });
    if (radiusRow) radiusRow.style.display = 'block';
  }
  obj.dirty = true;
  canvas.renderAll();
}
function updateMaskRadius() {
  var obj = canvas.getActiveObject();
  if (!obj || obj.type !== 'image' || !obj.clipPath) return;
  var r = parseInt(document.getElementById('prop-mask-radius').value);
  obj.clipPath.set({ rx: r, ry: r });
  obj.dirty = true;
  canvas.renderAll();
}
function setStrokePreset(size) {
  document.getElementById('prop-stroke-width').value = size;
  updateBorder();
}

// ── ALLINEAMENTO / DISTRIBUZIONE MULTIPLA ──
function alignObjects(type) {
  const objs = canvas.getActiveObjects();
  if (!objs || objs.length < 2) { alert('Seleziona almeno 2 elementi'); return; }
  const W = canvas.getWidth();
  const H = canvas.getHeight();

  let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
  objs.forEach(o => {
    const b = o.getBoundingRect();
    minX = Math.min(minX, b.left);
    minY = Math.min(minY, b.top);
    maxX = Math.max(maxX, b.left + b.width);
    maxY = Math.max(maxY, b.top + b.height);
  });

  objs.forEach(o => {
    const b = o.getBoundingRect();
    if (type === 'left') o.set('left', minX + (o.left - b.left));
    else if (type === 'right') o.set('left', maxX - b.width + (o.left - b.left));
    else if (type === 'centerH') o.set('left', (minX + maxX) / 2 - b.width / 2 + (o.left - b.left));
    else if (type === 'top') o.set('top', minY + (o.top - b.top));
    else if (type === 'bottom') o.set('top', maxY - b.height + (o.top - b.top));
    else if (type === 'centerV') o.set('top', (minY + maxY) / 2 - b.height / 2 + (o.top - b.top));
    o.setCoords();
  });
  canvas.renderAll();
}

function distributeObjects(dir) {
  const objs = canvas.getActiveObjects();
  if (!objs || objs.length < 3) { alert('Seleziona almeno 3 elementi'); return; }

  if (dir === 'h') {
    const sorted = [...objs].sort((a,b) => a.getBoundingRect().left - b.getBoundingRect().left);
    const first = sorted[0].getBoundingRect();
    const last = sorted[sorted.length-1].getBoundingRect();
    const totalW = sorted.reduce((s,o) => s + o.getBoundingRect().width, 0);
    const gap = (last.left + last.width - first.left - totalW) / (sorted.length - 1);
    let x = first.left;
    sorted.forEach(o => {
      const b = o.getBoundingRect();
      o.set('left', x + (o.left - b.left));
      o.setCoords();
      x += b.width + gap;
    });
  } else {
    const sorted = [...objs].sort((a,b) => a.getBoundingRect().top - b.getBoundingRect().top);
    const first = sorted[0].getBoundingRect();
    const last = sorted[sorted.length-1].getBoundingRect();
    const totalH = sorted.reduce((s,o) => s + o.getBoundingRect().height, 0);
    const gap = (last.top + last.height - first.top - totalH) / (sorted.length - 1);
    let y = first.top;
    sorted.forEach(o => {
      const b = o.getBoundingRect();
      o.set('top', y + (o.top - b.top));
      o.setCoords();
      y += b.height + gap;
    });
  }
  canvas.renderAll();
}

function bringForward() {
  const obj = canvas.getActiveObject();
  if (obj) { canvas.bringForward(obj); canvas.renderAll(); refreshLayers(); }
}
function sendBackward() {
  const obj = canvas.getActiveObject();
  if (obj) { canvas.sendBackwards(obj); canvas.renderAll(); refreshLayers(); }
}

// ── FOTO ──
function insertPhoto(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    fabric.Image.fromURL(e.target.result, function(img) {
      const maxW = canvas.getWidth() * 0.4;
      const maxH = canvas.getHeight() * 0.4;
      const scale = Math.min(maxW / img.width, maxH / img.height);
      img.set({
        left: canvas.getWidth() / 2 - (img.width * scale) / 2,
        top: canvas.getHeight() / 2 - (img.height * scale) / 2,
        scaleX: scale,
        scaleY: scale,
        selectable: true,
        hasControls: true,
        hasBorders: true,
        strokeWidth: 0,
        stroke: '#c8a96e',
        customType: 'photo'
      });
      canvas.add(img);
      canvas.setActiveObject(img);
      canvas.renderAll();
    });
  };
  reader.readAsDataURL(input.files[0]);
  input.value = '';
}

// La stessa foto scelta come principale nel Foto Manager, così ricordino,
// manifesto e card partono tutti dalla stessa immagine.
function inserisciFotoPrincipale() {
  if (!fotoPrincipale) return;
  fabric.Image.fromURL(fotoPrincipale, function(img) {
    const maxW = canvas.getWidth() * 0.4;
    const maxH = canvas.getHeight() * 0.4;
    const scale = Math.min(maxW / img.width, maxH / img.height);
    img.set({
      left: canvas.getWidth() / 2 - (img.width * scale) / 2,
      top: canvas.getHeight() / 2 - (img.height * scale) / 2,
      scaleX: scale,
      scaleY: scale,
      selectable: true,
      hasControls: true,
      hasBorders: true,
      strokeWidth: 0,
      stroke: '#c8a96e',
      customType: 'photo'
    });
    canvas.add(img);
    canvas.setActiveObject(img);
    canvas.renderAll();
  }, { crossOrigin: 'anonymous' });
}

// ── EXPORT (client-side, nessuna chiamata server) ──
function exportPNG() {
  const dataURL = canvas.toDataURL({ format: 'png', quality: 1, multiplier: 2 });
  const a = document.createElement('a');
  a.href = dataURL;
  a.download = 'manifesto.png';
  a.click();
}

function exportPDF() {
  const { jsPDF } = window.jspdf;
  const f = FORMATI[currentFormat] || FORMATI['a3l'];
  const orientamento = f.wmm > f.hmm ? 'landscape' : 'portrait';
  const pdf = new jsPDF({ orientation: orientamento, unit: 'mm', format: [f.wmm, f.hmm] });
  const dataURL = canvas.toDataURL({ format: 'jpeg', quality: 1, multiplier: 6 });
  pdf.addImage(dataURL, 'JPEG', 0, 0, f.wmm, f.hmm);
  pdf.save('manifesto.pdf');
}

// Stampa: genera il PDF nel formato scelto e apre il dialogo di stampa di sistema
function stampaManifesto() {
  const { jsPDF } = window.jspdf;
  const f = FORMATI[currentFormat] || FORMATI['a3l'];
  const orientamento = f.wmm > f.hmm ? 'landscape' : 'portrait';
  const pdf = new jsPDF({ orientation: orientamento, unit: 'mm', format: [f.wmm, f.hmm] });
  const dataURL = canvas.toDataURL({ format: 'jpeg', quality: 1, multiplier: 6 });
  pdf.addImage(dataURL, 'JPEG', 0, 0, f.wmm, f.hmm);
  pdf.autoPrint();
  const blobUrl = pdf.output('bloburl');
  const ifr = document.createElement('iframe');
  ifr.style.display = 'none';
  ifr.src = blobUrl;
  document.body.appendChild(ifr);
  setTimeout(function(){ try { ifr.contentWindow.focus(); ifr.contentWindow.print(); } catch(e) { window.open(blobUrl); } }, 800);
}

// ── SALVATAGGIO SULLA PRATICA ──
// Sostituisce il vecchio saveToPratica(): lo stato arriva già iniettato dal
// controller all'apertura, qui si manda solo canvas + PDF pronto stampa.
async function salvaManifesto() {
  const btn = event.target;
  const testoOriginale = btn.textContent;
  btn.textContent = '⏳...'; btn.disabled = true;

  const fmt = FORMATI[currentFormat];
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: fmt.wmm > fmt.hmm ? 'landscape' : 'portrait', unit: 'mm', format: [fmt.wmm, fmt.hmm] });
  const dataUrlCanvas = canvas.toDataURL({ format: 'jpeg', quality: 0.92, multiplier: 3 });
  doc.addImage(dataUrlCanvas, 'JPEG', 0, 0, fmt.wmm, fmt.hmm);
  const pdfDataUrl = doc.output('datauristring');
  // Miniatura leggera per la pagina pubblica: lì non deve arrivare né il PDF
  // né l'export a piena risoluzione, solo un'anteprima cliccabile.
  const anteprima = canvas.toDataURL({ format: 'jpeg', quality: 0.6, multiplier: 0.5 });

  try {
    const res = await fetch('/admin/api/necrologi/{{ $necrologio->id }}/salva-manifesto', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        canvas: JSON.stringify(canvas.toJSON(['customType', 'customBlockType'])),
        formato: currentFormat,
        pdf: pdfDataUrl,
        anteprima: anteprima,
      }),
    });
    const data = await res.json();
    if (data.success) {
      toastMsg('✓ Manifesto salvato');
    } else {
      modale({ titolo: 'Salvataggio non riuscito', testo: data.error || 'Riprova.' });
    }
  } catch (e) {
    modale({ titolo: 'Errore di connessione', testo: 'Riprova fra un momento.' });
  } finally {
    btn.textContent = testoOriginale; btn.disabled = false;
  }
}

function toastMsg(text) {
  var msg = document.createElement('div');
  msg.textContent = text;
  msg.style.cssText = 'position:fixed;top:70px;right:20px;background:#3a7a5a;color:#fff;padding:.75rem 1.25rem;border-radius:8px;font-size:.85rem;z-index:9999;box-shadow:0 4px 15px rgba(0,0,0,.3)';
  document.body.appendChild(msg);
  setTimeout(() => msg.remove(), 4000);
}

// ── TEMPLATE SALVATI (unico sistema, sul modello del Ricordino Designer) ──
// Template attualmente in lavorazione (applicato o appena salvato): è quello
// che "Salva come template" propone di aggiornare.
let templateCorrente = null;

/**
 * Stato del canvas ripulito per l'uso come template: i blocchi personali
 * (nome, date, frase, età) tornano al segnaposto e la foto del defunto viene
 * esclusa. Un eventuale QR non viene toccato: resta quello di chi l'ha creato,
 * chi applica il template lo sostituisce col proprio bottone "QR Necrologio".
 */
function canvasTemplateJSON(c) {
  const data = JSON.parse(JSON.stringify(c.toJSON(['customType', 'customBlockType'])));
  data.objects = (data.objects || []).filter(o => o.customType !== 'photo');
  data.objects.forEach(o => {
    if (BLOCCHI_PERSONALI.indexOf(o.customBlockType) !== -1) {
      o.text = testoPersonale(o.customBlockType, null);
      o.styles = {};
    }
  });
  return data;
}

/** Anteprima del template: renderizzata dal JSON ripulito, non dal canvas a schermo. */
function anteprimaTemplate(json, cb, formato) {
  const f = FORMATI[formato] || { wpx: canvas.getWidth(), hpx: canvas.getHeight() };
  const tmp = new fabric.StaticCanvas(null, { width: f.wpx, height: f.hpx });
  tmp.loadFromJSON(json, function() {
    if (!tmp.backgroundColor) tmp.backgroundColor = '#ffffff';
    tmp.renderAll();
    let thumb = null;
    try { thumb = tmp.toDataURL({format:'jpeg', quality:0.5, multiplier:0.25}); } catch (e) {}
    tmp.dispose();
    cb(thumb);
  });
}

function loadSavedTemplates() {
  fetch('/admin/api/manifesto-templates').then(r=>r.json()).then(templates => {
    const container = document.getElementById('saved-templates-list');
    if (!templates.length) {
      container.innerHTML='<div style="color:var(--gray);font-size:.75rem;font-style:italic;padding:.4rem">Nessun template</div>';
      return;
    }
    // Predefiniti/Globali (agenzia_id nullo lato server) in cima, poi quelli
    // dell'agenzia corrente.
    const predefiniti = templates.filter(t => t.globale);
    const miei        = templates.filter(t => !t.globale);
    container.innerHTML = (predefiniti.length ? gruppoTemplate('Predefiniti / Globali', predefiniti) : '')
                        + (miei.length ? gruppoTemplate('I miei', miei) : '');

    templates.filter(t => !t.anteprima).forEach(t => {
      anteprimaTemplate(t.fronte, function(dataUrl) {
        const box = container.querySelector('[data-anteprima="' + t.id + '"]');
        if (box && dataUrl) box.style.backgroundImage = "url('" + dataUrl + "')";
      }, t.formato);
    });
  });
}

function gruppoTemplate(titolo, lista) {
  return '<div style="font-size:.58rem;letter-spacing:.12em;text-transform:uppercase;color:var(--gray);padding:.3rem .1rem .25rem">' + titolo + '</div>'
    + lista.map(t => {
      const inLavorazione = !!templateCorrente && templateCorrente.id === t.id;
      return `
      <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.35rem;padding:.35rem;border:1px solid ${inLavorazione?'var(--gold)':'var(--border)'};border-radius:5px;background:${inLavorazione?'rgba(200,169,110,.14)':'var(--cream)'}">
        <div data-anteprima="${t.id}" style="width:34px;height:44px;border-radius:3px;flex-shrink:0;border:1px solid var(--border);background-color:#fff;background-size:cover;background-position:center${t.anteprima?`;background-image:url('${t.anteprima}')`:''}"></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:.73rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${_lyEsc(t.nome)}</div>
          <div style="font-size:.62rem;color:var(--gray)">${t.formato || ''}${inLavorazione ? ' · in lavorazione' : ''}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:2px">
          <button title="Applica al manifesto" onclick="loadSavedTemplate(${t.id})" style="font-size:.62rem;padding:2px 4px;border:1px solid var(--border);border-radius:3px;background:var(--ink);color:#fff;cursor:pointer">↓</button>
          ${t.editabile ? `<button title="Elimina template" onclick="deleteSavedTemplate(${t.id}, ${_lyEsc(JSON.stringify(t.nome))})" style="font-size:.62rem;padding:2px 4px;border:1px solid var(--border);border-radius:3px;background:var(--red);color:#fff;cursor:pointer">✕</button>` : ''}
        </div>
      </div>`;
    }).join('');
}

async function saveAsTemplate() {
  const fronte = canvasTemplateJSON(canvas);

  if (!fronte.objects.length) {
    await modale({ titolo: 'Niente da salvare',
      testo: 'Il manifesto è vuoto: aggiungi almeno un blocco prima di creare un template.' });
    return;
  }

  const modificabile = !!templateCorrente && templateCorrente.editabile;

  const azioni = [{ testo: 'Annulla', valore: null, tipo: 'neutro' }];
  if (modificabile) azioni.push({ testo: 'Salva come nuovo', valore: 'nuovo', tipo: 'neutro' });
  azioni.push(modificabile
    ? { testo: 'Aggiorna template', valore: 'aggiorna', tipo: 'primario' }
    : { testo: 'Salva template',    valore: 'nuovo',    tipo: 'primario' });

  let testo, nomeIniziale;
  if (modificabile) {
    testo = 'Stai lavorando sul template <strong>' + _lyEsc(templateCorrente.nome) + '</strong>: puoi aggiornarlo con le modifiche fatte oppure salvarne una copia nuova.';
    nomeIniziale = templateCorrente.nome;
  } else if (templateCorrente) {
    testo = 'Questo template non si sovrascrive da qui: le tue modifiche diventano un template nuovo, tutto tuo.';
    nomeIniziale = templateCorrente.nome + ' (copia)';
  } else {
    testo = 'Viene salvata solo l\'impaginazione: nome, date, frase ed età tornano segnaposto e la foto del defunto non viene inclusa. Un eventuale QR resta quello che hai inserito tu.';
    nomeIniziale = 'Manifesto ' + new Date().toLocaleDateString('it-IT');
  }

  const scelta = await modale({
    titolo: modificabile ? 'Salva template' : 'Nuovo template',
    testo:  testo,
    campo:  { etichetta: 'Nome del template', valore: nomeIniziale },
    azioni: azioni,
  });
  if (!scelta.azione) return;

  if (!scelta.valore) {
    await modale({ titolo: 'Manca il nome', testo: 'Dai un nome al template, così lo ritrovi nell\'elenco.' });
    return;
  }

  const aggiorna = scelta.azione === 'aggiorna';
  const url = '/admin/api/manifesto-templates' + (aggiorna ? '/' + templateCorrente.id : '');

  anteprimaTemplate(fronte, function(thumbnail) {
    const body = { nome: scelta.valore, fronte: JSON.stringify(fronte), anteprima: thumbnail };
    if (!aggiorna) body.formato = currentFormat;
    fetch(url, {
      method: aggiorna ? 'PUT' : 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        modale({ titolo: 'Salvataggio non riuscito', testo: _lyEsc(res.error || 'Riprova fra un momento.') });
        return;
      }
      templateCorrente = { id: res.id, nome: scelta.valore, editabile: true };
      const sez = document.getElementById('acc-template');
      if (sez) sez.open = true;
      loadSavedTemplates();
    })
    .catch(() => modale({ titolo: 'Salvataggio non riuscito', testo: 'Non è stato possibile contattare il server.' }));
  }, currentFormat);
}

/** Applica un template al manifesto corrente, riempiendolo coi dati del defunto. */
async function loadSavedTemplate(id) {
  const templates = await fetch('/admin/api/manifesto-templates').then(r => r.json());
  const t = templates.filter(function(x) { return x.id == id; })[0];
  if (!t) return;

  if (canvas.getObjects().length) {
    const conferma = await modale({
      titolo: 'Applicare il template?',
      testo:  '<strong>' + _lyEsc(t.nome) + '</strong>' + (t.formato ? ' · ' + t.formato : '') + '<br>'
            + 'Il contenuto attuale del manifesto verrà sostituito. I dati del defunto vengono riempiti in automatico.',
      azioni: [
        { testo: 'Annulla', valore: null, tipo: 'neutro' },
        { testo: 'Applica', valore: 'ok', tipo: 'primario' },
      ],
    });
    if (!conferma.azione) return;
  }

  const fmt = t.formato || currentFormat;
  const f = FORMATI[fmt] || FORMATI[currentFormat];
  canvas.setWidth(f.wpx);
  canvas.setHeight(f.hpx);
  document.getElementById('formato-select').value = fmt;
  currentFormat = fmt;

  canvas.loadFromJSON(t.fronte, function() {
    riempiConDefunto();
    canvas.renderAll();
    autoZoom();
    refreshLayers();
  });

  templateCorrente = { id: t.id, nome: t.nome, editabile: !!t.editabile };
  loadSavedTemplates();
}

/** Rimette i dati del defunto corrente nei blocchi personali (segnaposto del template). */
function riempiConDefunto() {
  canvas.getObjects().forEach(function(o) {
    if ((o.type === 'textbox' || o.type === 'text') && !o.styles) o.styles = {};
    if (BLOCCHI_PERSONALI.indexOf(o.customBlockType) !== -1) {
      o.set('text', testoPersonale(o.customBlockType, praticaData));
    }
  });
  canvas.renderAll();
}

async function deleteSavedTemplate(id, nome) {
  const conferma = await modale({
    titolo: 'Eliminare il template?',
    testo:  '<strong>' + _lyEsc(nome || '') + '</strong> verrà rimosso dall\'elenco.',
    azioni: [
      { testo: 'Annulla',  valore: null,     tipo: 'neutro' },
      { testo: 'Elimina',  valore: 'ok',     tipo: 'pericolo' },
    ],
  });
  if (!conferma.azione) return;

  fetch('/admin/api/manifesto-templates/'+id, { method: 'DELETE' })
    .then(r=>r.json()).then(res => {
      if (!res.success) { modale({ titolo: 'Eliminazione non riuscita', testo: _lyEsc(res.error || 'Riprova fra un momento.') }); return; }
      if (templateCorrente && templateCorrente.id === id) templateCorrente = null;
      loadSavedTemplates();
    });
}

// ── PANNELLO LIVELLI ──
function _lyEsc(s){ return String(s).replace(/[&<>"]/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch]; }); }

function layerLabel(o, idx) {
  if (o.customType === 'divisore') return { icon: '─', name: 'Divisore' };
  if (o.type === 'image') return { icon: '\u{1F5BC}', name: 'Immagine' };
  if (o.type === 'textbox' || o.type === 'i-text' || o.type === 'text') {
    var t = (o.text || '').replace(/\s+/g, ' ').trim();
    return { icon: 'T', name: t ? (t.length > 22 ? t.slice(0,22) + '…' : t) : 'Testo vuoto' };
  }
  if (o.type === 'line') return { icon: '╱', name: 'Linea' };
  if (o.type === 'rect') return { icon: '□', name: 'Rettangolo' };
  return { icon: '◆', name: (o.type || 'Oggetto') + ' ' + (idx+1) };
}
function refreshLayers() {
  var box = document.getElementById('layers-list');
  if (!box) return;
  var objs = window.canvas.getObjects().filter(function(o){ return o.customType !== 'background'; });
  if (!objs.length) { box.innerHTML = '<div class="layers-empty">Nessun elemento nel manifesto</div>'; return; }
  var active = window.canvas.getActiveObjects();
  var html = '';
  for (var i = objs.length - 1; i >= 0; i--) {
    var o = objs[i];
    var lab = layerLabel(o, i);
    var isSel = active.indexOf(o) !== -1;
    var oid = o.__layerId || (o.__layerId = 'ly_' + Math.random().toString(36).slice(2,9));
    html += '<div class="layer-row' + (isSel ? ' active' : '') + '" data-oid="' + oid + '" onclick="onLayerRowClick(event,\'' + oid + '\')">'
         +    '<input type="checkbox" ' + (isSel ? 'checked' : '') + ' onclick="event.stopPropagation();onLayerCheck(\'' + oid + '\',this.checked)">'
         +    '<span class="layer-icon">' + lab.icon + '</span>'
         +    '<span class="layer-name" title="' + lab.name.replace(/"/g,'&quot;') + '">' + lab.name + '</span>'
         +  '</div>';
  }
  box.innerHTML = html;
}
function findLayerObj(oid) {
  return window.canvas.getObjects().filter(function(o){ return o.__layerId === oid; })[0] || null;
}
function applyLayerSelection(objs) {
  window.canvas.discardActiveObject();
  if (objs.length === 1) { window.canvas.setActiveObject(objs[0]); }
  else if (objs.length > 1) { var sel = new fabric.ActiveSelection(objs, { canvas: window.canvas }); window.canvas.setActiveObject(sel); }
  window.canvas.requestRenderAll();
  refreshLayers();
}
function currentCheckedObjs() {
  var rows = document.querySelectorAll('#layers-list .layer-row');
  var objs = [];
  rows.forEach(function(r){
    var cb = r.querySelector('input[type=checkbox]');
    if (cb && cb.checked) { var o = findLayerObj(r.getAttribute('data-oid')); if (o) objs.push(o); }
  });
  return objs;
}
function onLayerCheck(oid, checked) { applyLayerSelection(currentCheckedObjs()); }
function onLayerRowClick(ev, oid) { var o = findLayerObj(oid); if (o) applyLayerSelection([o]); }
function selectAllLayers() {
  var objs = window.canvas.getObjects().filter(function(o){ return o.customType !== 'background'; });
  applyLayerSelection(objs);
}
function deselectAllLayers() { window.canvas.discardActiveObject(); window.canvas.requestRenderAll(); refreshLayers(); }

// ── UNDO / REDO ──
var undoStack = [];
var redoStack = [];
var isUndoRedo = false;
var undoTimer = null;

function saveState() {
  if (isUndoRedo) return;
  clearTimeout(undoTimer);
  undoTimer = setTimeout(function() {
    var state = JSON.stringify(window.canvas.toJSON(['id','selectable','evented','excludeFromExport']));
    if (undoStack.length === 0 || undoStack[undoStack.length-1] !== state) {
      undoStack.push(state);
      if (undoStack.length > 25) undoStack.shift();
      redoStack = [];
      updateUndoRedoBtns();
    }
  }, 300);
}

function undoAction() {
  if (undoStack.length <= 1) return;
  isUndoRedo = true;
  redoStack.push(undoStack.pop());
  var state = undoStack[undoStack.length - 1];
  window.canvas.loadFromJSON(JSON.parse(state), function() {
    window.canvas.renderAll();
    refreshLayers();
    setTimeout(function(){ isUndoRedo = false; updateUndoRedoBtns(); }, 100);
  });
}

function redoAction() {
  if (!redoStack.length) return;
  isUndoRedo = true;
  var state = redoStack.pop();
  undoStack.push(state);
  window.canvas.loadFromJSON(JSON.parse(state), function() {
    window.canvas.renderAll();
    refreshLayers();
    setTimeout(function(){ isUndoRedo = false; updateUndoRedoBtns(); }, 100);
  });
}

function updateUndoRedoBtns() {
  var u = document.getElementById('btn-undo');
  var r = document.getElementById('btn-redo');
  if (u) { u.disabled = undoStack.length <= 1; u.style.opacity = undoStack.length <= 1 ? '.4' : '1'; }
  if (r) { r.disabled = redoStack.length === 0; r.style.opacity = redoStack.length === 0 ? '.4' : '1'; }
}

// ── LINEE GUIDA ──
var guideLines = [];
var guideVisible = false;

function toggleGuide() {
  var btn = document.getElementById('btn-guide');
  if (guideVisible) {
    guideLines.forEach(function(l){ window.canvas.remove(l); });
    guideLines = []; guideVisible = false;
    if (btn) { btn.style.borderColor=''; btn.style.color=''; btn.style.background=''; }
  } else {
    var w = window.canvas.width, h = window.canvas.height;
    var defs = [
      [0, h/2, w, h/2, 'rgba(0,150,255,.6)', [5,5]],
      [w/2, 0, w/2, h, 'rgba(0,150,255,.6)', [5,5]],
      [0, h/3, w, h/3, 'rgba(255,80,80,.4)', [3,7]],
      [0, h*2/3, w, h*2/3, 'rgba(255,80,80,.4)', [3,7]],
      [w/3, 0, w/3, h, 'rgba(255,80,80,.4)', [3,7]],
      [w*2/3, 0, w*2/3, h, 'rgba(255,80,80,.4)', [3,7]],
    ];
    defs.forEach(function(d){
      var l = new fabric.Line([d[0],d[1],d[2],d[3]], {stroke:d[4],strokeWidth:1,strokeDashArray:d[5],selectable:false,evented:false,excludeFromExport:true});
      window.canvas.add(l); l.bringToFront(); guideLines.push(l);
    });
    var margin = new fabric.Rect({left:20,top:20,width:w-40,height:h-40,fill:'transparent',stroke:'rgba(200,169,110,.35)',strokeWidth:1,strokeDashArray:[4,8],selectable:false,evented:false,excludeFromExport:true});
    window.canvas.add(margin); margin.bringToFront(); guideLines.push(margin);
    guideVisible = true;
    if (btn) { btn.style.borderColor='#c8a96e'; btn.style.color='#c8a96e'; btn.style.background='rgba(200,169,110,.15)'; }
  }
  window.canvas.renderAll();
}

// Shortcut tastiera Ctrl+Z / Ctrl+Y e Canc/Backspace per eliminare la selezione
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undoAction(); }
  if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); redoAction(); }

  if (e.key === 'Delete' || e.key === 'Backspace') {
    var c = window.canvas;
    if (!c) return;
    var active = c.getActiveObject();
    if (!active) return;
    if (active.isEditing) return;
    var t = e.target;
    var isUiField = (t && (t.tagName === 'INPUT' || (t.tagName === 'TEXTAREA' && !t.classList.contains('canvas-textarea') && t.style.position !== 'absolute') || t.isContentEditable));
    if (isUiField) return;
    e.preventDefault();
    if (active.type === 'activeSelection') {
      active.forEachObject(function(o) { c.remove(o); });
    } else {
      c.remove(active);
    }
    c.discardActiveObject();
    if (typeof clearPropsPanel === 'function') clearPropsPanel();
    c.requestRenderAll();
    if (typeof saveState === 'function') saveState();
  }
});

// ── MODALE (Promise-based, azioni: [{testo,valore,tipo:'primario'|'neutro'|'pericolo'}]) ──
let _modaleChiudi = null;
function modale(opzioni) {
  const box    = document.getElementById('app-modal');
  const input  = document.getElementById('app-modal-input');
  const campo  = document.getElementById('app-modal-field');
  const azioni = opzioni.azioni || [{ testo: 'Ho capito', valore: 'ok', tipo: 'primario' }];

  document.getElementById('app-modal-title').textContent = opzioni.titolo || '';
  document.getElementById('app-modal-text').innerHTML = opzioni.testo || '';

  if (opzioni.campo) {
    campo.style.display = 'block';
    document.getElementById('app-modal-label').textContent = opzioni.campo.etichetta || 'Nome';
    input.value = opzioni.campo.valore || '';
  } else {
    campo.style.display = 'none';
    input.value = '';
  }

  const stili = {
    primario: 'background:#c8a96e;color:#fff;border:1px solid #c8a96e',
    neutro:   'background:#f5f0e8;color:#1a1a2e;border:1px solid #ddd8d0',
    pericolo: 'background:#c44b3a;color:#fff;border:1px solid #c44b3a',
  };
  const cont = document.getElementById('app-modal-actions');
  cont.innerHTML = '';
  azioni.forEach(function(a) {
    const b = document.createElement('button');
    b.textContent = a.testo;
    b.style.cssText = "padding:.45rem .95rem;border-radius:6px;font-size:.8rem;cursor:pointer;font-family:'DM Sans',sans-serif;" + (stili[a.tipo] || stili.neutro);
    b.onclick = function() { chiudiModale(a.valore); };
    cont.appendChild(b);
  });

  box.style.display = 'flex';
  box.onclick = function(e) { if (e.target === box) chiudiModale(null); };
  if (opzioni.campo) setTimeout(function() { input.focus(); input.select(); }, 30);

  return new Promise(function(resolve) {
    function tasti(e) {
      if (e.key === 'Escape') { e.preventDefault(); chiudiModale(null); }
      if (e.key === 'Enter') {
        const primaria = azioni.filter(function(a) { return a.tipo === 'primario'; })[0];
        if (primaria) { e.preventDefault(); chiudiModale(primaria.valore); }
      }
    }
    _modaleChiudi = function(valore) {
      document.removeEventListener('keydown', tasti);
      box.style.display = 'none';
      _modaleChiudi = null;
      resolve({ azione: valore, valore: input.value.trim() });
    };
    document.addEventListener('keydown', tasti);
  });
}
function chiudiModale(valore) { if (_modaleChiudi) _modaleChiudi(valore); }
</script>
</body>
</html>
