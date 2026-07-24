<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="google" content="notranslate">
<title>Designer Ricordini | MemorAI</title>
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
<script src="/vendor/libs/fabric.min.js"></script>
<style>
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Regular.ttf') format('truetype'); font-weight:normal; font-style:normal; font-display:swap; }
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Regular-Italic.ttf') format('truetype'); font-weight:normal; font-style:italic; font-display:swap; }
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Bold.ttf') format('truetype'); font-weight:bold; font-style:normal; font-display:swap; }
@font-face { font-family:'Monotype Corsiva'; src:url('/fonts/Monotype-Corsiva-Bold-Italic.ttf') format('truetype'); font-weight:bold; font-style:italic; font-display:swap; }
:root{
  --ink:#1a1a2e;--gold:#c8a96e;--cream:#f5f0e8;--cream-dark:#ede6d8;
  --white:#fdfaf5;--gray:#8a7f72;--border:#ddd8d0;--green:#3a7a5a;--red:#c44b3a;--blue:#0f3460;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);height:100vh;overflow:hidden;display:flex;flex-direction:column}
nav{background:var(--ink);padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;flex-shrink:0;z-index:100}
.logo{color:#fff;font-size:1rem;font-weight:600;font-family:'Cormorant Garamond',serif}
.nav-links{display:flex;align-items:center;gap:.5rem}
.nav-btn{padding:.4rem .9rem;border-radius:6px;font-size:.8rem;font-weight:500;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none}
.btn-gold{background:var(--gold);color:#fff}
.btn-ghost{background:rgba(255,255,255,.1);color:#fff}
.btn-green{background:var(--green);color:#fff}
.btn-blue{background:var(--blue);color:#fff}

/* LAYOUT */
.designer-layout{display:flex;flex:1;overflow:hidden}
.sidebar{width:260px;background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0}
.canvas-area{flex:1;display:flex;flex-direction:column;overflow:hidden;background:#2a2a3a}
.props-panel{width:240px;background:var(--white);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0}

/* TOOLBAR */
.toolbar{background:#1e1e2e;border-bottom:1px solid rgba(255,255,255,.1);padding:.35rem 1rem;display:flex;align-items:center;gap:.4rem;flex-shrink:0;flex-wrap:wrap}
.tool-btn{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.15);border-radius:4px;padding:.22rem .5rem;font-size:.7rem;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap}
.tool-btn:hover{background:rgba(200,169,110,.3);border-color:var(--gold)}
.tool-sep{width:1px;height:18px;background:rgba(255,255,255,.15);margin:0 .2rem}

/* CANVAS WRAPPER */
.canvases-row{display:flex;gap:3rem;align-items:flex-start;justify-content:center;flex:1;padding:1.5rem;overflow:auto}
.canvas-side{display:flex;flex-direction:column;align-items:center;gap:.5rem;flex-shrink:0}
.canvas-label{color:rgba(255,255,255,.6);font-size:.75rem;letter-spacing:.1em;text-transform:uppercase}
/* L'ombra va sul .canvas-outer (dimensione reale del foglio): l'ombra propria di
   un elemento NON è tagliata dal suo overflow:hidden, mentre quella del wrapper
   figlio veniva ritagliata dal genitore. Sfondo bianco = foglio sempre visibile. */
.canvas-outer{box-shadow:0 12px 40px rgba(0,0,0,.55);background:#fff;border-radius:2px}
.canvas-wrapper{position:relative;transform-origin:top center}

/* SIDEBAR */
.panel-title{font-size:.63rem;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);padding:.6rem 1rem .4rem;border-bottom:1px solid var(--border);font-weight:500}
.block-btn{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:5px;margin-bottom:.35rem;cursor:pointer;font-size:.78rem;text-align:left;background:var(--cream);color:var(--ink);font-family:'DM Sans',sans-serif;display:flex;align-items:center;gap:.45rem;transition:all .15s}
.block-btn:hover{border-color:var(--gold);background:rgba(200,169,110,.05)}
.block-icon{font-size:.9rem;width:18px;text-align:center}
.blocks-list{padding:.4rem}
.upload-area{padding:.6rem;border-top:1px solid var(--border)}
.upload-btn{width:100%;padding:.5rem;border:2px dashed var(--border);border-radius:6px;background:none;color:var(--gray);font-size:.76rem;cursor:pointer;font-family:'DM Sans',sans-serif;text-align:center;transition:all .2s}
.upload-btn:hover{border-color:var(--gold);color:var(--gold)}

/* PROPS */
.prop-group{padding:.6rem .85rem;border-bottom:1px solid var(--border)}
.prop-label{font-size:.68rem;font-weight:500;color:var(--gray);display:block;margin-bottom:.3rem}
.prop-input{width:100%;padding:.35rem .55rem;border:1px solid var(--border);border-radius:4px;font-size:.8rem;font-family:'DM Sans',sans-serif;background:var(--cream)}
.prop-input:focus{outline:none;border-color:var(--gold)}
.prop-row{display:flex;gap:.4rem}
.prop-row .prop-input{flex:1}
.color-row{display:flex;align-items:center;gap:.4rem}
.color-swatch{width:26px;height:26px;border-radius:4px;border:1px solid var(--border);cursor:pointer;flex-shrink:0}
.font-select{width:100%;padding:.35rem .55rem;border:1px solid var(--border);border-radius:4px;font-size:.78rem;font-family:'DM Sans',sans-serif;background:var(--cream)}
.btn-group-row{display:flex;gap:.35rem;margin-top:.35rem;flex-wrap:wrap}
.btn-sm{padding:.3rem .55rem;border-radius:4px;font-size:.72rem;cursor:pointer;border:1px solid var(--border);background:var(--cream);font-family:'DM Sans',sans-serif}
.btn-sm.active{background:var(--ink);color:#fff;border-color:var(--ink)}
.btn-danger{background:var(--red);color:#fff;border:none;width:100%;padding:.45rem;border-radius:5px;font-size:.78rem;cursor:pointer;margin-top:.4rem;font-family:'DM Sans',sans-serif}
.no-selection{padding:1.2rem;text-align:center;color:var(--gray);font-size:.78rem;font-style:italic}
.simbolo-btn{width:100%;aspect-ratio:1;border:1px solid var(--border);border-radius:6px;background:var(--cream);cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;transition:all .15s}
.simbolo-btn:hover{border-color:var(--gold);background:rgba(200,169,110,.1)}
.wbtn{padding:.6rem .8rem;border:1px solid var(--border);border-radius:8px;background:var(--cream);color:var(--ink);font-size:.8rem;cursor:pointer;text-align:left;transition:all .15s;width:100%}
.wbtn:hover{border-color:var(--gold);background:rgba(200,169,110,.12)}

/* LATO ATTIVO */
.canvas-wrapper.active-side{outline:3px solid var(--gold);outline-offset:3px;border-radius:2px}
</style>
</head>
<body>

<nav>
  <div class="logo">MemorAI — Designer Ricordini</div>
  <div style="display:flex;align-items:center;gap:.5rem">
    <span style="color:rgba(255,255,255,.5);font-size:.72rem">Formato:</span>
    <select id="formato-select" onchange="cambiaFormato(this.value)" style="background:#fff;color:#1a1a2e;border:1px solid rgba(255,255,255,.3);border-radius:5px;padding:.28rem .55rem;font-size:.76rem;cursor:pointer">
      <option value="7x10">7x10 cm (standard)</option>
      <option value="6x9">6x9 cm (piccolo)</option>
    </select>
  </div>
  <div class="nav-links">
    @if(isset($pratica))
    <span style="color:rgba(255,255,255,.5);font-size:.78rem">{{ $pratica->defunto->last_name ?? '' }} {{ $pratica->defunto->first_name ?? '' }}</span>
    @endif
    <button class="nav-btn btn-ghost" onclick="clearCanvas()">🗑 Pulisci</button>
    <button class="nav-btn btn-gold" onclick="exportPNG()" style="font-size:.75rem">📥 PNG</button>
        <button onclick="apriImpaginatorePDF()" style="background:rgba(200,169,110,.15);color:#c8a96e;border:1px solid rgba(200,169,110,.3);padding:.4rem .75rem;border-radius:6px;font-size:.75rem;cursor:pointer;font-family:Inter,sans-serif">📄 Stampa PDF</button>
    {{-- Salva Template, Necrologio e Invia-approvazione: flussi B2B → FASE 2 --}}
    <button class="nav-btn btn-green" onclick="saveToPratica()">💾 Salva</button>
    <a href="/studio/foto" class="nav-btn btn-ghost">← Foto Manager</a>
  </div>
</nav>

{{-- BANNER CONSENSO GDPR DEL DEFUNTO --}}
<div id="gdpr-banner" style="padding:.5rem 1rem;font-family:'DM Sans',sans-serif;font-size:.82rem;display:flex;align-items:center;gap:.75rem"></div>

{{-- MODALE AUTORIZZAZIONE GDPR --}}
<div id="gdpr-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9998;align-items:center;justify-content:center;padding:1rem">
  <div style="background:#fff;border-radius:14px;max-width:460px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden">
    <div style="background:#1a1a2e;padding:1rem 1.5rem">
      <div style="color:#c8a96e;font-family:monospace;font-size:.75rem;letter-spacing:.12em">🔒 CONSENSO GDPR</div>
    </div>
    <div style="padding:1.5rem">
      <div style="font-size:.95rem;font-weight:600;color:#1a1a2e;margin-bottom:.4rem">Autorizzazione all'uso di immagine e dati di <span id="gdpr-nome-defunto" style="color:#a5863f"></span></div>
      <div style="font-size:.8rem;color:#8a7f72;margin-bottom:1.1rem">Chi autorizza dichiara di avere titolo per acconsentire al trattamento dei dati e dell'immagine del defunto per la realizzazione del ricordino.</div>
      <label style="font-size:.75rem;font-weight:600;color:#8a7f72;display:block;margin-bottom:.25rem">Nome di chi autorizza *</label>
      <input id="gdpr-autorizzato-da" type="text" placeholder="es. Giulia Rossi" style="width:100%;border:1px solid #e0dbd4;border-radius:6px;padding:.6rem .85rem;font-size:.9rem;font-family:inherit;margin-bottom:.85rem">
      <label style="font-size:.75rem;font-weight:600;color:#8a7f72;display:block;margin-bottom:.25rem">Parentela / relazione col defunto</label>
      <input id="gdpr-parentela" type="text" placeholder="es. figlia" style="width:100%;border:1px solid #e0dbd4;border-radius:6px;padding:.6rem .85rem;font-size:.9rem;font-family:inherit;margin-bottom:.85rem">
      <label style="font-size:.75rem;font-weight:600;color:#8a7f72;display:block;margin-bottom:.25rem">Note (facoltative)</label>
      <textarea id="gdpr-note" rows="2" style="width:100%;border:1px solid #e0dbd4;border-radius:6px;padding:.6rem .85rem;font-size:.9rem;font-family:inherit;margin-bottom:.25rem"></textarea>
      <div id="gdpr-error" style="color:#b23b3b;font-size:.78rem;min-height:1rem;margin-bottom:.5rem"></div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end">
        <button onclick="document.getElementById('gdpr-modal').style.display='none'" style="background:#f5f0eb;border:1px solid #e0dbd4;color:#1a1a2e;padding:.6rem 1.25rem;border-radius:7px;font-size:.85rem;cursor:pointer;font-family:inherit">Annulla</button>
        <button id="gdpr-submit" onclick="salvaConsensoGdpr()" style="background:#1a1a2e;color:#c8a96e;border:none;padding:.6rem 1.5rem;border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;font-family:inherit">✓ Registra consenso</button>
      </div>
    </div>
  </div>
</div>

<script>
function renderGdprBanner() {
  var b = document.getElementById('gdpr-banner');
  if (gdprData.consenso) {
    b.style.background = 'rgba(58,122,90,.12)';
    b.style.borderBottom = '1px solid rgba(58,122,90,.35)';
    b.innerHTML = '<span style="color:#2f6b4f">✓ Consenso GDPR registrato per <strong>' + (gdprData.defunto || '') + '</strong>'
      + (gdprData.autorizzato_da ? ' — da ' + gdprData.autorizzato_da + (gdprData.parentela ? ' (' + gdprData.parentela + ')' : '') : '')
      + (gdprData.autorizzato_at ? ' · ' + gdprData.autorizzato_at : '') + '</span>';
  } else {
    b.style.background = 'rgba(178,59,59,.1)';
    b.style.borderBottom = '1px solid rgba(178,59,59,.3)';
    b.innerHTML = '<span style="color:#b23b3b;flex:1">⚠️ Consenso GDPR non registrato per <strong>' + (gdprData.defunto || 'il defunto') + '</strong></span>'
      + '<button onclick="apriModaleGdpr()" style="background:#b23b3b;color:#fff;border:none;border-radius:6px;padding:.4rem .9rem;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit">Autorizza</button>';
  }
}
function apriModaleGdpr() {
  document.getElementById('gdpr-nome-defunto').textContent = gdprData.defunto || '';
  document.getElementById('gdpr-error').textContent = '';
  document.getElementById('gdpr-modal').style.display = 'flex';
}
function salvaConsensoGdpr() {
  var nome = document.getElementById('gdpr-autorizzato-da').value.trim();
  var err = document.getElementById('gdpr-error');
  if (!nome) { err.textContent = 'Il nome di chi autorizza è obbligatorio.'; return; }
  var btn = document.getElementById('gdpr-submit');
  btn.disabled = true; btn.textContent = '⏳...';
  fetch('/admin/api/defunto/' + praticaData_defuntoId + '/gdpr', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
    body: JSON.stringify({
      autorizzato_da: nome,
      parentela: document.getElementById('gdpr-parentela').value.trim(),
      note: document.getElementById('gdpr-note').value.trim()
    })
  }).then(r => r.json()).then(function(res){
    btn.disabled = false; btn.textContent = '✓ Registra consenso';
    if (res.success) {
      gdprData.consenso = true;
      gdprData.autorizzato_da = res.autorizzato_da;
      gdprData.parentela = res.parentela;
      gdprData.autorizzato_at = res.autorizzato_at;
      document.getElementById('gdpr-modal').style.display = 'none';
      renderGdprBanner();
    } else {
      err.textContent = res.error || 'Errore durante il salvataggio.';
    }
  }).catch(function(){ btn.disabled = false; btn.textContent = '✓ Registra consenso'; err.textContent = 'Errore di connessione.'; });
}
var praticaData_defuntoId = {{ $praticaId ?? 'null' }};
document.addEventListener('DOMContentLoaded', renderGdprBanner);
</script>

<div class="designer-layout">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="panel-title">Lato Attivo</div>
    <div style="display:flex;gap:.5rem;padding:.6rem">
      <button onclick="setActiveSide('fronte')" id="btn-fronte" style="flex:1;padding:.5rem;border-radius:6px;font-size:.8rem;cursor:pointer;border:2px solid var(--gold);background:var(--gold);color:#fff;font-family:'DM Sans',sans-serif">FRONTE</button>
      <button onclick="setActiveSide('retro')" id="btn-retro" style="flex:1;padding:.5rem;border-radius:6px;font-size:.8rem;cursor:pointer;border:2px solid var(--border);background:var(--cream);color:var(--ink);font-family:'DM Sans',sans-serif">RETRO</button>
    </div>

    <div class="panel-title">Sfondo</div>
    <div style="padding:.5rem">
      <button class="block-btn" onclick="setBg('white')"><span class="block-icon">⬜</span>Bianco</button>
      <button class="block-btn" onclick="setBg('cream')"><span class="block-icon">🟫</span>Avorio</button>
      <button class="block-btn" onclick="setBg('dark')"><span class="block-icon">⬛</span>Scuro</button>
      <input type="file" id="bg-upload" accept="image/*" style="display:none" onchange="uploadBackground(this)">
      <button class="block-btn" onclick="document.getElementById('bg-upload').click()"><span class="block-icon">🖼</span>Carica sfondo</button>
    </div>

    <div class="panel-title">Blocchi Testo</div>
    <div class="blocks-list">
      <button class="block-btn" onclick="addBlock('nome')"><span class="block-icon">👤</span>Nome Defunto</button>
      <button class="block-btn" onclick="addBlock('eta')"><span class="block-icon">🔢</span>Anni vissuti</button>
      <button class="block-btn" onclick="addBlock('date')"><span class="block-icon">📅</span>Date</button>
      <button class="block-btn" onclick="addBlock('frase')"><span class="block-icon">✨</span>Frase</button>
      <button class="block-btn" onclick="addBlock('preghiera')"><span class="block-icon">🙏</span>Preghiera</button>
      <button class="block-btn" onclick="addBlock('testo')"><span class="block-icon">📝</span>Testo libero</button>
      <button class="block-btn" onclick="addBlock('linea')"><span class="block-icon">➖</span>Linea decorativa</button>
    </div>

    <div class="panel-title">Immagini</div>
    <div class="blocks-list">
      <input type="file" id="foto-upload" accept="image/*" style="display:none" onchange="insertFoto(this)">
      <button class="block-btn" onclick="document.getElementById('foto-upload').click()"><span class="block-icon">📷</span>Carica Foto</button>
      <button class="block-btn" onclick="apriGalleriaFoto()" style="border-color:#7c3aed;background:rgba(124,58,237,.08)"><span class="block-icon">🖼</span><span style="color:#7c3aed;font-weight:500">Galleria Foto</span></button>

      <button class="block-btn" onclick="openSantoModal()"><span class="block-icon">📿</span>Immagine Santo</button>
      <button class="block-btn" onclick="addBlock('logo')"><span class="block-icon">🏢</span>Logo Agenzia</button>
    </div>

    <div class="panel-title">Simboli Religiosi</div>
    <div style="padding:.4rem">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.35rem;margin-bottom:.4rem" id="simboli-grid">
        <button class="simbolo-btn" onclick="insertSimbolo('croce_latina')" title="Croce Latina">✝</button>
        <button class="simbolo-btn" onclick="insertSimbolo('croce_greca')" title="Croce Greca">✚</button>
        <button class="simbolo-btn" onclick="insertSimbolo('croce_ortodossa')" title="Croce Ortodossa">☦</button>
        <button class="simbolo-btn" onclick="insertSimbolo('croce_raggi')" title="Croce Decorata">✛</button>
        <button class="simbolo-btn" onclick="insertSimbolo('stella')" title="Stella">✦</button>
        <button class="simbolo-btn" onclick="insertSimbolo('stella6')" title="Stella 6 punte">✡</button>
        <button class="simbolo-btn" onclick="insertSimbolo('omega')" title="Omega">Ω</button>
        <button class="simbolo-btn" onclick="insertSimbolo('chi_rho')" title="Chi-Rho">☧</button>
        <button class="simbolo-btn" onclick="insertSimbolo('separatore')" title="Separatore">✦</button>
        <button class="simbolo-btn" onclick="insertSimbolo('giglio')" title="Giglio">⚜</button>
        <button class="simbolo-btn" onclick="insertSimbolo('colomba')" title="Colomba">🕊</button>
        <button class="simbolo-btn" onclick="insertSimbolo('infinito')" title="Infinito">∞</button>
        <button class="simbolo-btn" onclick="insertSimbolo('gamma')" title="Gamma">Γ</button>
        <button class="simbolo-btn" onclick="insertSimbolo('alfa_omega')" title="Alfa e Omega">Αω</button>
      </div>
      <div style="font-size:.68rem;color:var(--gray);text-align:center;margin-bottom:.3rem">Colore dal pannello proprietà</div>
    </div>

    <div class="panel-title">I Miei Template</div>
    <div id="saved-templates-list" style="padding:.4rem">
      <div style="color:var(--gray);font-size:.75rem;font-style:italic;padding:.4rem">Caricamento...</div>
    </div>
  </div>

  <!-- CANVAS AREA -->
  <div class="canvas-area">
    <!-- BANNER FOTO PRINCIPALE -->
    @if(!empty($fotoElaborata))
    <div id="banner-foto-principale" style="background:rgba(200,169,110,.15);border-bottom:1px solid rgba(200,169,110,.3);padding:.4rem 1rem;display:flex;align-items:center;gap:.5rem;flex-shrink:0;z-index:10">
      <img src="{{ $fotoElaborata }}" style="width:28px;height:36px;object-fit:cover;border-radius:3px;border:1px solid #c8a96e;flex-shrink:0">
      <div style="flex:1;font-size:.72rem;color:rgba(255,255,255,.8)">★ Foto principale disponibile</div>
      <button onclick="usaFotoGalleria('{{ $fotoElaborata }}')" style="background:#c8a96e;color:#1a1a2e;border:none;border-radius:5px;padding:.3rem .7rem;font-size:.72rem;font-weight:600;cursor:pointer;white-space:nowrap">Usa sul canvas</button>
      <button onclick="apriGalleriaFoto()" style="background:rgba(124,58,237,.2);color:#a78bfa;border:1px solid rgba(124,58,237,.4);border-radius:5px;padding:.3rem .6rem;font-size:.72rem;cursor:pointer;white-space:nowrap">Galleria</button>
    </div>
    @endif
    <div class="toolbar">
      <span style="color:rgba(255,255,255,.4);font-size:.68rem">ALLINEA</span>
      <button class="tool-btn" onclick="alignObjects('left')">⬛⬜⬜ Sin</button>
      <button class="tool-btn" onclick="alignObjects('centerH')">⬜⬛⬜ Cen</button>
      <button class="tool-btn" onclick="alignObjects('right')">⬜⬜⬛ Des</button>
      <button class="tool-btn" onclick="alignObjects('top')">↑ Alto</button>
      <button class="tool-btn" onclick="alignObjects('centerV')">↕ Mez</button>
      <button class="tool-btn" onclick="alignObjects('bottom')">↓ Bas</button>
      <div class="tool-sep"></div>
      <button class="tool-btn" onclick="distributeObjects('h')">↔ Dist H</button>
      <button class="tool-btn" onclick="distributeObjects('v')">↕ Dist V</button>
      <div class="tool-sep"></div>
      <button class="tool-btn" onclick="bringForward()">▲ Avanti</button>
      <button class="tool-btn" onclick="sendBackward()">▼ Indietro</button>
      <div class="tool-sep"></div>
      <span style="color:rgba(255,255,255,.4);font-size:.68rem">ZOOM</span>
      <button class="tool-btn" onclick="setZoom(-0.1)">−</button>
      <span id="zoom-label" style="color:#fff;font-size:.72rem;min-width:35px;text-align:center">100%</span>
      <button class="tool-btn" onclick="setZoom(0.1)">+</button>
      <button class="tool-btn" onclick="resetZoom()">⌂</button>
    </div>

    <div class="canvases-row">
      <div class="canvas-side">
        <div class="canvas-label" id="label-fronte" style="color:var(--gold);font-weight:600;font-size:.8rem">● FRONTE</div>
        <div class="canvas-outer" id="outer-fronte" style="position:relative">
          <div class="canvas-wrapper" id="wrapper-fronte">
            <canvas id="canvas-fronte"></canvas>
          </div>
          <div id="border-fronte" style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;border:4px solid #c8a96e;box-sizing:border-box;z-index:10"></div>
        </div>
      </div>
      <div class="canvas-side">
        <div class="canvas-label" id="label-retro" style="color:rgba(255,255,255,.4);font-size:.8rem">○ RETRO</div>
        <div class="canvas-outer" id="outer-retro" style="position:relative">
          <div class="canvas-wrapper" id="wrapper-retro">
            <canvas id="canvas-retro"></canvas>
          </div>
          <div id="border-retro" style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;border:4px solid transparent;box-sizing:border-box;z-index:10"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- PANEL PROPRIETÀ -->
  <div class="props-panel">
    <div class="panel-title">Proprietà Elemento</div>
    <div class="no-selection" id="no-selection">Seleziona un elemento</div>
    <div id="props-content" style="display:none">
      <div class="prop-group">
        <span class="prop-label">Testo</span>
        <textarea class="prop-input" id="prop-text" rows="3" oninput="updateProp('text')" style="resize:vertical"></textarea>
      </div>
      <div class="prop-group">
        <span class="prop-label">Font</span>
        <select class="font-select" id="prop-font" onchange="updateProp('font')" style="font-size:.75rem">
          <optgroup label="Eleganti Serif">
            <option value="Cormorant Garamond">Cormorant Garamond</option>
            <option value="Playfair Display">Playfair Display</option>
            <option value="EB Garamond">EB Garamond</option>
            <option value="Libre Baskerville">Libre Baskerville</option>
            <option value="Lora">Lora</option>
            <option value="Crimson Text">Crimson Text</option>
            <option value="Merriweather">Merriweather</option>
            <option value="Spectral">Spectral</option>
            <option value="GFS Didot">GFS Didot</option>
            <option value="Philosopher">Philosopher</option>
          </optgroup>
          <optgroup label="Classici">
            <option value="Cinzel">Cinzel (Maiuscolo)</option>
            <option value="Georgia">Georgia</option>
            <option value="Times New Roman">Times New Roman</option>
          </optgroup>
          <optgroup label="Corsivi e Calligrafia">
            <option value="Pinyon Script">Pinyon Script</option>
            <option value="Dancing Script">Dancing Script</option>
            <option value="Monotype Corsiva">Monotype Corsiva</option>
          </optgroup>
          <optgroup label="Sans-serif">
            <option value="DM Sans">DM Sans</option>
            <option value="Arial">Arial</option>
          </optgroup>
        </select>
      </div>
      <div class="prop-group">
        <div style="margin-bottom:.5rem">
          <span class="prop-label">Dimensione carattere</span>
          <div style="display:flex;align-items:center;gap:.3rem;margin-top:.25rem">
            <button onclick="changeRicordinoSize(-1)" style="width:30px;height:30px;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer;font-size:1.1rem;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">−</button>
            <input type="number" class="prop-input" id="prop-size" value="12" min="6" max="200" oninput="updateProp('size')" style="text-align:center;width:60px">
            <button onclick="changeRicordinoSize(1)" style="width:30px;height:30px;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer;font-size:1.1rem;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">+</button>
          </div>
        </div>
        <div>
          <span class="prop-label">Interlinea</span>
          <div style="display:flex;align-items:center;gap:.3rem;margin-top:.25rem">
            <button onclick="changeRicordinoLineH(-0.1)" style="width:30px;height:30px;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer;font-size:1.1rem;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">−</button>
            <input type="number" class="prop-input" id="prop-lineheight" value="1.4" min="0.8" max="4" step="0.1" oninput="updateProp('lineHeight')" style="text-align:center;width:60px">
            <button onclick="changeRicordinoLineH(0.1)" style="width:30px;height:30px;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer;font-size:1.1rem;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">+</button>
          </div>
        </div>
      </div>
      <div class="prop-group">
        <div class="prop-row">
          <div><span class="prop-label">Colore</span>
            <div class="color-row">
              <input type="color" class="color-swatch" id="prop-color" value="#000000" oninput="updateProp('color')">
              <input type="text" class="prop-input" id="prop-color-hex" value="#000000" oninput="syncColor()" style="font-size:.72rem">
            </div>
          </div>
        </div>
      </div>
      <div class="prop-group">
        <span class="prop-label">Stile e Allineamento</span>
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
      <div class="prop-group" id="border-group" style="display:none">
        <span class="prop-label">Bordo / Traccia</span>
        <div class="prop-row" style="align-items:center;margin-bottom:.4rem">
          <div><span class="prop-label">Spessore</span><input type="number" class="prop-input" id="prop-stroke-width" value="0" min="0" max="20" oninput="updateBorder()"></div>
          <div><span class="prop-label">Colore</span>
            <div class="color-row">
              <input type="color" class="color-swatch" id="prop-stroke-color" value="#c8a96e" oninput="updateBorder()">
            </div>
          </div>
        </div>
        <div class="btn-group-row">
          <button class="btn-sm" onclick="setStrokePreset(0)">No</button>
          <button class="btn-sm" onclick="setStrokePreset(2)">Sottile</button>
          <button class="btn-sm" onclick="setStrokePreset(5)">Medio</button>
          <button class="btn-sm" onclick="setStrokePreset(8)">Spesso</button>
        </div>
      </div>
      <div class="prop-group">
        <button class="btn-danger" onclick="deleteSelected()">🗑 Elimina</button>
      </div>
    </div>
  </div>

</div>

<!-- MODAL SANTI -->
<div id="santo-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center">
  <div style="background:var(--white);border-radius:12px;padding:1.25rem;width:540px;max-width:95vw;max-height:85vh;overflow-y:auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
      <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem">Inserisci Immagine Santo</div>
      <button onclick="closeSantoModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--gray)">✕</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-bottom:.75rem" id="santi-gallery">
      <div style="text-align:center;color:var(--gray);font-size:.78rem;grid-column:1/-1;padding:.75rem">Nessun santo in galleria</div>
    </div>

    {{-- Anteprima + controlli opacità --}}
    <div id="santo-preview-box" style="display:none;border:1px solid var(--border);border-radius:8px;padding:.85rem;margin-bottom:.75rem;align-items:center;gap:1rem">
      <img id="santo-preview-img" style="width:60px;height:80px;object-fit:cover;border-radius:5px;border:2px solid var(--gold);flex-shrink:0">
      <div style="flex:1">
        <div style="font-size:.78rem;font-weight:500;color:var(--ink);margin-bottom:.5rem" id="santo-nome-sel">Santo selezionato</div>
        <div style="font-size:.75rem;color:var(--gray);margin-bottom:.4rem">Opacità (dissolvenza filigrana)</div>
        <div style="display:flex;align-items:center;gap:.5rem">
          <input type="range" id="santo-opacita" min="10" max="100" value="100" step="5"
                 oninput="updateSantoOpacitaLabel()"
                 style="flex:1;accent-color:var(--gold)">
          <span id="santo-opacita-val" style="font-size:.78rem;font-weight:500;color:var(--gold);min-width:35px">100%</span>
        </div>
        <div style="display:flex;gap:.5rem;margin-top:.35rem">
          <button onclick="document.getElementById('santo-opacita').value=20;updateSantoOpacitaLabel()" style="font-size:.68rem;padding:.2rem .5rem;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer">Filigrana 20%</button>
          <button onclick="document.getElementById('santo-opacita').value=40;updateSantoOpacitaLabel()" style="font-size:.68rem;padding:.2rem .5rem;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer">Semi 40%</button>
          <button onclick="document.getElementById('santo-opacita').value=100;updateSantoOpacitaLabel()" style="font-size:.68rem;padding:.2rem .5rem;border:1px solid var(--border);border-radius:4px;background:var(--cream);cursor:pointer">Piena 100%</button>
        </div>
      </div>
    </div>

    <div style="border-top:1px solid var(--border);padding-top:.75rem;display:flex;gap:.5rem">
      <input type="file" id="santo-upload" accept="image/*" style="display:none" onchange="uploadSanto(this)">
      <button onclick="document.getElementById('santo-upload').click()" style="flex:1;padding:.5rem;background:var(--cream);color:var(--ink);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.82rem">
        📁 Carica nuovo
      </button>
      <button onclick="inserisciSantoSelezionato()" style="flex:2;padding:.5rem;background:var(--gold);color:#fff;border:none;border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:500">
        ✓ Inserisci sul canvas
      </button>
    </div>
  </div>
</div>

<script>
const praticaData = @json($praticaData ?? []);
const agenziaData = @json($agenziaData ?? []);
const gdprData = @json($gdpr ?? ['consenso' => false]);
const studioToken = '{{ config('photoprint.studio_token') }}';
// Allega il token guard a tutte le chiamate /admin/api/ (Fase 1).
(function () {
    const _fetch = window.fetch;
    window.fetch = function (input, init) {
        const url = typeof input === 'string' ? input : (input && input.url) || '';
        if (url.indexOf('/admin/api/') !== -1) {
            init = init || {};
            init.headers = new Headers(init.headers || {});
            init.headers.set('X-Studio-Token', studioToken);
        }
        return _fetch.call(this, input, init);
    };
})();

// ── FORMATI (px a 96dpi) ──
// Dimensioni fisiche in px
const FORMATI_FISICI = {
  '7x10': { w: 265, h: 378 },
  '6x9':  { w: 227, h: 340 },
};
// Canvas interno 3x per nitidezza (come manifesto)
const SCALA = 3.5;
const FORMATI = {
  '7x10': { w: 265*SCALA, h: 378*SCALA },
  '6x9':  { w: 227*SCALA, h: 340*SCALA },
};

let currentFormat = '7x10';
let activeSide = 'fronte';
let canvasFronte, canvasRetro;
let zoom = 1;

function getActiveCanvas() {
  return activeSide === 'fronte' ? canvasFronte : canvasRetro;
}

function applyTouchSettings(obj) {
  if (!obj) return obj;
  var isTouch = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
  obj.set({
    hasControls: true,
    hasBorders: true,
    cornerSize: isTouch ? 28 : 12,
    cornerColor: '#c8a96e',
    cornerStrokeColor: '#8a6a20',
    cornerStyle: 'circle',
    transparentCorners: false,
    borderColor: '#c8a96e',
    borderScaleFactor: isTouch ? 3 : 2,
    padding: isTouch ? 16 : 4,
    touchCornerSize: isTouch ? 40 : 24,
  });
  return obj;
}

window.onload = function() {
  // Fullscreen automatico
  setTimeout(function() {
    var el = document.documentElement;
    if (el.requestFullscreen) el.requestFullscreen();
    else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
  }, 500);
  const fmt = FORMATI[currentFormat];

  // DPR per nitidezza su schermi Retina/HiDPI
  var dpr = window.devicePixelRatio || 1;
  var isTouch = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;

  // Nodi di ridimensionamento più grandi su touch/iPad
  fabric.Object.prototype.cornerSize = isTouch ? 28 : 12;
  fabric.Object.prototype.cornerColor = '#c8a96e';
  fabric.Object.prototype.cornerStrokeColor = '#8a6a20';
  fabric.Object.prototype.cornerStyle = 'circle';
  fabric.Object.prototype.transparentCorners = false;
  fabric.Object.prototype.borderColor = '#c8a96e';
  fabric.Object.prototype.borderScaleFactor = isTouch ? 3 : 2;
  fabric.Object.prototype.padding = isTouch ? 16 : 4;
  fabric.Object.prototype.touchCornerSize = isTouch ? 40 : 24;

  canvasFronte = new fabric.Canvas('canvas-fronte', {
    width: fmt.w, height: fmt.h,
    backgroundColor: '#ffffff',
    preserveObjectStacking: true,
    enableRetinaScaling: true,
    allowTouchScrolling: false,
  });
  canvasFronte.setDimensions({width: fmt.w, height: fmt.h});
  canvasFronte.getElement().style.width = fmt.w + 'px';
  canvasFronte.getElement().style.height = fmt.h + 'px';

  canvasRetro = new fabric.Canvas('canvas-retro', {
    width: fmt.w, height: fmt.h,
    backgroundColor: '#ffffff',
    preserveObjectStacking: true,
    enableRetinaScaling: true,
    allowTouchScrolling: false,
  });
  canvasRetro.setDimensions({width: fmt.w, height: fmt.h});
  canvasRetro.getElement().style.width = fmt.w + 'px';
  canvasRetro.getElement().style.height = fmt.h + 'px';
  // preload Monotype Corsiva poi re-render di entrambi i canvas
  if (document.fonts && document.fonts.load) {
    Promise.all([
      document.fonts.load("20px 'Monotype Corsiva'"),
      document.fonts.load("italic 20px 'Monotype Corsiva'"),
      document.fonts.load("bold 20px 'Monotype Corsiva'"),
      document.fonts.load("bold italic 20px 'Monotype Corsiva'")
    ]).then(function(){ if(canvasFronte) canvasFronte.renderAll(); if(canvasRetro) canvasRetro.renderAll(); });
  }

  // Bordo e label iniziale su fronte
  document.getElementById('border-fronte').style.borderColor = '#c8a96e';

  // Bordo e label iniziale su fronte
  document.getElementById('border-fronte').style.borderColor = '#c8a96e';

  // Events fronte
  canvasFronte.on('selection:created', updatePropsPanel);
  canvasFronte.on('selection:updated', updatePropsPanel);
  canvasFronte.on('selection:cleared', clearPropsPanel);
  canvasFronte.on('mouse:down', function() { if(activeSide !== 'fronte') setActiveSide('fronte'); });

  // Events retro
  canvasRetro.on('selection:created', updatePropsPanel);
  canvasRetro.on('selection:updated', updatePropsPanel);
  canvasRetro.on('selection:cleared', clearPropsPanel);
  canvasRetro.on('mouse:down', function() { if(activeSide !== 'retro') setActiveSide('retro'); });

  autoZoom();
  loadSavedTemplates();
};

// ── LATO ATTIVO ──
function setActiveSide(side) {
  activeSide = side;
  // Bordo dorato direttamente sul canvas element
  const cf = document.getElementById('canvas-fronte');
  const cr = document.getElementById('canvas-retro');
  const upperF = document.getElementById('canvas-fronte').nextElementSibling; // upper-canvas
  if (side === 'fronte') {
    document.getElementById('label-fronte').style.color = 'var(--gold)';
    document.getElementById('label-fronte').style.fontWeight = '600';
    document.getElementById('label-fronte').textContent = '● FRONTE';
    document.getElementById('label-retro').style.color = 'rgba(255,255,255,.4)';
    document.getElementById('label-retro').style.fontWeight = 'normal';
    document.getElementById('label-retro').textContent = '○ RETRO';
    document.getElementById('border-fronte').style.borderColor = '#c8a96e';
    document.getElementById('border-retro').style.borderColor = 'transparent';
  } else {
    document.getElementById('label-fronte').style.color = 'rgba(255,255,255,.4)';
    document.getElementById('label-fronte').style.fontWeight = 'normal';
    document.getElementById('label-fronte').textContent = '○ FRONTE';
    document.getElementById('label-retro').style.color = 'var(--gold)';
    document.getElementById('label-retro').style.fontWeight = '600';
    document.getElementById('label-retro').textContent = '● RETRO';
    document.getElementById('border-fronte').style.borderColor = 'transparent';
    document.getElementById('border-retro').style.borderColor = '#c8a96e';
  }
  document.getElementById('btn-fronte').style.background = side === 'fronte' ? 'var(--gold)' : 'var(--cream)';
  document.getElementById('btn-fronte').style.color = side === 'fronte' ? '#fff' : 'var(--ink)';
  document.getElementById('btn-fronte').style.borderColor = side === 'fronte' ? 'var(--gold)' : 'var(--border)';
  document.getElementById('btn-retro').style.background = side === 'retro' ? 'var(--gold)' : 'var(--cream)';
  document.getElementById('btn-retro').style.color = side === 'retro' ? '#fff' : 'var(--ink)';
  document.getElementById('btn-retro').style.borderColor = side === 'retro' ? 'var(--gold)' : 'var(--border)';
  clearPropsPanel();
}

// ── FORMATO ──
function cambiaFormato(fmt) {
  if (!confirm('Cambiare formato? Gli elementi verranno ridimensionati.')) {
    document.getElementById('formato-select').value = currentFormat;
    return;
  }
  currentFormat = fmt;
  const f = FORMATI[fmt];
  [canvasFronte, canvasRetro].forEach(c => {
    c.setWidth(f.w);
    c.setHeight(f.h);
    c.renderAll();
  });
  // Aggiorna dimensioni CSS wrapper
  const fis = FORMATI_FISICI[fmt];
  autoZoom();
}

// ── ZOOM ──
function autoZoom() {
  const area = document.querySelector('.canvases-row');
  const fis = FORMATI_FISICI[currentFormat];
  const availH = area.clientHeight - 80;
  const availW = (area.clientWidth - 150) / 2;
  zoom = Math.min(availH / fis.h, availW / fis.w, 2.5);
  zoom = Math.max(0.5, zoom);
  applyZoom();
}

function setZoom(d) { zoom = Math.max(0.3, Math.min(3, zoom + d)); applyZoom(); }
function resetZoom() { autoZoom(); }

function applyZoom() {
  const fis = FORMATI_FISICI[currentFormat];
  // Lo zoom reale include la scala interna del canvas
  const realZoom = zoom / SCALA;
  const scaledH = Math.round(fis.h * zoom);
  const scaledW = Math.round(fis.w * zoom);
  ['fronte','retro'].forEach(side => {
    const wrapper = document.getElementById('wrapper-' + side);
    const outer = document.getElementById('outer-' + side);
    wrapper.style.transform = `scale(${realZoom})`;
    wrapper.style.transformOrigin = 'top left';
    outer.style.width = scaledW + 'px';
    outer.style.height = scaledH + 'px';
    outer.style.overflow = 'hidden';
    const cs = outer.closest('.canvas-side');
    if(cs){ cs.style.width = scaledW+'px'; }
  });
  document.getElementById('zoom-label').textContent = Math.round(zoom*100)+'%';
}

// ── SFONDO ──
function setBg(type) {
  const c = getActiveCanvas();
  const colors = { white: '#ffffff', cream: '#f8f4ee', dark: '#1a1a2e' };
  c.backgroundColor = colors[type] || '#ffffff';
  c.getObjects().filter(o => o.customType === 'background').forEach(o => c.remove(o));
  c.renderAll();
}

function uploadBackground(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const c = getActiveCanvas();
    fabric.Image.fromURL(e.target.result, function(img) {
      img.set({
        left: 0, top: 0,
        scaleX: c.getWidth() / img.width,
        scaleY: c.getHeight() / img.height,
        selectable: false, evented: false, customType: 'background'
      });
      c.getObjects().filter(o => o.customType === 'background').forEach(o => c.remove(o));
      c.add(img);
      c.sendToBack(img);
      c.renderAll();
    });
  };
  reader.readAsDataURL(input.files[0]);
  input.value = '';
}

// ── BLOCCHI ──
function addBlock(type) {
  const c = getActiveCanvas();
  const W = c.getWidth();
  const H = c.getHeight();
  // fontSize scalato per canvas 3x
  const FS = SCALA;

  if (type === 'linea') {
    const line = new fabric.Line([10, 0, W-20, 0], {
      left: 10, top: H/2,
      stroke: '#1a1a2e', strokeWidth: 1,
      selectable: true
    });
    c.add(line); c.setActiveObject(line); c.renderAll();
    return;
  }

  let text = '', fontSize = 10, fontFamily = 'Cormorant Garamond';
  let fontStyle = 'normal', fontWeight = 'normal', textAlign = 'center', fill = '#1a1a2e', customType = 'testo';

  switch(type) {
    case 'nome':
      text = (praticaData.cognome || 'COGNOME') + '\n' + (praticaData.nome || 'Nome');
      fontSize = 16*FS; fontWeight = 'bold'; break;
    case 'eta':
      text = praticaData.anni ? 'di anni ' + praticaData.anni : 'di anni ___';
      fontSize = 11*FS; fontStyle = 'italic'; break;
    case 'date':
      text = (praticaData.data_nascita || '__/__/____') + ' — ' + (praticaData.data_morte || '__/__/____');
      fontSize = 9*FS; break;
    case 'frase':
      text = praticaData.frase || "E mancato all'affetto dei suoi cari";
      fontSize = 9*FS; fontStyle = 'italic'; break;
    case 'preghiera':
      text = (praticaData.prayer && praticaData.prayer.trim()) ? praticaData.prayer : "Signore, accogli nella tua pace\nl'anima del tuo servo.\nAmen.";
      fontSize = 8*FS; fontStyle = 'italic'; customType = 'preghiera';
      break;
    case 'logo':
      text = agenziaData.name || 'Nome Agenzia';
      fontSize = 7*FS; fill = '#8a7f72'; break;
    default:
      text = 'Testo libero...'; fontSize = 10*FS; break;
  }

  const obj = new fabric.Textbox(text, {
    left: 10, top: 30 + c.getObjects().length * 15,
    width: W - 20, fontSize, fontFamily, fontStyle, fontWeight, textAlign, fill, editable: true,
    customType: customType || 'testo',
  });
  applyTouchSettings(obj);
  c.add(obj); c.setActiveObject(obj); c.renderAll();
}

// ── FOTO ──
function insertFoto(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const c = getActiveCanvas();
    fabric.Image.fromURL(e.target.result, function(img) {
      const maxW = c.getWidth() * 0.72;
      const maxH = c.getHeight() * 0.45;
      const scale = Math.min(maxW/img.width, maxH/img.height);
      var scaledW2 = img.width * scale;
      img.set({
        left: c.getWidth()/2 - scaledW2/2,
        top: 10, scaleX: scale, scaleY: scale,
        selectable: true, strokeWidth: 0, stroke: '#c8a96e', customType: 'photo'
      });
      applyTouchSettings(img);
      c.add(img); c.setActiveObject(img); c.renderAll();
    });
  };
  reader.readAsDataURL(input.files[0]);
  input.value = '';
}

// ── SANTI ──
function openSantoModal() {
  document.getElementById('santo-modal').style.display = 'flex';
  loadSantiGallery();
}
function closeSantoModal() { document.getElementById('santo-modal').style.display = 'none'; }

function loadSantiGallery() {
  fetch('/admin/api/santi').then(r => r.json()).then(santi => {
    const gallery = document.getElementById('santi-gallery');
    if (!santi.length) {
      gallery.innerHTML = '<div style="text-align:center;color:var(--gray);font-size:.78rem;grid-column:1/-1;padding:.75rem">Nessun santo</div>';
      return;
    }
    gallery.innerHTML = santi.map(s => `
      <div style="cursor:pointer;border:2px solid var(--border);border-radius:6px;overflow:hidden;transition:border-color .2s"
           onmouseover="this.style.borderColor='#c8a96e'" onmouseout="this.style.borderColor='var(--border)'"
           onclick="selectSantoPreview('${s.url}','${s.name}')">
        <img src="${s.url}" style="width:100%;aspect-ratio:3/4;object-fit:cover">
        <div style="font-size:.6rem;padding:3px;text-align:center;color:var(--gray)">${s.name}</div>
      </div>`).join('');
  });
}

var selectedSantoUrl = null;

function selectSantoPreview(url, name) {
  selectedSantoUrl = url;
  document.getElementById('santo-preview-img').src = url;
  document.getElementById('santo-preview-box').style.display = 'flex';
  document.getElementById('santo-nome-sel').textContent = name;
  updateSantoOpacitaLabel();
}

function updateSantoOpacitaLabel() {
  var val = document.getElementById('santo-opacita').value;
  document.getElementById('santo-opacita-val').textContent = val + '%';
}

function inserisciSantoSelezionato() {
  if (!selectedSantoUrl) { alert('Seleziona prima un santo'); return; }
  var opacita = parseInt(document.getElementById('santo-opacita').value) / 100;
  insertSantoFromGallery(selectedSantoUrl, opacita);
}

function uploadSanto(input) {
  if (!input.files[0]) return;
  const name = prompt('Nome del santo:', '');
  if (!name) return;
  const formData = new FormData();
  formData.append('image', input.files[0]);
  formData.append('name', name);
  formData.append('_token', '{{ csrf_token() }}');
  fetch('/admin/api/santi', { method: 'POST', body: formData })
    .then(r => r.json()).then(res => { if(res.success) { loadSantiGallery(); selectSantoPreview(res.url, name); }});
  input.value = '';
}

function insertSantoFromGallery(url, opacita) {
  closeSantoModal();
  if (opacita === undefined) opacita = 1;
  const c = getActiveCanvas();
  fabric.Image.fromURL(url, function(img) {
    const maxH = c.getHeight() * 0.55;
    const maxW = c.getWidth() * 0.7;
    const scale = Math.min(maxW/img.width, maxH/img.height);
    const scaledW = img.width * scale;
    const scaledH = img.height * scale;
    img.set({
      left: c.getWidth()/2 - scaledW/2,
      top: c.getHeight()/2 - scaledH/2,
      scaleX: scale, scaleY: scale,
      opacity: opacita,
      selectable: true, strokeWidth: 0, customType: 'santo'
    });
    applyTouchSettings(img);
    // Manda in fondo se è filigrana
    if (opacita < 0.5) {
      c.add(img);
      c.sendToBack(img);
    } else {
      c.add(img);
    }
    c.setActiveObject(img);
    c.renderAll();
  });
}

// ── PROPS PANEL ──
function updatePropsPanel() {
  const c = getActiveCanvas();
  const obj = c.getActiveObject();
  if (!obj) { clearPropsPanel(); return; }
  document.getElementById('no-selection').style.display = 'none';
  document.getElementById('props-content').style.display = 'block';
  if (obj.type === 'textbox' || obj.type === 'text') {
    document.getElementById('prop-text').value = obj.text || '';
    document.getElementById('prop-font').value = obj.fontFamily || 'Cormorant Garamond';
    document.getElementById('prop-size').value = obj.fontSize || 10;
    document.getElementById('prop-lineheight').value = obj.lineHeight || 1.4;
    document.getElementById('prop-color').value = obj.fill || '#000000';
    document.getElementById('prop-color-hex').value = obj.fill || '#000000';
    document.getElementById('btn-bold').classList.toggle('active', obj.fontWeight === 'bold');
    document.getElementById('btn-italic').classList.toggle('active', obj.fontStyle === 'italic');
    ['left','center','right'].forEach(a => document.getElementById('btn-'+a).classList.toggle('active', obj.textAlign === a));
  }
  document.getElementById('prop-x').value = Math.round(obj.left);
  document.getElementById('prop-y').value = Math.round(obj.top);
  const borderGroup = document.getElementById('border-group');
  if (obj.type === 'image') {
    borderGroup.style.display = 'block';
    document.getElementById('prop-stroke-width').value = obj.strokeWidth || 0;
    document.getElementById('prop-stroke-color').value = obj.stroke || '#c8a96e';
  } else {
    borderGroup.style.display = 'none';
  }
}

function clearPropsPanel() {
  document.getElementById('no-selection').style.display = 'block';
  document.getElementById('props-content').style.display = 'none';
}

function changeRicordinoSize(delta) {
  var input = document.getElementById('prop-size');
  var current = parseInt(input.value) || 12;
  var step = current >= 60 ? 2 : 1;
  var newVal = Math.max(6, Math.min(200, current + delta * step));
  input.value = newVal;
  var obj = getActiveCanvas().getActiveObject();
  if (obj) { obj.set('fontSize', newVal); getActiveCanvas().renderAll(); }
}

function changeRicordinoLineH(delta) {
  var input = document.getElementById('prop-lineheight');
  var current = parseFloat(input.value) || 1.4;
  var newVal = Math.max(0.8, Math.min(4, Math.round((current + delta) * 10) / 10));
  input.value = newVal.toFixed(1);
  var obj = getActiveCanvas().getActiveObject();
  if (obj) { obj.set('lineHeight', newVal); getActiveCanvas().renderAll(); }
}

function updateProp(prop) {
  const c = getActiveCanvas();
  const obj = c.getActiveObject();
  if (!obj) return;
  if (prop === 'text') obj.set('text', document.getElementById('prop-text').value);
  else if (prop === 'font') obj.set('fontFamily', document.getElementById('prop-font').value);
  else if (prop === 'size') obj.set('fontSize', parseInt(document.getElementById('prop-size').value));
  else if (prop === 'color') { const col = document.getElementById('prop-color').value; obj.set('fill',col); document.getElementById('prop-color-hex').value=col; }
  else if (prop === 'lineHeight') obj.set('lineHeight', parseFloat(document.getElementById('prop-lineheight').value));
  else if (prop === 'x') obj.set('left', parseInt(document.getElementById('prop-x').value));
  else if (prop === 'y') obj.set('top', parseInt(document.getElementById('prop-y').value));
  obj.setCoords(); c.renderAll();
}

function syncColor() {
  const hex = document.getElementById('prop-color-hex').value;
  if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
    document.getElementById('prop-color').value = hex;
    const obj = getActiveCanvas().getActiveObject();
    if (obj) { obj.set('fill', hex); getActiveCanvas().renderAll(); }
  }
}

function toggleStyle(style) {
  const c = getActiveCanvas();
  const obj = c.getActiveObject();
  if (!obj) return;
  if (style === 'bold') { obj.set('fontWeight', obj.fontWeight==='bold'?'normal':'bold'); document.getElementById('btn-bold').classList.toggle('active'); }
  else if (style === 'italic') { obj.set('fontStyle', obj.fontStyle==='italic'?'normal':'italic'); document.getElementById('btn-italic').classList.toggle('active'); }
  c.renderAll();
}

function setAlign(align) {
  const c = getActiveCanvas();
  const obj = c.getActiveObject();
  if (!obj) return;
  obj.set('textAlign', align);
  ['left','center','right'].forEach(a => document.getElementById('btn-'+a).classList.remove('active'));
  document.getElementById('btn-'+align).classList.add('active');
  c.renderAll();
}

function updateBorder() {
  const c = getActiveCanvas();
  const obj = c.getActiveObject();
  if (!obj) return;
  const sw = parseInt(document.getElementById('prop-stroke-width').value)||0;
  const sc = document.getElementById('prop-stroke-color').value;
  obj.set({ strokeWidth: sw, stroke: sw>0?sc:null });
  c.renderAll();
}

function setStrokePreset(size) {
  document.getElementById('prop-stroke-width').value = size;
  updateBorder();
}

function deleteSelected() {
  const c = getActiveCanvas();
  const obj = c.getActiveObject();
  if (obj) { c.remove(obj); clearPropsPanel(); c.renderAll(); }
}

// ── ALLINEAMENTO ──
function alignObjects(type) {
  const c = getActiveCanvas();
  const objs = c.getActiveObjects();
  if (!objs || objs.length < 2) { alert('Seleziona almeno 2 elementi'); return; }
  let minX=Infinity, minY=Infinity, maxX=-Infinity, maxY=-Infinity;
  objs.forEach(o => { const b=o.getBoundingRect(); minX=Math.min(minX,b.left); minY=Math.min(minY,b.top); maxX=Math.max(maxX,b.left+b.width); maxY=Math.max(maxY,b.top+b.height); });
  objs.forEach(o => {
    const b=o.getBoundingRect();
    if(type==='left') o.set('left',minX+(o.left-b.left));
    else if(type==='right') o.set('left',maxX-b.width+(o.left-b.left));
    else if(type==='centerH') o.set('left',(minX+maxX)/2-b.width/2+(o.left-b.left));
    else if(type==='top') o.set('top',minY+(o.top-b.top));
    else if(type==='bottom') o.set('top',maxY-b.height+(o.top-b.top));
    else if(type==='centerV') o.set('top',(minY+maxY)/2-b.height/2+(o.top-b.top));
    o.setCoords();
  });
  c.renderAll();
}

function distributeObjects(dir) {
  const c = getActiveCanvas();
  const objs = c.getActiveObjects();
  if (!objs || objs.length < 3) { alert('Seleziona almeno 3 elementi'); return; }
  if (dir === 'h') {
    const sorted = [...objs].sort((a,b) => a.getBoundingRect().left - b.getBoundingRect().left);
    const first=sorted[0].getBoundingRect(), last=sorted[sorted.length-1].getBoundingRect();
    const totalW=sorted.reduce((s,o)=>s+o.getBoundingRect().width,0);
    const gap=(last.left+last.width-first.left-totalW)/(sorted.length-1);
    let x=first.left;
    sorted.forEach(o => { const b=o.getBoundingRect(); o.set('left',x+(o.left-b.left)); o.setCoords(); x+=b.width+gap; });
  } else {
    const sorted = [...objs].sort((a,b) => a.getBoundingRect().top - b.getBoundingRect().top);
    const first=sorted[0].getBoundingRect(), last=sorted[sorted.length-1].getBoundingRect();
    const totalH=sorted.reduce((s,o)=>s+o.getBoundingRect().height,0);
    const gap=(last.top+last.height-first.top-totalH)/(sorted.length-1);
    let y=first.top;
    sorted.forEach(o => { const b=o.getBoundingRect(); o.set('top',y+(o.top-b.top)); o.setCoords(); y+=b.height+gap; });
  }
  c.renderAll();
}

function bringForward() { const c=getActiveCanvas(); const obj=c.getActiveObject(); if(obj){c.bringForward(obj);c.renderAll();} }
function sendBackward() { const c=getActiveCanvas(); const obj=c.getActiveObject(); if(obj){c.sendBackwards(obj);c.renderAll();} }

// ── TEMPLATE SALVATI ──
function loadSavedTemplates() {
  fetch('/admin/api/ricordino-templates').then(r=>r.json()).then(templates => {
    const container = document.getElementById('saved-templates-list');
    if (!templates.length) {
      container.innerHTML='<div style="color:var(--gray);font-size:.75rem;font-style:italic;padding:.4rem">Nessun template</div>';
      return;
    }
    container.innerHTML = templates.map(t => `
      <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.35rem;padding:.35rem;border:1px solid var(--border);border-radius:5px;background:var(--cream)">
        ${t.thumbnail?`<img src="${t.thumbnail}" style="width:28px;height:40px;object-fit:cover;border-radius:3px;flex-shrink:0">`:'<div style="width:28px;height:40px;background:#ddd;border-radius:3px;flex-shrink:0"></div>'}
        <div style="flex:1;min-width:0"><div style="font-size:.73rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.name}</div></div>
        <div style="display:flex;flex-direction:column;gap:2px">
          <button onclick="loadSavedTemplate(${t.id})" style="font-size:.62rem;padding:2px 4px;border:1px solid var(--border);border-radius:3px;background:var(--ink);color:#fff;cursor:pointer">↓</button>
          <button onclick="deleteSavedTemplate(${t.id})" style="font-size:.62rem;padding:2px 4px;border:1px solid var(--border);border-radius:3px;background:var(--red);color:#fff;cursor:pointer">✕</button>
        </div>
      </div>`).join('');
  });
}

function saveAsTemplate() {
  const name = prompt('Nome template:', 'Ricordino ' + new Date().toLocaleDateString('it-IT'));
  if (!name) return;
  const thumbnail = canvasFronte.toDataURL({format:'jpeg',quality:0.5,multiplier:0.3});
  const data = { name, format: currentFormat, thumbnail,
    canvas_fronte: JSON.stringify(canvasFronte.toJSON(['customType'])),
    canvas_retro: JSON.stringify(canvasRetro.toJSON(['customType'])) };
  fetch('/admin/api/ricordino-templates', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
    body: JSON.stringify(data)
  }).then(r=>r.json()).then(res => { if(res.success){alert('Template salvato!');loadSavedTemplates();} });
}

function loadSavedTemplate(id) {
  fetch('/admin/api/ricordino-templates').then(r=>r.json()).then(templates => {
    const t = templates.find(x=>x.id==id);
    if (!t) return;
    const f = FORMATI[t.format] || FORMATI['7x10'];
    [canvasFronte, canvasRetro].forEach(c => { c.setWidth(f.w); c.setHeight(f.h); });
    document.getElementById('formato-select').value = t.format || '7x10';
    currentFormat = t.format || '7x10';
    canvasFronte.loadFromJSON(t.canvas_fronte, () => canvasFronte.renderAll());
    canvasRetro.loadFromJSON(t.canvas_retro, () => canvasRetro.renderAll());
    autoZoom();
  });
}

function deleteSavedTemplate(id) {
  if (!confirm('Eliminare?')) return;
  fetch('/admin/api/ricordino-templates/'+id, {method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
    .then(r=>r.json()).then(res => { if(res.success) loadSavedTemplates(); });
}

// ── CLEAR ──
function clearCanvas() {
  if (!confirm('Pulire entrambi i lati?')) return;
  [canvasFronte, canvasRetro].forEach(c => { c.clear(); c.backgroundColor='#ffffff'; c.renderAll(); });
}

// ── EXPORT ──
function exportPNG() {
  ['fronte','retro'].forEach((side,i) => {
    setTimeout(() => {
      const c = side==='fronte'?canvasFronte:canvasRetro;
      const url = c.toDataURL({format:'png',quality:1,multiplier:4});
      const a = document.createElement('a');
      a.href=url; a.download='ricordino_'+side+'.png'; a.click();
    }, i*500);
  });
}

// ── IMPAGINATORE FRONTE/RETRO PDF ──
function apriImpaginatorePDF() {
  document.getElementById('modal-impaginatore').style.display = 'flex';
}
function chiudiImpaginatore() {
  document.getElementById('modal-impaginatore').style.display = 'none';
}

async function generaPDF(formatoPagina) {
  var btn = document.getElementById('btn-genera-pdf');
  btn.textContent = 'Generazione in corso...';
  btn.disabled = true;

  var imgFronte = canvasFronte.toDataURL({format:'jpeg', quality:0.95, multiplier:4});
  var imgRetro = canvasRetro.toDataURL({format:'jpeg', quality:0.95, multiplier:4});

  var pagine = {
    'A4': { w: 210, h: 297 },
    'A3': { w: 297, h: 420 },
  };
  var pag = pagine[formatoPagina] || pagine['A4'];
  var ricW = currentFormat === '7x10' ? 70 : 60;
  var ricH = currentFormat === '7x10' ? 100 : 90;
  var spazio = 3;

  // Calcola quante colonne e righe entrano nel foglio
  var cols = Math.floor(pag.w / (ricW + spazio));
  var rows = Math.floor(pag.h / (ricH + spazio));
  var copie = parseInt(document.getElementById('pdf-copie').value) || 1;
  cols = Math.min(cols, copie);
  rows = Math.ceil(copie / cols);

  // Dimensione esatta del blocco
  var bloccoW = cols * ricW + (cols - 1) * spazio;
  var bloccoH = rows * ricH + (rows - 1) * spazio;

  // Margini per centrare il blocco nella pagina
  var marginLeft = (pag.w - bloccoW) / 2;
  var marginTop  = (pag.h - bloccoH) / 2;

  var script = document.createElement('script');
  script.src = '/vendor/libs/jspdf.umd.min.js';
  script.onload = function() {
    var { jsPDF } = window.jspdf;
    var doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: formatoPagina });

    // ── PAGINA FRONTE: blocco centrato, ordine normale sx→dx ──
    var count = 0;
    for (var r = 0; r < rows; r++) {
      for (var c = 0; c < cols; c++) {
        if (count >= copie) break;
        var x = marginLeft + c * (ricW + spazio);
        var y = marginTop  + r * (ricH + spazio);
        doc.addImage(imgFronte, 'JPEG', x, y, ricW, ricH);
        count++;
      }
    }

    // ── PAGINA RETRO: blocco centrato, ordine SPECCHIATO dx→sx ──
    doc.addPage();
    count = 0;
    for (var r = 0; r < rows; r++) {
      for (var c = cols - 1; c >= 0; c--) {
        if (count >= copie) break;
        var x = marginLeft + c * (ricW + spazio);
        var y = marginTop  + r * (ricH + spazio);
        doc.addImage(imgRetro, 'JPEG', x, y, ricW, ricH);
        count++;
      }
    }

    doc.save('ricordini_stampa_' + formatoPagina + '.pdf');
    btn.textContent = 'Genera PDF';
    btn.disabled = false;
    chiudiImpaginatore();
  };
  document.head.appendChild(script);
}

// ── SALVA PREGHIERA NEL NECROLOGIO ──
function salvaPreghieraNecrologio() {
  var pid = {{ $praticaId ?? 'null' }};
  if (!pid) { alert('Nessuna pratica collegata'); return; }
  var allObjs = canvasFronte.getObjects().concat(canvasRetro.getObjects());
  var prayerObjs = allObjs.filter(function(o){ return o.type === 'textbox' && o.fontStyle === 'italic' && o.text && o.text.length > 5; });
  var prayerText = prayerObjs.map(function(o){ return o.text; }).join('\n');
  // Verifica se esiste già un necrologio per questa pratica
  fetch('/admin/api/necrologio-pratica/'+pid)
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (d.exists) {
        // Necrologio esiste - aggiorna la preghiera
        fetch('/admin/api/salva-preghiera', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
          body: JSON.stringify({ pratica_id: pid, prayer: prayerText })
        }).then(r=>r.json()).then(function(res){
          if(res.success){
            var msg=document.createElement('div');
            msg.textContent='✓ Preghiera aggiornata nel necrologio!';
            msg.style.cssText='position:fixed;top:70px;right:20px;background:#0f3460;color:#fff;padding:.75rem 1.25rem;border-radius:8px;font-size:.85rem;z-index:9999;box-shadow:0 4px 15px rgba(0,0,0,.3)';
            document.body.appendChild(msg);
            setTimeout(function(){ msg.remove(); },3000);
          }
        });
      } else {
        // Necrologio non esiste - vai alla creazione con dati precompilati
        var url = '/admin/necrologi/nuovo?pratica_id='+pid;
        if (prayerText) url += '&prayer='+encodeURIComponent(prayerText);
        window.location.href = url;
      }
    });
}

// ── SALVA PRATICA ──
function saveToPratica() {
  const practiceId = {{ $praticaId ?? 'null' }};
  if (!practiceId) { alert('Nessuna pratica collegata'); return; }
  const btn = event.target;
  btn.textContent = '⏳...'; btn.disabled = true;
  const preview = canvasFronte.toDataURL({format:'jpeg',quality:0.6,multiplier:0.4});
  fetch('/admin/api/defunto/'+practiceId+'/ricordino', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
    body: JSON.stringify({
      canvas_fronte: JSON.stringify(canvasFronte.toJSON(['customType'])),
      canvas_retro: JSON.stringify(canvasRetro.toJSON(['customType'])),
      preview, preview_retro: canvasRetro.toDataURL({format:'jpeg',quality:0.6,multiplier:0.4}), format: currentFormat
    })
  }).then(r=>r.json()).then(res => {
    btn.textContent='💾 Salva'; btn.disabled=false;
    if(res.success){
      // Salva preghiera nel necrologio collegato
      // Cerca blocco preghiera in entrambi i canvas
      var allObjs = canvasFronte.getObjects().concat(canvasRetro.getObjects());
      var prayerObj = allObjs.find(function(o){ return o.customType === 'preghiera' || (o.type === 'textbox' && o.fontStyle === 'italic' && o.text && o.text.length > 10); });
      if (prayerObj && prayerObj.text && practiceId) {
        fetch('/admin/api/salva-preghiera', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
          body: JSON.stringify({ pratica_id: practiceId, prayer: prayerObj.text })
        });
      }
      const msg=document.createElement('div');
      msg.textContent='✓ Ricordino salvato!';
      msg.style.cssText='position:fixed;top:70px;right:20px;background:#3a7a5a;color:#fff;padding:.75rem 1.25rem;border-radius:8px;font-size:.85rem;z-index:9999;box-shadow:0 4px 15px rgba(0,0,0,.3)';
      document.body.appendChild(msg);
      setTimeout(()=>msg.remove(),3000);
    }
  }).catch(()=>{btn.textContent='💾 Salva';btn.disabled=false;});
}

// ── SIMBOLI RELIGIOSI ──
function insertSimbolo(tipo) {
  var c = getActiveCanvas();
  var W = c.getWidth();
  var H = c.getHeight();
  var simboli = {
    croce_latina: '✝', croce_greca: '✚', croce_ortodossa: '☦', croce_raggi: '✛',
    stella: '✦', stella6: '✡', omega: 'Ω', chi_rho: '☧',
    separatore: '— ✦ —', giglio: '⚜', colomba: '🕊', infinito: '∞', gamma: 'Γ', alfa_omega: 'Αω'
  };
  var char = simboli[tipo] || '✝';
  var fontSize = tipo === 'separatore' ? Math.round(12*SCALA) : Math.round(28*SCALA);
  var obj = new fabric.Text(char, {
    left: W/2,
    top: H/2,
    fontSize: fontSize,
    fontFamily: 'serif',
    fill: '#1a1a2e',
    originX: 'center',
    originY: 'center',
    selectable: true,
    editable: false,
  });
  c.add(obj);
  c.setActiveObject(obj);
  c.renderAll();
}


// ── SIMBOLI RELIGIOSI ──
function insertSimbolo(tipo) {
  var c = getActiveCanvas();
  var W = c.getWidth();
  var H = c.getHeight();
  var simboli = {
    croce_latina: '✝', croce_greca: '✚', croce_ortodossa: '☦', croce_raggi: '✛',
    stella: '✦', stella6: '✡', omega: 'Ω', chi_rho: '☧',
    separatore: '— ✦ —', giglio: '⚜', colomba: '🕊', infinito: '∞',
    gamma: 'Γ', alfa_omega: 'Αω'
  };
  var char = simboli[tipo] || '✝';
  var fontSize = tipo === 'separatore' ? Math.round(12*SCALA) : Math.round(28*SCALA);
  var obj = new fabric.Text(char, {
    left: W/2,
    top: H/2,
    fontSize: fontSize,
    fontFamily: 'serif',
    fill: '#1a1a2e',
    originX: 'center',
    originY: 'center',
    selectable: true,
    editable: false,
  });
  c.add(obj);
  c.setActiveObject(obj);
  c.renderAll();
}

</script>




<!-- MODAL GALLERIA FOTO PRATICA -->
<div id="galleria-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.8);z-index:1000;align-items:center;justify-content:center">
  <div style="background:var(--white);border-radius:14px;padding:1.75rem;width:700px;max-width:96vw;max-height:90vh;display:flex;flex-direction:column">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
      <div>
        <div style="font-family:Cormorant Garamond,serif;font-size:1.4rem">🖼 Galleria Foto Pratica</div>
        <div style="font-size:.75rem;color:var(--gray);margin-top:.2rem">Seleziona una foto dalla galleria o vai al modulo Foto per elaborarne una nuova</div>
      </div>
      <button onclick="chiudiGalleriaFoto()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--gray)">✕</button>
    </div>
    <div id="galleria-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;overflow-y:auto;flex:1;padding:.25rem">
      <div style="color:var(--gray);font-size:.82rem;grid-column:1/-1;text-align:center;padding:2rem">Caricamento foto...</div>
    </div>
    <div style="margin-top:1rem;display:flex;justify-content:flex-end;gap:.75rem;border-top:1px solid var(--border);padding-top:1rem">
      @if(isset($praticaId))
      <a href="/admin/pratiche/{{ $praticaId }}/foto" target="_blank" style="padding:.55rem 1rem;background:rgba(124,58,237,.1);color:#7c3aed;border:1px solid #7c3aed;border-radius:8px;font-size:.82rem;text-decoration:none;font-family:DM Sans,sans-serif">
        📸 Vai al modulo Foto
      </a>
      @endif
      <button onclick="chiudiGalleriaFoto()" style="padding:.55rem 1rem;background:var(--cream);color:var(--ink);border:1px solid var(--border);border-radius:8px;font-size:.82rem;cursor:pointer;font-family:DM Sans,sans-serif">
        Chiudi
      </button>
    </div>
  </div>
</div>

<script>
// ── GALLERIA FOTO PRATICA ──
const fotoGalleriaData = @json($fotoGalleria ?? []);
const fotoElaborataPrincipale = @json($fotoElaborata ?? null);

function apriGalleriaFoto() {
  document.getElementById('galleria-modal').style.display = 'flex';
  renderGalleria();
}

function chiudiGalleriaFoto() {
  document.getElementById('galleria-modal').style.display = 'none';
}

function renderGalleria() {
  var grid = document.getElementById('galleria-grid');
  if (!fotoGalleriaData || fotoGalleriaData.length === 0) {
    grid.innerHTML = '<div style="color:var(--gray);font-size:.82rem;grid-column:1/-1;text-align:center;padding:2rem">Nessuna foto nella galleria.<br>Usa il modulo 📸 Foto per caricare e elaborare le foto.</div>';
    return;
  }
  grid.innerHTML = '';
  fotoGalleriaData.forEach(function(foto) {
    var div = document.createElement('div');
    div.style.cssText = 'position:relative;cursor:pointer;border-radius:8px;overflow:hidden;border:2px solid ' + (foto.is_principale ? '#c8a96e' : 'transparent') + ';transition:all .2s';
    div.onmouseover = function() { this.style.borderColor = '#c8a96e'; };
    div.onmouseout = function() { this.style.borderColor = foto.is_principale ? '#c8a96e' : 'transparent'; };
    div.onclick = function() { usaFotoGalleria(foto.url); };
    div.innerHTML = '<img src="' + foto.url + '" style="width:100%;height:120px;object-fit:cover;display:block">' +
      (foto.is_principale ? '<div style="position:absolute;top:4px;left:4px;background:#c8a96e;color:#1a1a2e;font-size:.6rem;padding:.15rem .4rem;border-radius:3px;font-weight:600">★ Principale</div>' : '') +
      '<div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.6);color:#fff;font-size:.65rem;padding:.3rem .4rem">' + ucfirstTipo(foto.tipo) + '</div>';
    grid.appendChild(div);
  });
}

function ucfirstTipo(t) {
  if (!t) return '';
  return t.charAt(0).toUpperCase() + t.slice(1);
}

function usaFotoGalleria(url) {
  url = (url || "").replace(/^https?:\/\/[^\/]+/i, "");
  var c = getActiveCanvas();
  fabric.Image.fromURL(url, function(img) {
    var maxW = c.getWidth() * 0.72;
    var maxH = c.getHeight() * 0.45;
    var scale = Math.min(maxW / img.width, maxH / img.height);
    var scaledW = img.width * scale;
    img.set({
      left: Math.round(c.getWidth() / 2 - scaledW / 2),
      top: Math.round(c.getHeight() * 0.04),
      scaleX: scale, scaleY: scale,
      selectable: true,
      strokeWidth: 0, stroke: '#c8a96e', customType: 'photo'
    });
    applyTouchSettings(img);
    c.add(img);
    c.setActiveObject(img);
    c.renderAll();
  }, { crossOrigin: 'anonymous' });
  chiudiGalleriaFoto();
}

// ── CARICA RICORDINO SALVATO (flusso lineare e robusto) ──
@if(!empty($savedFronte))
window.addEventListener('load', function() {
  function avviaCaricamento() {
    if (!canvasFronte || !canvasRetro) { setTimeout(avviaCaricamento, 60); return; }

    var fmtSaved = '{{ $savedFormat ?? "7x10" }}';

    // 1) BINARIO FORMATO+DIMENSIONI: porta le canvas al formato salvato (ridimensiona, non ricrea)
    if (fmtSaved !== currentFormat && FORMATI[fmtSaved]) {
      currentFormat = fmtSaved;
      var f = FORMATI[fmtSaved];
      [canvasFronte, canvasRetro].forEach(function(cv){
        cv.setWidth(f.w); cv.setHeight(f.h); cv.renderAll();
      });
      var sel = document.getElementById('formato-select');
      if (sel) sel.value = fmtSaved;
      if (typeof autoZoom === 'function') autoZoom();
    }

    // 2) BINARIO CONTENUTO: popola il JSON nel CALLBACK (quando la canvas e pronta), non a timeout
    canvasFronte.loadFromJSON({!! json_encode($savedFronte) !!}, function() {
      canvasFronte.getObjects().forEach(function(o){ applyTouchSettings(o); });
      canvasFronte.renderAll();
    });
    @if(!empty($savedRetro))
    canvasRetro.loadFromJSON({!! json_encode($savedRetro) !!}, function() {
      canvasRetro.getObjects().forEach(function(o){ applyTouchSettings(o); });
      canvasRetro.renderAll();
    });
    @endif
  }
  avviaCaricamento();
});
@endif

// Carica foto principale automaticamente se disponibile
window.addEventListener('load', function() {
  if (fotoElaborataPrincipale) {
    setTimeout(function() {
      var banner = document.getElementById('banner-foto-principale');
      if (banner) banner.style.display = 'flex';
    }, 500);
  }
});

// ── TOUCH PINCH-TO-RESIZE su iPad ──
(function() {
  var lastDist = 0;
  var lastScale = 1;
  var pinchObj = null;
  var lastObjScaleX = 1;
  var lastObjScaleY = 1;

  function getDist(touches) {
    var dx = touches[0].clientX - touches[1].clientX;
    var dy = touches[0].clientY - touches[1].clientY;
    return Math.sqrt(dx*dx + dy*dy);
  }

  function setupPinch(cv) {
    var el = cv.getElement().parentNode;
    if (!el) return;

    el.addEventListener('touchstart', function(e) {
      if (e.touches.length === 2) {
        e.preventDefault();
        lastDist = getDist(e.touches);
        pinchObj = cv.getActiveObject();
        if (pinchObj) {
          lastObjScaleX = pinchObj.scaleX;
          lastObjScaleY = pinchObj.scaleY;
          lastScale = 1;
        }
      }
    }, { passive: false });

    el.addEventListener('touchmove', function(e) {
      if (e.touches.length === 2) {
        e.preventDefault();
        var dist = getDist(e.touches);
        var ratio = dist / lastDist;
        if (pinchObj) {
          // Ridimensiona oggetto selezionato
          pinchObj.set({
            scaleX: Math.max(0.05, lastObjScaleX * ratio),
            scaleY: Math.max(0.05, lastObjScaleY * ratio),
          });
          cv.renderAll();
        }
      }
    }, { passive: false });

    el.addEventListener('touchend', function(e) {
      if (e.touches.length < 2) {
        pinchObj = null;
        lastDist = 0;
      }
    }, { passive: false });
  }

  window.addEventListener('load', function() {
    setTimeout(function() {
      if (canvasFronte) setupPinch(canvasFronte);
      if (canvasRetro) setupPinch(canvasRetro);
    }, 800);
  });
})();
</script>

{{-- MODAL IMPAGINATORE PDF --}}
<div id="modal-impaginatore" style="display:none;position:fixed;inset:0;background:rgba(10,10,20,.75);z-index:500;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:2rem;width:94%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.3)">
    <div style="font-size:1.1rem;font-weight:600;color:#1a1a2e;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid #e8e0d4">
      📄 Impaginatore Stampa Fronte/Retro
    </div>
    <div style="font-size:.82rem;color:#8a7f72;margin-bottom:1.25rem;line-height:1.6">
      Genera un PDF con tutti i fronti su una pagina e tutti i retri sulla pagina successiva, pronti per la stampa fronte/retro.
    </div>
    <div style="margin-bottom:1rem">
      <label style="font-size:.78rem;font-weight:500;color:#8a7f72;display:block;margin-bottom:.35rem">Formato foglio</label>
      <select id="pdf-formato" style="width:100%;border:1px solid #e0dbd4;border-radius:6px;padding:.65rem .9rem;font-size:.9rem;font-family:Inter,sans-serif">
        <option value="A4">A4 — 210x297mm (standard)</option>
        <option value="A3">A3 — 297x420mm (grande formato)</option>
      </select>
    </div>
    <div style="margin-bottom:1.5rem">
      <label style="font-size:.78rem;font-weight:500;color:#8a7f72;display:block;margin-bottom:.35rem">Numero copie</label>
      <input type="number" id="pdf-copie" value="10" min="1" max="100" style="width:100%;border:1px solid #e0dbd4;border-radius:6px;padding:.65rem .9rem;font-size:.9rem;font-family:Inter,sans-serif">
      <div style="font-size:.72rem;color:#aaa;margin-top:.25rem">Il sistema impaginerà automaticamente il numero di ricordini per foglio</div>
    </div>
    <div style="display:flex;gap:.75rem;justify-content:flex-end">
      <button onclick="chiudiImpaginatore()" style="background:#f0ece6;color:#8a7f72;border:1px solid #e0dbd4;padding:.6rem 1.25rem;border-radius:6px;font-size:.85rem;cursor:pointer;font-family:Inter,sans-serif">Annulla</button>
      <button id="btn-genera-pdf" onclick="generaPDF(document.getElementById('pdf-formato').value)" style="background:#1a1a2e;color:#fff;border:none;padding:.6rem 1.5rem;border-radius:6px;font-size:.85rem;font-weight:600;cursor:pointer;font-family:Inter,sans-serif">Genera PDF</button>
    </div>
  </div>
</div>
<script>
function inviaApprovazione(tipo) {
  var praticaId = {{ $praticaId ?? 'null' }};
  if (!praticaId) { alert('Salva prima il documento'); return; }
  document.getElementById('confirm-invio-tipo').textContent = tipo;
  document.getElementById('confirm-invio-modal').style.display='flex';
  document.getElementById('confirm-invio-btn').onclick = function() {
    document.getElementById('confirm-invio-modal').style.display='none';
    var canvas = document.getElementById('canvasFronte') || document.getElementById('canvas');
    var imgData = canvas ? canvas.toDataURL('image/png') : null;
    fetch('/admin/pratiche/' + praticaId + '/invia-approvazione', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
    body: JSON.stringify({tipo: tipo, immagine: imgData})
  }).then(r => r.json()).then(res => {
    if (res.success) {
      document.getElementById('invio-modal-email').textContent = res.email || '';
      document.getElementById('invio-modal-link').href = res.approva_url || '#';
      document.getElementById('invio-modal-link').textContent = res.approva_url || '';
      var waBtn = document.getElementById('invio-modal-wa');
      if (res.whatsapp_url) { waBtn.href = res.whatsapp_url; waBtn.style.display='inline-flex'; }
      else { waBtn.style.display='none'; }
      var emailRow = document.getElementById('invio-modal-email-row');
      if (res.email_inviata) { emailRow.style.display='block'; }
      else { emailRow.style.display='none'; }
      document.getElementById('invio-modal').style.display='flex';
    } else { alert('Errore: ' + (res.error || 'sconosciuto')); }
    }).catch(() => alert('Errore di connessione'));
  };
}
</script>
{{-- MODAL CONFERMA INVIO --}}
<div id="confirm-invio-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9998;align-items:center;justify-content:center;padding:1rem">
  <div style="background:#fff;border-radius:14px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden">
    <div style="background:#1a1a2e;padding:1rem 1.5rem">
      <div style="color:#c8a96e;font-family:monospace;font-size:.75rem;letter-spacing:.15em">📤 INVIA PER APPROVAZIONE</div>
    </div>
    <div style="padding:1.5rem">
      <div style="font-size:.95rem;font-weight:600;color:#1a1a2e;margin-bottom:.5rem">Inviare il <span id="confirm-invio-tipo"></span> alla famiglia?</div>
      <div style="font-size:.82rem;color:#8a7f72;margin-bottom:1.5rem">La famiglia riceverà un link per visualizzare e approvare il documento. Potranno anche richiedere modifiche.</div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end">
        <button onclick="document.getElementById('confirm-invio-modal').style.display='none'" style="background:#f5f0eb;border:1px solid #e0dbd4;color:#1a1a2e;padding:.6rem 1.25rem;border-radius:7px;font-size:.85rem;cursor:pointer;font-family:inherit">Annulla</button>
        <button id="confirm-invio-btn" style="background:#1a1a2e;color:#c8a96e;border:none;padding:.6rem 1.5rem;border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;font-family:inherit">📤 Invia</button>
      </div>
    </div>
  </div>
</div>
{{-- MODAL INVIO APPROVAZIONE --}}
<div id="invio-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem">
  <div style="background:#fff;border-radius:14px;max-width:480px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden">
    <div style="background:#1a1a2e;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between">
      <div style="color:#c8a96e;font-family:monospace;font-size:.75rem;letter-spacing:.15em">📤 INVIATO PER APPROVAZIONE</div>
      <button onclick="document.getElementById('invio-modal').style.display='none'" style="background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer">×</button>
    </div>
    <div style="padding:1.5rem">
      <div style="background:#f0faf5;border:1px solid #3a7a5a;border-radius:8px;padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem">
        <span style="font-size:1.5rem">✅</span>
        <div>
          <div style="font-size:.88rem;font-weight:600;color:#1a1a2e">Richiesta inviata con successo</div>
          <div style="font-size:.75rem;color:#8a7f72;margin-top:.15rem">La famiglia riceverà il link per visualizzare e approvare</div>
        </div>
      </div>
      <div id="invio-modal-email-row" style="margin-bottom:1rem">
        <div style="font-size:.72rem;color:#888;margin-bottom:.25rem">EMAIL INVIATA A</div>
        <div id="invio-modal-email" style="font-size:.85rem;font-weight:500;color:#1a1a2e"></div>
      </div>
      <div style="margin-bottom:1.25rem">
        <div style="font-size:.72rem;color:#888;margin-bottom:.25rem">LINK APPROVAZIONE</div>
        <a id="invio-modal-link" href="#" target="_blank" style="font-size:.75rem;color:#0f3460;word-break:break-all"></a>
      </div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a id="invio-modal-wa" href="#" target="_blank" style="display:none;align-items:center;gap:.5rem;background:#25d366;color:#fff;padding:.6rem 1.1rem;border-radius:7px;text-decoration:none;font-size:.85rem;font-weight:600">📱 Apri WhatsApp</a>
        <button onclick="navigator.clipboard.writeText(document.getElementById('invio-modal-link').href);this.textContent='✅ Copiato!'" style="background:#f5f0eb;border:1px solid #e0dbd4;color:#1a1a2e;padding:.6rem 1.1rem;border-radius:7px;font-size:.85rem;cursor:pointer;font-family:inherit">📋 Copia link</button>
        <button onclick="document.getElementById('invio-modal').style.display='none'" style="background:#1a1a2e;color:#fff;border:none;padding:.6rem 1.1rem;border-radius:7px;font-size:.85rem;cursor:pointer;font-family:inherit">Chiudi</button>
      </div>
    </div>
  </div>
</div>
{{-- @include('components.chatbox-memorai') — componente memoraiengine assente nell'e-shop (Fase 2) --}}
</body>
</html>