<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="google" content="notranslate">
<title>Designer Storia Social | MemorAI</title>
{{-- Font self-hosted (GDPR): niente Google Fonts da CDN, stesso foglio degli altri designer. --}}
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
{{-- Fabric.js self-hosted: niente cdnjs/jsdelivr (a differenza della sorgente MemorAI). --}}
<script src="/vendor/libs/fabric.min.js"></script>
<style>
:root{
  --ink:#1a1a2e;--gold:#c8a96e;--cream:#f5f0e8;--cream-dark:#ede6d8;
  --white:#fdfaf5;--gray:#8a7f72;--border:#ddd8d0;--green:#3a7a5a;--red:#c44b3a;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);height:100vh;overflow:hidden;display:flex;flex-direction:column}
nav{background:var(--ink);padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;flex-shrink:0;z-index:100;flex-wrap:wrap;gap:.4rem}
.logo{color:#fff;font-size:1rem;font-weight:600;font-family:'Cormorant Garamond',serif}
.nav-links{display:flex;align-items:center;gap:.5rem}
.nav-btn{padding:.4rem .9rem;border-radius:6px;font-size:.8rem;font-weight:500;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none}
.btn-gold{background:var(--gold);color:#fff}
.btn-ghost{background:rgba(255,255,255,.1);color:#fff}
.btn-ghost:hover{background:rgba(255,255,255,.2)}
.btn-green{background:var(--green);color:#fff}

.designer-layout{display:flex;flex:1;overflow:hidden;min-height:0}
.sidebar{width:280px;background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0;min-height:0}
.canvas-area{flex:1;display:flex;align-items:center;justify-content:center;background:#2a2a3a;overflow:auto;position:relative;min-height:0}
.props-panel{width:260px;background:var(--white);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0;min-height:0}

.panel-title{font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);padding:.75rem 1rem .5rem;border-bottom:1px solid var(--border);font-weight:500}
.acc{flex-shrink:0}
.acc>summary{display:flex;align-items:center;gap:.4rem;cursor:pointer;list-style:none;user-select:none}
.acc>summary::-webkit-details-marker{display:none}
.acc>summary:hover{background:rgba(200,169,110,.07)}
.acc>summary .acc-lbl{flex:1}
.acc>summary .acc-arrow{font-size:.7rem;opacity:.7;transition:transform .15s;line-height:1}
.acc[open]>summary .acc-arrow{transform:rotate(180deg)}

.template-grid,.libreria-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;padding:.75rem}
.template-thumb{border:2px solid var(--border);border-radius:6px;overflow:hidden;cursor:pointer;transition:all .2s;aspect-ratio:9/16;background:#f0ece4;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--gray);text-align:center;padding:.5rem;position:relative}
.template-thumb:hover{border-color:var(--gold)}
.template-thumb img{width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0}
.template-label{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.6);color:#fff;font-size:.6rem;padding:3px;text-align:center}
.libreria-item{border:1px solid var(--border);border-radius:6px;overflow:hidden;cursor:pointer;aspect-ratio:1;background:#f0ece4;display:flex;align-items:center;justify-content:center}
.libreria-item:hover{border-color:var(--gold)}
.libreria-item img{width:100%;height:100%;object-fit:contain}
.libreria-cats{display:flex;flex-wrap:wrap;gap:.3rem;padding:.5rem .75rem 0}
.libreria-cat-btn{font-size:.65rem;padding:.2rem .5rem;border:1px solid var(--border);border-radius:10px;background:var(--cream);cursor:pointer;font-family:'DM Sans',sans-serif}
.libreria-cat-btn.active{background:var(--ink);color:#fff;border-color:var(--ink)}

.blocks-list{padding:.5rem}
.block-btn{width:100%;padding:.6rem .85rem;border:1px solid var(--border);border-radius:6px;margin-bottom:.4rem;cursor:pointer;font-size:.8rem;text-align:left;background:var(--cream);color:var(--ink);font-family:'DM Sans',sans-serif;display:flex;align-items:center;gap:.5rem;transition:all .15s}
.block-btn:hover{border-color:var(--gold);background:rgba(200,169,110,.05)}
.block-icon{font-size:1rem;width:20px;text-align:center}

.simbolo-btn{width:100%;aspect-ratio:1;border:1px solid var(--border);border-radius:6px;background:var(--cream);cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;transition:all .15s}
.simbolo-btn:hover{border-color:var(--gold);background:rgba(200,169,110,.1)}

.upload-area{padding:.75rem;border-top:1px solid var(--border)}
.upload-btn{width:100%;padding:.6rem;border:2px dashed var(--border);border-radius:8px;background:none;color:var(--gray);font-size:.8rem;cursor:pointer;font-family:'DM Sans',sans-serif;text-align:center;transition:all .2s}
.upload-btn:hover{border-color:var(--gold);color:var(--gold)}

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

.canvas-wrapper{position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
canvas{display:block}

.zoom-btn{width:26px;height:26px;border-radius:5px;background:rgba(255,255,255,.15);color:#fff;border:none;font-size:.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.zoom-btn:hover{background:rgba(255,255,255,.25)}
.zoom-label{background:rgba(255,255,255,.15);color:#fff;border-radius:6px;padding:0 .6rem;font-size:.75rem;display:flex;align-items:center}
.tool-btn{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.15);border-radius:4px;padding:.25rem .5rem;font-size:.72rem;cursor:pointer;font-family:'DM Sans',sans-serif;white-space:nowrap}
.tool-btn:hover{background:rgba(200,169,110,.3);border-color:var(--gold)}
.tool-btn.icon{width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.toolbar{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;row-gap:.4rem}
.tool-group{display:flex;align-items:center;gap:.3rem;flex-wrap:nowrap}
.tool-sep{width:1px;height:20px;background:rgba(255,255,255,.15);flex-shrink:0}
.tool-label{color:rgba(255,255,255,.4);font-size:.65rem;letter-spacing:.08em;text-transform:uppercase;margin-right:.35rem;flex-shrink:0}
.align-ico{display:inline-flex;gap:2px;width:15px;height:13px;flex-shrink:0}
.align-ico i{flex:1;background:rgba(255,255,255,.25);border-radius:1px;display:block}
.align-ico i.on{background:#fff}
.align-ico.stack{flex-direction:column;width:13px;height:15px}
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
{{-- Dentro l'overlay iframe (vedi partials.designer-overlay) il link "torna
     indietro" navigherebbe l'iframe stesso, confuso — si chiude con la ×
     dell'overlay. Selettore sul tag <a>: .nav-btn.btn-ghost è condivisa
     anche da bottoni funzionali (es. Pulisci). --}}
<style>body.in-iframe a.nav-btn.btn-ghost { display: none; }</style>
<script>if (window !== window.top) document.body.classList.add('in-iframe');</script>
<script>
// Rifletti oggetto (bottoni Flip H/V della toolbar), definita prima del
// canvas per leggere sempre window.canvas — stesso pattern del Designer
// Manifesti.
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
  <div class="logo">MemorAI — Designer Storia Social</div>
  <div style="display:flex;align-items:center;gap:.3rem">
    <button class="zoom-btn" onclick="setZoom(-0.1)" title="Riduci">−</button>
    <div class="zoom-label" id="zoom-label">100%</div>
    <button class="zoom-btn" onclick="setZoom(0.1)" title="Ingrandisci">+</button>
    <button class="zoom-btn" onclick="resetZoom()" title="Adatta alla finestra">⌂</button>
  </div>
  <div class="nav-links">
    <span style="color:rgba(255,255,255,.6);font-size:.8rem">{{ $defunto->nomeCompleto() }}</span>
    <button class="nav-btn btn-ghost" onclick="clearCanvas()">🗑 Pulisci</button>
    @if(auth()->user()->eStaff() || auth()->user()->agenzia)
    <button class="nav-btn" style="background:#8a5c2e;color:#fff" onclick="saveAsTemplate()">📌 Salva come template</button>
    @endif
    <button class="nav-btn btn-gold" onclick="exportPNG()">📥 Esporta PNG</button>
    <button class="nav-btn btn-green" onclick="salvaStoria()">💾 Salva</button>
    <a href="{{ route('defunti.show', $defunto) }}" class="nav-btn btn-ghost">← Scheda defunto</a>
  </div>
</nav>

<div class="designer-layout">

  <div class="sidebar">
    <details class="acc" id="acc-libreria" open>
      <summary class="panel-title"><span class="acc-lbl">Libreria MemorAI</span><span class="acc-arrow">▾</span></summary>
      <div class="libreria-cats" id="libreria-cats"></div>
      <div class="libreria-grid" id="libreria-grid">
        <div style="grid-column:1/-1;color:var(--gray);font-size:.72rem;font-style:italic;padding:.5rem">Caricamento libreria condivisa…</div>
      </div>
    </details>

    <details class="acc" id="acc-blocchi" open>
      <summary class="panel-title"><span class="acc-lbl">Blocchi Testo</span><span class="acc-arrow">▾</span></summary>
      <div class="blocks-list">
        <button class="block-btn" onclick="addBlock('nome')"><span class="block-icon">👤</span>Nome</button>
        <button class="block-btn" onclick="addBlock('date')"><span class="block-icon">📅</span>Date</button>
        <button class="block-btn" onclick="addBlock('frase')"><span class="block-icon">✨</span>Frase</button>
        <button class="block-btn" onclick="addBlock('eta')"><span class="block-icon">🔢</span>Età (anni vissuti)</button>
        <button class="block-btn" onclick="addBlock('agenzia')"><span class="block-icon">🏢</span>Agenzia</button>
        <button class="block-btn" onclick="addBlock('testo')"><span class="block-icon">📝</span>Testo libero</button>
      </div>
    </details>

    <details class="acc" id="acc-curvo">
      <summary class="panel-title"><span class="acc-lbl">Testo su tracciato</span><span class="acc-arrow">▾</span></summary>
      <div class="blocks-list">
        <button class="block-btn" onclick="addTestoCurvo('arco_su')"><span class="block-icon">⌢</span>Arco verso l'alto</button>
        <button class="block-btn" onclick="addTestoCurvo('arco_giu')"><span class="block-icon">⌣</span>Arco verso il basso</button>
        <button class="block-btn" onclick="addTestoCurvo('cerchio')"><span class="block-icon">◯</span>Cerchio completo</button>
        <button class="block-btn" onclick="addTestoCurvo('onda')"><span class="block-icon">〜</span>Onda</button>
      </div>
    </details>

    <details class="acc" id="acc-divisori">
      <summary class="panel-title"><span class="acc-lbl">Divisori</span><span class="acc-arrow">▾</span></summary>
      <div style="padding:.5rem">
        <button class="block-btn" onclick="addDivisore('linea')"><span class="block-icon">➖</span>Linea sottile</button>
        <button class="block-btn" onclick="addDivisore('rombo')"><span class="block-icon">◇</span>Linea con rombo</button>
        <button class="block-btn" onclick="addDivisore('foglie')"><span class="block-icon">🌿</span>Ramo / foglie</button>
        <button class="block-btn" onclick="addDivisore('fregio')"><span class="block-icon">❦</span>Fregio classico</button>
        <button class="block-btn" onclick="addDivisore('croce')"><span class="block-icon">✝</span>Croce ornata</button>
      </div>
    </details>

    <details class="acc" id="acc-simboli">
      <summary class="panel-title"><span class="acc-lbl">Simboli Religiosi</span><span class="acc-arrow">▾</span></summary>
      <div style="padding:.5rem">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.35rem">
        <button class="simbolo-btn" onclick="insertSimbolo('croce_latina')" title="Croce Latina">✝</button>
        <button class="simbolo-btn" onclick="insertSimbolo('croce_greca')" title="Croce Greca">✚</button>
        <button class="simbolo-btn" onclick="insertSimbolo('croce_ortodossa')" title="Croce Ortodossa">☦</button>
        <button class="simbolo-btn" onclick="insertSimbolo('stella')" title="Stella">✦</button>
        <button class="simbolo-btn" onclick="insertSimbolo('omega')" title="Omega">Ω</button>
        <button class="simbolo-btn" onclick="insertSimbolo('giglio')" title="Giglio">⚜</button>
        <button class="simbolo-btn" onclick="insertSimbolo('colomba')" title="Colomba">🕊</button>
        <button class="simbolo-btn" onclick="insertSimbolo('infinito')" title="Infinito">∞</button>
        </div>
      </div>
    </details>

    <details class="acc" id="acc-template">
      <summary class="panel-title"><span class="acc-lbl">Template</span><span class="acc-arrow">▾</span></summary>
      <div id="saved-templates-list" style="padding:.4rem">
        <div style="color:var(--gray);font-size:.75rem;font-style:italic;padding:.4rem">Caricamento...</div>
      </div>
    </details>
  </div>

  <div style="display:flex;flex-direction:column;flex:1;overflow:hidden">
  <div class="toolbar" style="background:#1e1e2e;border-bottom:1px solid rgba(255,255,255,.1);padding:.4rem 1rem;flex-shrink:0">
    <div class="tool-group">
      <button id="btn-undo" onclick="undoAction()" title="Annulla (Ctrl+Z)" class="tool-btn icon" disabled>↩</button>
      <button id="btn-redo" onclick="redoAction()" title="Ripristina (Ctrl+Y)" class="tool-btn icon" disabled>↪</button>
    </div>
    <div class="tool-sep"></div>
    <div class="tool-group">
      <button id="btn-guide" onclick="toggleGuide()" title="Linee guida" class="tool-btn">⊹ Guide</button>
    </div>
    <div class="tool-sep"></div>
    <div class="tool-group">
      <span class="tool-label">Allinea</span>
      <button class="tool-btn icon" onclick="alignObjects('left')" title="Allinea a sinistra"><span class="align-ico"><i class="on"></i><i></i><i></i></span></button>
      <button class="tool-btn icon" onclick="alignObjects('centerH')" title="Centra orizzontalmente"><span class="align-ico"><i></i><i class="on"></i><i></i></span></button>
      <button class="tool-btn icon" onclick="alignObjects('right')" title="Allinea a destra"><span class="align-ico"><i></i><i></i><i class="on"></i></span></button>
      <button class="tool-btn icon" onclick="alignObjects('top')" title="Allinea in alto"><span class="align-ico stack"><i class="on"></i><i></i><i></i></span></button>
      <button class="tool-btn icon" onclick="alignObjects('centerV')" title="Centra verticalmente"><span class="align-ico stack"><i></i><i class="on"></i><i></i></span></button>
      <button class="tool-btn icon" onclick="alignObjects('bottom')" title="Allinea in basso"><span class="align-ico stack"><i></i><i></i><i class="on"></i></span></button>
    </div>
    <div class="tool-sep"></div>
    <div class="tool-group">
      <button class="tool-btn" onclick="distributeObjects('h')" title="Distribuisci orizzontalmente">↔ Dist H</button>
      <button class="tool-btn" onclick="distributeObjects('v')" title="Distribuisci verticalmente">↕ Dist V</button>
    </div>
    <div class="tool-sep"></div>
    <div class="tool-group">
      <button class="tool-btn icon" onclick="bringForward()" title="Porta avanti">▲</button>
      <button class="tool-btn icon" onclick="sendBackward()" title="Porta indietro">▼</button>
    </div>
    <div class="tool-sep"></div>
    <div class="tool-group">
      <span class="tool-label">Foto</span>
      <button class="tool-btn" onclick="document.getElementById('foto-upload').click()">📷 Inserisci Foto</button>
      <input type="file" id="foto-upload" accept="image/*" style="display:none" onchange="insertPhoto(this)">
      <button class="tool-btn" id="btn-foto-pratica" onclick="inserisciFotoPrincipale()"
              style="display:inline-flex;align-items:center;gap:.4rem"
              @if(!($fotoPrincipale ?? null)) disabled title="Nessuna foto ancora caricata" @endif>
        @if($fotoPrincipale ?? null)
          <img src="{{ $fotoPrincipale }}" style="width:22px;height:28px;object-fit:cover;border-radius:2px;border:1px solid rgba(255,255,255,.3)">
        @else
          🖼
        @endif
        Foto del defunto
      </button>
    </div>
    <div class="tool-sep"></div>
    <div class="tool-group">
      <button class="tool-btn" onclick="flipOggetto('X')" title="Rifletti orizzontale">↔ Flip H</button>
      <button class="tool-btn" onclick="flipOggetto('Y')" title="Rifletti verticale">↕ Flip V</button>
    </div>
  </div>
  <div class="canvas-area" id="canvas-area">
    <div class="canvas-wrapper" id="canvas-wrapper">
      <canvas id="storia-canvas"></canvas>
    </div>
  </div>
  </div>

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
      <div class="prop-group" id="curvo-group" style="display:none">
        <span class="prop-label">Larghezza curva</span>
        <input type="range" id="prop-curvo-ampiezza" min="200" max="1000" step="20" value="700" oninput="aggiornaTracciato('ampiezzaTracciato', this.value)" style="width:100%">
        <span class="prop-label" style="margin-top:.5rem">Curvatura</span>
        <input type="range" id="prop-curvo-curvatura" min="20" max="400" step="10" value="180" oninput="aggiornaTracciato('curvaturaTracciato', this.value)" style="width:100%">
      </div>
      <div class="prop-group">
        <span class="prop-label">Ombra</span>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;margin-bottom:.4rem">
          <input type="checkbox" id="prop-ombra-attiva" onchange="updateOmbra()"> Attiva
        </label>
        <div id="ombra-controlli" style="display:none">
          <div class="prop-row" style="align-items:center;margin-bottom:.4rem">
            <input type="color" class="color-swatch" id="prop-ombra-colore" value="#000000" oninput="updateOmbra()">
            <input type="range" id="prop-ombra-blur" min="0" max="40" step="1" value="8" oninput="updateOmbra()" title="Sfocatura" style="flex:1">
          </div>
          <div class="prop-row">
            <input type="range" id="prop-ombra-offx" min="-30" max="30" step="1" value="4" oninput="updateOmbra()" title="Spostamento X">
            <input type="range" id="prop-ombra-offy" min="-30" max="30" step="1" value="4" oninput="updateOmbra()" title="Spostamento Y">
          </div>
        </div>
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
</div>

{{-- Modale propria, al posto di prompt/confirm/alert — identica agli altri designer. --}}
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
// ── DATI ──
const praticaData = @json($praticaData ?? []);
const agenziaData = @json($agenziaData ?? []);
const savedCanvas = @json($storia->canvas);
const fotoPrincipale = @json($fotoPrincipale ?? null);
const storiaToken = @json($storia->token);
// Nome dell'export manuale: servizio + codice univoco, lo stesso del file
// scaricato dal link pubblico. Estensione forzata a png: qui si esporta il
// canvas, non il jpeg dell'anteprima salvata.
const storiaNomeFile = @json($storia->nomeFile('png'));
const csrfToken = '{{ csrf_token() }}';

// Allega CSRF e sessione a tutte le chiamate /admin/api/: stesso schema di
// manifesti/necrologi, gli endpoint sono protetti dall'auth di sessione.
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

// ── FORMATO FISSO (Fase 1: un solo fotogramma 1080x1920, come le storie FB/IG) ──
const CANVAS_W = 1080;
const CANVAS_H = 1920;
let zoom = 1;
let canvas;

window.onload = function() {
  fabric.Object.prototype.set({
    cornerSize: 14, cornerColor: '#ffffff', cornerStrokeColor: '#1a1a2e',
    cornerStyle: 'circle', transparentCorners: false,
    borderColor: '#c8a96e', borderScaleFactor: 2, padding: 4
  });
  if (fabric.IText && fabric.IText.prototype.initHiddenTextarea) {
    const _orig = fabric.IText.prototype.initHiddenTextarea;
    fabric.IText.prototype.initHiddenTextarea = function () {
      _orig.call(this);
      const ta = this.hiddenTextarea;
      if (ta && !ta.__noScrollChase) {
        const _focus = ta.focus.bind(ta);
        ta.focus = function (opt) { _focus(Object.assign({}, opt || {}, { preventScroll: true })); };
        ta.__noScrollChase = true;
      }
    };
  }

  canvas = window.canvas = new fabric.Canvas('storia-canvas', {
    width: CANVAS_W, height: CANVAS_H, backgroundColor: '#ffffff', preserveObjectStacking: true,
  });

  if (savedCanvas && savedCanvas.objects && savedCanvas.objects.length) {
    canvas.loadFromJSON(savedCanvas, function () { canvas.renderAll(); refreshLayers(); });
  }

  autoZoom();
  caricaLibreria();
  loadSavedTemplates();

  canvas.on('object:added', function() { saveState(); refreshLayers(); });
  canvas.on('object:modified', function() { saveState(); refreshLayers(); });
  canvas.on('object:removed', function(e) {
    var cornice = e.target && trovaCornice(e.target);
    if (cornice) canvas.remove(cornice);
    saveState(); refreshLayers();
  });
  setTimeout(function(){ saveState(); }, 800);

  ['object:moving', 'object:scaling', 'object:rotating'].forEach(function (evento) {
    canvas.on(evento, function (e) { if (e.target) sincronizzaCornice(e.target); });
  });

  canvas.on('selection:created', updatePropsPanel);
  canvas.on('selection:updated', updatePropsPanel);
  canvas.on('selection:cleared', clearPropsPanel);
  canvas.on('object:modified', updatePropsPanel);
  canvas.on('selection:created', refreshLayers);
  canvas.on('selection:updated', refreshLayers);
  canvas.on('selection:cleared', refreshLayers);
  setTimeout(refreshLayers, 900);
  canvas.on('text:changed', function(e) { document.getElementById('prop-text').value = e.target.text; });

  initAccordion();
};

// ── SEZIONI SIDEBAR (fisarmonica, stato in localStorage) ──
const ACC_KEY = 'storia-designer:sezioni';
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
  var wrapper = document.getElementById('canvas-area');
  var disponibileH = wrapper.clientHeight - 60;
  zoom = Math.max(0.2, Math.min(0.9, disponibileH / CANVAS_H));
  applyZoom();
}
function setZoom(delta) { window._zoomManuale = true; zoom = Math.max(0.2, Math.min(2, zoom + delta)); applyZoom(); }
function resetZoom() { window._zoomManuale = false; autoZoom(); }
window.addEventListener('resize', function() { if (window._zoomManuale) return; clearTimeout(window._rzTimer); window._rzTimer = setTimeout(autoZoom, 150); });
function applyZoom() {
  const wrapper = document.getElementById('canvas-wrapper');
  wrapper.style.transform = `scale(${zoom})`;
  wrapper.style.transformOrigin = 'center center';
  document.getElementById('zoom-label').textContent = Math.round(zoom * 100) + '%';
}

// ── LIBRERIA GRAFICA CONDIVISA (cdn.memoraiengine.com, sola lettura, pubblica) ──
// Nessun catalogo duplicato: si legge dal vivo, stessa decisione già presa
// per Tribute (vedi brief-designer-storia-per-tribute.md). Se il CDN non
// risponde, la sezione resta vuota senza bloccare il resto del designer.
const CDN_MEMORAI = 'https://cdn.memoraiengine.com';
let LIBRERIA = { categorie: [], assets: [] };
function caricaLibreria() {
  fetch(CDN_MEMORAI + '/assets/libreria/index.json')
    .then(function(r){ if (!r.ok) throw new Error('http ' + r.status); return r.json(); })
    .then(function(dati){
      LIBRERIA = dati;
      var cats = document.getElementById('libreria-cats');
      cats.innerHTML = (dati.categorie || []).map(function(c, i){
        return '<button class="libreria-cat-btn' + (i===0?' active':'') + '" onclick="renderLibreria(\'' + c + '\', this)">' + c + '</button>';
      }).join('');
      renderLibreria((dati.categorie || [])[0] || null);
    })
    .catch(function(){
      document.getElementById('libreria-grid').innerHTML = '<div style="grid-column:1/-1;color:var(--gray);font-size:.7rem;font-style:italic;padding:.5rem">Libreria condivisa non raggiungibile al momento.</div>';
    });
}
function renderLibreria(categoria, btn) {
  if (btn) {
    document.querySelectorAll('.libreria-cat-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
  }
  var grid = document.getElementById('libreria-grid');
  var items = (LIBRERIA.assets || []).filter(function(a){ return !categoria || a.categoria === categoria; });
  if (!items.length) { grid.innerHTML = '<div style="grid-column:1/-1;color:var(--gray);font-size:.7rem;font-style:italic;padding:.5rem">Nessun elemento in questa categoria.</div>'; return; }
  grid.innerHTML = items.map(function(a){
    var nome = (a.nome||'').replace(/"/g,'&quot;');
    var url = (a.url||'').replace(/"/g,'&quot;');
    var tipo = (a.tipo||'').replace(/"/g,'&quot;');
    return '<div class="libreria-item" data-url="' + url + '" data-tipo="' + tipo + '" title="' + nome + '"><img src="' + CDN_MEMORAI + a.url + '" loading="lazy"></div>';
  }).join('');
}
// Delegato invece di onclick inline: JSON.stringify(a.url) produceva virgolette
// doppie identiche a quelle dell'attributo onclick="...", troncando l'handler
// a metà (SyntaxError: Unexpected end of input) e impedendo il click di funzionare.
document.getElementById('libreria-grid').addEventListener('click', function(e){
  var item = e.target.closest('.libreria-item');
  if (!item) return;
  addAssetLibreria(item.dataset.url, item.dataset.tipo);
});
function addAssetLibreria(url, tipo) {
  var full = CDN_MEMORAI + url;
  if (tipo === 'vettoriale' || /\.svg$/i.test(full)) {
    fabric.loadSVGFromURL(full, function(objects, options) {
      var obj = fabric.util.groupSVGElements(objects, options);
      obj.set({ left: CANVAS_W/2, top: CANVAS_H/2, originX: 'center', originY: 'center' });
      var target = CANVAS_W * 0.5;
      if (obj.width) obj.scaleToWidth(target);
      canvas.add(obj); canvas.setActiveObject(obj); canvas.renderAll();
    });
  } else {
    fabric.Image.fromURL(full, function(img) {
      var maxW = CANVAS_W * 0.6, maxH = CANVAS_H * 0.4;
      var scale = Math.min(maxW / img.width, maxH / img.height);
      img.set({
        left: CANVAS_W/2 - (img.width*scale)/2, top: CANVAS_H/2 - (img.height*scale)/2,
        scaleX: scale, scaleY: scale, selectable: true, hasControls: true, hasBorders: true,
      });
      canvas.add(img); canvas.setActiveObject(img); canvas.renderAll();
    }, { crossOrigin: 'anonymous' });
  }
}

// ── DIVISORI (stessi SVG del Designer Manifesti) ──
var DIVISORI_SVG = {
  rombo: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 24"><line x1="10" y1="12" x2="180" y2="12" stroke="#000" stroke-width="1.5"/><path d="M200 4 L208 12 L200 20 L192 12 Z" fill="#000"/><line x1="220" y1="12" x2="390" y2="12" stroke="#000" stroke-width="1.5"/></svg>',
  foglie: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 40"><line x1="20" y1="20" x2="380" y2="20" stroke="#000" stroke-width="1"/><path d="M200 20 q-15 -12 -30 -4 q12 8 30 4 q15 -12 30 -4 q-12 8 -30 4Z" fill="#000"/><circle cx="165" cy="20" r="2.5" fill="#000"/><circle cx="235" cy="20" r="2.5" fill="#000"/></svg>',
  fregio: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 30"><path d="M10 15 h150 q10 0 15 -8 q5 8 15 8 q10 0 15 -8 q5 8 15 8 h150" fill="none" stroke="#000" stroke-width="1.5"/><circle cx="200" cy="7" r="3" fill="#000"/></svg>',
  croce: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 40"><line x1="10" y1="20" x2="175" y2="20" stroke="#000" stroke-width="1"/><path d="M196 8 h8 v8 h8 v8 h-8 v8 h-8 v-8 h-8 v-8 h8 Z" fill="#000"/><line x1="225" y1="20" x2="390" y2="20" stroke="#000" stroke-width="1"/></svg>'
};
function addDivisore(tipo){
  if (tipo === 'linea') {
    var ln = new fabric.Line([0,0,CANVAS_W-160,0], {left:80, top:400, stroke:'#1a1a2e', strokeWidth:1.5, customType:'divisore'});
    canvas.add(ln); canvas.setActiveObject(ln); canvas.renderAll(); return;
  }
  var svg = DIVISORI_SVG[tipo];
  if (!svg) return;
  fabric.loadSVGFromString(svg, function(objects, options){
    var obj = fabric.util.groupSVGElements(objects, options);
    obj.set({left:80, top:400, customType:'divisore'});
    if (obj.width) obj.scaleToWidth(CANVAS_W - 160);
    canvas.add(obj); canvas.setActiveObject(obj); canvas.renderAll();
  });
}

// ── SIMBOLI RELIGIOSI ──
function insertSimbolo(tipo) {
  var simboli = { croce_latina:'✝', croce_greca:'✚', croce_ortodossa:'☦', stella:'✦', omega:'Ω', giglio:'⚜', colomba:'🕊', infinito:'∞' };
  var char = simboli[tipo] || '✝';
  var obj = new fabric.Text(char, {
    left: CANVAS_W/2, top: CANVAS_H/2, fontSize: Math.round(CANVAS_W*0.12),
    fontFamily: 'serif', fill: '#1a1a2e', originX: 'center', originY: 'center', editable: false,
  });
  canvas.add(obj); canvas.setActiveObject(obj); canvas.renderAll();
}

// ── TESTO SU TRACCIATO (arco/cerchio/onda) ──
// Porting diretto dell'algoritmo di storia-designer.blade.php (MemorAI
// Engine): un path SVG puro, indipendente dal backend. Fabric.js supporta
// `path` su fabric.Text dalla stessa versione già in uso in questo repo
// (5.3), non serve la 6.x della sorgente.
function costruisciPath(tipo, ampiezza, curvatura) {
  var A = ampiezza || 700, C = curvatura || 180;
  if (tipo === 'arco_su')  return 'M 0 ' + C + ' Q ' + (A/2) + ' ' + (-C) + ' ' + A + ' ' + C;
  if (tipo === 'arco_giu') return 'M 0 0 Q ' + (A/2) + ' ' + (C*2) + ' ' + A + ' 0';
  if (tipo === 'cerchio') { var rr = A/2; return 'M 0 ' + rr + ' a ' + rr + ' ' + rr + ' 0 1 1 ' + (rr*2) + ' 0 a ' + rr + ' ' + rr + ' 0 1 1 ' + (-rr*2) + ' 0'; }
  if (tipo === 'onda') { var q = A/4; return 'M 0 ' + C + ' Q ' + q + ' 0 ' + (q*2) + ' ' + C + ' T ' + A + ' ' + C; }
  return 'M 0 0 L ' + A + ' 0';
}
function addTestoCurvo(tipo) {
  var ampiezza = 700, curvatura = 180;
  var p = new fabric.Path(costruisciPath(tipo, ampiezza, curvatura), { fill: '', stroke: '', objectCaching: false });
  var t = new fabric.Text('IN MEMORIA', {
    path: p, left: CANVAS_W/2, top: CANVAS_H*0.25, originX: 'center', originY: 'center',
    fontFamily: 'Cormorant Garamond', fontSize: 72, fill: '#1a1a2e',
    tipoTracciato: tipo, ampiezzaTracciato: ampiezza, curvaturaTracciato: curvatura,
  });
  canvas.add(t); canvas.setActiveObject(t); canvas.renderAll(); refreshLayers();
}
function aggiornaTracciato(campo, valore) {
  var o = canvas.getActiveObject();
  if (!o || !o.tipoTracciato) return;
  o[campo] = parseInt(valore);
  var nuovoPath = new fabric.Path(costruisciPath(o.tipoTracciato, o.ampiezzaTracciato, o.curvaturaTracciato), { fill: '', stroke: '', objectCaching: false });
  o.set('path', nuovoPath);
  if (o.initDimensions) o.initDimensions();
  o.dirty = true;
  canvas.renderAll();
}

// ── BLOCCHI TESTO (dati del defunto) ──
const BLOCCHI_PERSONALI = ['nome', 'date', 'frase', 'eta'];
function testoPersonale(tipo, dati) {
  var d = dati || {};
  switch (tipo) {
    case 'nome':  return (d.cognome || 'COGNOME') + ' ' + (d.nome || 'Nome');
    case 'date':  return (d.data_nascita || '__/__/____') + ' — ' + (d.data_morte || '__/__/____');
    case 'frase': return d.frase || 'È mancato all\'affetto dei suoi cari';
    case 'eta':   return (d.anni !== null && d.anni !== undefined && d.anni !== '') ? 'di anni ' + Math.floor(d.anni) : 'di anni ___';
  }
  return '';
}
function addBlock(type) {
  var text = '', fontSize = 50, fontWeight = 'normal', fill = '#1a1a2e';
  switch(type) {
    case 'nome':  text = testoPersonale('nome', praticaData); fontSize = 90; fontWeight = 'bold'; break;
    case 'date':  text = testoPersonale('date', praticaData); fontSize = 44; break;
    case 'frase': text = testoPersonale('frase', praticaData); fontSize = 52; break;
    case 'eta':   text = testoPersonale('eta', praticaData); fontSize = 40; break;
    case 'agenzia': text = agenziaData.name || 'Nome Agenzia'; fontSize = 32; fill = '#8a7f72'; break;
    case 'testo': text = 'Testo libero...'; fontSize = 40; break;
  }

  var selezionato = canvas.getActiveObject();
  if (selezionato && selezionato.customBlockType === type) {
    selezionato.set('text', text);
    canvas.renderAll();
    return;
  }

  var obj = new fabric.Textbox(text, {
    left: 60, top: 200 + canvas.getObjects().length * 30, width: CANVAS_W - 120,
    fontSize: fontSize, fontFamily: 'Cormorant Garamond', fontWeight: fontWeight,
    textAlign: 'center', fill: fill, editable: true, customBlockType: type,
  });
  canvas.add(obj); canvas.setActiveObject(obj); canvas.renderAll();
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
    assicuraFont(obj.fontFamily || 'Cormorant Garamond');
    document.getElementById('prop-size').value = obj.fontSize || 20;
    document.getElementById('prop-color').value = obj.fill || '#000000';
    document.getElementById('prop-color-hex').value = obj.fill || '#000000';
    document.getElementById('btn-bold').classList.toggle('active', obj.fontWeight === 'bold');
    document.getElementById('btn-italic').classList.toggle('active', obj.fontStyle === 'italic');
    document.getElementById('btn-left').classList.toggle('active', obj.textAlign === 'left');
    document.getElementById('btn-center').classList.toggle('active', obj.textAlign === 'center');
    document.getElementById('btn-right').classList.toggle('active', obj.textAlign === 'right');
  }

  var curvoGroup = document.getElementById('curvo-group');
  if (obj.tipoTracciato) {
    curvoGroup.style.display = 'block';
    document.getElementById('prop-curvo-ampiezza').value = obj.ampiezzaTracciato || 700;
    document.getElementById('prop-curvo-curvatura').value = obj.curvaturaTracciato || 180;
  } else {
    curvoGroup.style.display = 'none';
  }

  var ombra = obj.shadow;
  document.getElementById('prop-ombra-attiva').checked = !!ombra;
  document.getElementById('ombra-controlli').style.display = ombra ? 'block' : 'none';
  if (ombra) {
    document.getElementById('prop-ombra-colore').value = rgbaToHex(ombra.color) || '#000000';
    document.getElementById('prop-ombra-blur').value = ombra.blur || 8;
    document.getElementById('prop-ombra-offx').value = ombra.offsetX || 4;
    document.getElementById('prop-ombra-offy').value = ombra.offsetY || 4;
  }

  const borderGroup = document.getElementById('border-group');
  if (obj.type === 'image') {
    borderGroup.style.display = 'block';
    const cornice = trovaCornice(obj);
    const strokeColore = cornice ? cornice.stroke : (obj.stroke || '#c8a96e');
    document.getElementById('prop-stroke-width').value = cornice ? cornice.strokeWidth : (obj.strokeWidth || 0);
    document.getElementById('prop-stroke-color').value = strokeColore;
    document.getElementById('prop-stroke-hex').value = strokeColore;
  } else {
    borderGroup.style.display = 'none';
  }
}
function clearPropsPanel() {
  document.getElementById('no-selection').style.display = 'block';
  document.getElementById('props-content').style.display = 'none';
}
function rgbaToHex(c) {
  if (!c) return null;
  if (c[0] === '#') return c;
  var m = c.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
  if (!m) return null;
  return '#' + [1,2,3].map(function(i){ return ('0'+parseInt(m[i]).toString(16)).slice(-2); }).join('');
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

// Forza il caricamento del webfont prima di disegnarlo sul canvas: senza
// questo, un font custom scelto ora renderizza col fallback finché qualcos'
// altro in pagina non lo richiede — stesso problema già noto e risolto per
// Monotype Corsiva nel Designer Manifesti, qui generalizzato a ogni font.
var _fontCache = {};
function assicuraFont(famiglia) {
  if (!famiglia || _fontCache[famiglia] || !document.fonts || !document.fonts.load) return;
  _fontCache[famiglia] = true;
  Promise.all([
    document.fonts.load("20px '" + famiglia + "'"),
    document.fonts.load("italic 20px '" + famiglia + "'"),
    document.fonts.load("bold 20px '" + famiglia + "'"),
  ]).then(function(){ canvas.renderAll(); }).catch(function(){});
}

function updateProp(prop) {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  if (prop === 'text' && (obj.type === 'textbox' || obj.type === 'text')) {
    obj.set('text', document.getElementById('prop-text').value);
  } else if (prop === 'font') {
    const font = document.getElementById('prop-font').value;
    assicuraFont(font);
    obj.set('fontFamily', font);
  } else if (prop === 'size') {
    obj.set('fontSize', parseInt(document.getElementById('prop-size').value));
  } else if (prop === 'color') {
    const c = document.getElementById('prop-color').value;
    applicaColoreObj(obj, c);
    document.getElementById('prop-color-hex').value = c;
  }
  obj.setCoords();
  canvas.renderAll();
}
function applicaColoreObj(obj, c){
  if (!obj) return;
  if (obj.type === 'group') {
    obj.forEachObject(function(o){
      if (o.stroke) o.set('stroke', c);
      if (o.fill && o.fill !== '' && o.fill !== 'transparent') o.set('fill', c);
    });
    obj.set('dirty', true);
  } else if (obj.type === 'path') {
    if (obj.stroke) obj.set('stroke', c);
    if (obj.fill && obj.fill !== '' && obj.fill !== 'transparent') obj.set('fill', c);
  } else {
    obj.set('fill', c);
  }
}
function syncColor() {
  const hex = document.getElementById('prop-color-hex').value;
  if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
    document.getElementById('prop-color').value = hex;
    const obj = canvas.getActiveObject();
    if (obj) { applicaColoreObj(obj, hex); canvas.renderAll(); }
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

// ── OMBRA (testo o immagine) ──
function updateOmbra() {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  const attiva = document.getElementById('prop-ombra-attiva').checked;
  document.getElementById('ombra-controlli').style.display = attiva ? 'block' : 'none';
  if (!attiva) { obj.set('shadow', null); canvas.renderAll(); return; }
  const colore = document.getElementById('prop-ombra-colore').value;
  const blur = parseInt(document.getElementById('prop-ombra-blur').value);
  const offX = parseInt(document.getElementById('prop-ombra-offx').value);
  const offY = parseInt(document.getElementById('prop-ombra-offy').value);
  obj.set('shadow', new fabric.Shadow({ color: colore, blur: blur, offsetX: offX, offsetY: offY }));
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
  canvas.renderAll();
}

// ── BORDO / MASCHERA FOTO (porting diretto dal Designer Manifesti) ──
function formaMaschera(kind, w, h, extra) {
  if (kind === 'oval') return new fabric.Ellipse(Object.assign({ rx: w/2, ry: h/2, originX: 'center', originY: 'center' }, extra || {}));
  if (kind === 'round') {
    var r = parseInt((document.getElementById('prop-mask-radius')||{}).value || 30);
    return new fabric.Rect(Object.assign({ width: w, height: h, rx: r, ry: r, originX: 'center', originY: 'center' }, extra || {}));
  }
  return null;
}
function generaIdCornice() { return 'ft_' + Math.random().toString(36).slice(2, 9); }
function trovaCornice(obj) {
  if (!obj || !obj.__fotoId) return null;
  return canvas.getObjects().find(function (o) { return o.isCorniceMaschera && o.__corniceDiId === obj.__fotoId; }) || null;
}
function sincronizzaCornice(obj) {
  var cornice = trovaCornice(obj);
  if (!cornice) return;
  cornice.set({ left: obj.left, top: obj.top, scaleX: obj.scaleX, scaleY: obj.scaleY, angle: obj.angle, originX: obj.originX, originY: obj.originY });
  cornice.setCoords();
}
function rimuoviCornice(obj) { var cornice = trovaCornice(obj); if (cornice) canvas.remove(cornice); }
function aggiornaCornice(obj) {
  if (!obj || obj.type !== 'image' || !obj.clipPath) { rimuoviCornice(obj); return; }
  var sw = parseInt(document.getElementById('prop-stroke-width').value) || 0;
  if (sw <= 0) { rimuoviCornice(obj); return; }
  var sc = document.getElementById('prop-stroke-color').value;
  var kind = obj.clipPath.type === 'ellipse' ? 'oval' : 'round';
  rimuoviCornice(obj);
  obj.__fotoId = obj.__fotoId || generaIdCornice();
  var cornice = formaMaschera(kind, obj.width, obj.height, {
    fill: null, stroke: sc, strokeWidth: sw, selectable: false, evented: false,
    isCorniceMaschera: true, __corniceDiId: obj.__fotoId,
  });
  canvas.add(cornice);
  sincronizzaCornice(obj);
  canvas.bringToFront(cornice);
}
function updateBorder() {
  const obj = canvas.getActiveObject();
  if (!obj) return;
  const sw = parseInt(document.getElementById('prop-stroke-width').value) || 0;
  const sc = document.getElementById('prop-stroke-color').value;
  document.getElementById('prop-stroke-hex').value = sc;
  if (obj.type === 'image' && obj.clipPath) { aggiornaCornice(obj); }
  else { obj.set({ strokeWidth: sw, stroke: sw > 0 ? sc : null }); }
  canvas.renderAll();
}
function syncStrokeColor() {
  const hex = document.getElementById('prop-stroke-hex').value;
  if (/^#[0-9A-Fa-f]{6}$/.test(hex)) { document.getElementById('prop-stroke-color').value = hex; updateBorder(); }
}
function setImageMask(kind) {
  var obj = canvas.getActiveObject();
  if (!obj || obj.type !== 'image') return;
  var radiusRow = document.getElementById('mask-radius-row');
  if (kind === 'none') { obj.clipPath = null; if (radiusRow) radiusRow.style.display = 'none'; rimuoviCornice(obj); }
  else {
    obj.clipPath = formaMaschera(kind, obj.width, obj.height);
    if (radiusRow) radiusRow.style.display = (kind === 'round') ? 'block' : 'none';
    aggiornaCornice(obj);
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
  var cornice = trovaCornice(obj);
  if (cornice) cornice.set({ rx: r, ry: r });
  canvas.renderAll();
}

// ── ALLINEAMENTO / DISTRIBUZIONE MULTIPLA (porting diretto) ──
function alignObjects(type) {
  const objs = canvas.getActiveObjects();
  if (!objs || objs.length < 2) { alert('Seleziona almeno 2 elementi'); return; }
  let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
  objs.forEach(o => { const b = o.getBoundingRect(); minX = Math.min(minX,b.left); minY = Math.min(minY,b.top); maxX = Math.max(maxX,b.left+b.width); maxY = Math.max(maxY,b.top+b.height); });
  objs.forEach(o => {
    const b = o.getBoundingRect();
    if (type === 'left') o.set('left', minX + (o.left - b.left));
    else if (type === 'right') o.set('left', maxX - b.width + (o.left - b.left));
    else if (type === 'centerH') o.set('left', (minX+maxX)/2 - b.width/2 + (o.left - b.left));
    else if (type === 'top') o.set('top', minY + (o.top - b.top));
    else if (type === 'bottom') o.set('top', maxY - b.height + (o.top - b.top));
    else if (type === 'centerV') o.set('top', (minY+maxY)/2 - b.height/2 + (o.top - b.top));
    o.setCoords();
  });
  canvas.renderAll();
}
function distributeObjects(dir) {
  const objs = canvas.getActiveObjects();
  if (!objs || objs.length < 3) { alert('Seleziona almeno 3 elementi'); return; }
  if (dir === 'h') {
    const sorted = [...objs].sort((a,b) => a.getBoundingRect().left - b.getBoundingRect().left);
    const first = sorted[0].getBoundingRect(), last = sorted[sorted.length-1].getBoundingRect();
    const totalW = sorted.reduce((s,o) => s + o.getBoundingRect().width, 0);
    const gap = (last.left + last.width - first.left - totalW) / (sorted.length - 1);
    let x = first.left;
    sorted.forEach(o => { const b = o.getBoundingRect(); o.set('left', x + (o.left - b.left)); o.setCoords(); x += b.width + gap; });
  } else {
    const sorted = [...objs].sort((a,b) => a.getBoundingRect().top - b.getBoundingRect().top);
    const first = sorted[0].getBoundingRect(), last = sorted[sorted.length-1].getBoundingRect();
    const totalH = sorted.reduce((s,o) => s + o.getBoundingRect().height, 0);
    const gap = (last.top + last.height - first.top - totalH) / (sorted.length - 1);
    let y = first.top;
    sorted.forEach(o => { const b = o.getBoundingRect(); o.set('top', y + (o.top - b.top)); o.setCoords(); y += b.height + gap; });
  }
  canvas.renderAll();
}
function bringForward() { const obj = canvas.getActiveObject(); if (obj) { canvas.bringForward(obj); canvas.renderAll(); refreshLayers(); } }
function sendBackward() { const obj = canvas.getActiveObject(); if (obj) { canvas.sendBackwards(obj); canvas.renderAll(); refreshLayers(); } }

// ── FOTO ──
function insertPhoto(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    fabric.Image.fromURL(e.target.result, function(img) {
      const maxW = CANVAS_W * 0.5, maxH = CANVAS_H * 0.35;
      const scale = Math.min(maxW / img.width, maxH / img.height);
      img.set({ left: CANVAS_W/2 - (img.width*scale)/2, top: CANVAS_H/2 - (img.height*scale)/2, scaleX: scale, scaleY: scale, selectable: true, hasControls: true, hasBorders: true, customType: 'photo' });
      canvas.add(img); canvas.setActiveObject(img); canvas.renderAll();
    });
  };
  reader.readAsDataURL(input.files[0]);
  input.value = '';
}
function inserisciFotoPrincipale() {
  if (!fotoPrincipale) return;
  fabric.Image.fromURL(fotoPrincipale, function(img) {
    const maxW = CANVAS_W * 0.5, maxH = CANVAS_H * 0.35;
    const scale = Math.min(maxW / img.width, maxH / img.height);
    img.set({ left: CANVAS_W/2 - (img.width*scale)/2, top: CANVAS_H/2 - (img.height*scale)/2, scaleX: scale, scaleY: scale, selectable: true, hasControls: true, hasBorders: true, customType: 'photo' });
    canvas.add(img); canvas.setActiveObject(img); canvas.renderAll();
  }, { crossOrigin: 'anonymous' });
}

// ── EXPORT (client-side) ──
function exportPNG() {
  const dataURL = canvas.toDataURL({ format: 'png', quality: 1, multiplier: 2 });
  const a = document.createElement('a');
  a.href = dataURL; a.download = storiaNomeFile; a.click();
}

// ── SALVATAGGIO ──
const CUSTOM_PROPS = ['customType', 'customBlockType', '__fotoId', 'isCorniceMaschera', '__corniceDiId', 'tipoTracciato', 'ampiezzaTracciato', 'curvaturaTracciato'];

async function salvaStoria() {
  const btn = event.target;
  const testoOriginale = btn.textContent;
  btn.textContent = '⏳...'; btn.disabled = true;

  const anteprima = canvas.toDataURL({ format: 'jpeg', quality: 0.85, multiplier: 1 });

  try {
    const res = await fetch('/admin/api/storie-social/' + storiaToken + '/salva', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ canvas: JSON.stringify(canvas.toJSON(CUSTOM_PROPS)), anteprima: anteprima }),
    });
    const data = await res.json();
    if (data.success) { toastMsg('✓ Storia salvata'); }
    else { modale({ titolo: 'Salvataggio non riuscito', testo: data.error || 'Riprova.' }); }
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

// ── TEMPLATE (letti da chiunque, scritti da staff/agenzie) ──
let templateCorrente = null;
function layoutTemplateJSON(c) {
  const data = JSON.parse(JSON.stringify(c.toJSON(CUSTOM_PROPS)));
  data.objects = (data.objects || []).filter(o => o.customType !== 'photo' && !o.isCorniceMaschera);
  data.objects.forEach(o => {
    if (BLOCCHI_PERSONALI.indexOf(o.customBlockType) !== -1) { o.text = testoPersonale(o.customBlockType, null); o.styles = {}; }
  });
  return data;
}
function anteprimaTemplate(json, cb) {
  const tmp = new fabric.StaticCanvas(null, { width: CANVAS_W, height: CANVAS_H });
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
  // Due fonti indipendenti, unite nella stessa lista: la centrale MemorAI
  // (sola lettura, cdn.memoraiengine.com — stessa decisione di caricaLibreria())
  // e quella locale del tenant. Se una delle due non risponde l'altra si
  // mostra comunque, non è un errore bloccante.
  Promise.all([
    fetch(CDN_MEMORAI + '/storia-video/template').then(r => r.ok ? r.json() : {template:[]}).catch(() => ({template:[]})),
    fetch('/admin/api/storia-templates').then(r => r.json()).catch(() => []),
  ]).then(([centrali, locali]) => {
    const container = document.getElementById('saved-templates-list');
    const predefiniti = locali.filter(t => t.globale);
    const miei = locali.filter(t => !t.globale);
    const html = gruppoTemplate('MemorAI (collezione centrale)', centrali.template || [], 'centrale')
      + gruppoTemplate('Predefiniti / Globali', predefiniti, 'locale')
      + gruppoTemplate('Della mia agenzia', miei, 'locale');
    container.innerHTML = html || '<div style="color:var(--gray);font-size:.75rem;font-style:italic;padding:.4rem">Nessun template</div>';
  });
}
function gruppoTemplate(titolo, lista, fonte) {
  if (!lista.length) return '';
  return '<div style="font-size:.58rem;letter-spacing:.12em;text-transform:uppercase;color:var(--gray);padding:.3rem .1rem .25rem">' + titolo + '</div>'
    + lista.map(t => {
      const anteprima = fonte === 'centrale' ? (t.preview ? CDN_MEMORAI + t.preview : null) : t.anteprima;
      const applica = fonte === 'centrale' ? `loadCentralTemplate(${_esc(JSON.stringify(t.id))})` : `loadSavedTemplate(${t.id})`;
      return `
      <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.35rem;padding:.35rem;border:1px solid var(--border);border-radius:5px;background:var(--cream)">
        <div style="width:34px;height:44px;border-radius:3px;flex-shrink:0;border:1px solid var(--border);background-color:#fff;background-size:cover;background-position:center${anteprima?`;background-image:url('${anteprima}')`:''}"></div>
        <div style="flex:1;min-width:0"><div style="font-size:.73rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${_esc(t.nome)}</div></div>
        <div style="display:flex;flex-direction:column;gap:2px">
          <button title="Applica" onclick="${applica}" style="font-size:.62rem;padding:2px 4px;border:1px solid var(--border);border-radius:3px;background:var(--ink);color:#fff;cursor:pointer">↓</button>
          ${fonte === 'locale' && t.editabile ? `<button title="Elimina" onclick="deleteSavedTemplate(${t.id}, ${_esc(JSON.stringify(t.nome))})" style="font-size:.62rem;padding:2px 4px;border:1px solid var(--border);border-radius:3px;background:var(--red);color:#fff;cursor:pointer">✕</button>` : ''}
        </div>
      </div>`;
    }).join('');
}
/** Template centrale: il singolo (con canvas_json) si scarica solo all'applicazione, l'elenco resta leggero. */
async function loadCentralTemplate(id) {
  const t = await fetch(CDN_MEMORAI + '/storia-video/template/' + encodeURIComponent(id)).then(r => r.ok ? r.json() : null).catch(() => null);
  if (!t || !t.canvas_json) { modale({ titolo: 'Template non disponibile', testo: 'Non è stato possibile scaricare questo template dalla collezione centrale.' }); return; }

  if (canvas.getObjects().length) {
    const conferma = await modale({
      titolo: 'Applicare il template?', testo: 'Il contenuto attuale verrà sostituito. I dati del defunto vengono riempiti in automatico.',
      azioni: [{ testo: 'Annulla', valore: null, tipo: 'neutro' }, { testo: 'Applica', valore: 'ok', tipo: 'primario' }],
    });
    if (!conferma.azione) return;
  }
  canvas.loadFromJSON(t.canvas_json, function() { riempiConDefunto(); canvas.renderAll(); refreshLayers(); });
}
function _esc(s){ return String(s).replace(/[&<>"]/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch]; }); }

async function saveAsTemplate() {
  const layout = layoutTemplateJSON(canvas);
  if (!layout.objects.length) { await modale({ titolo: 'Niente da salvare', testo: 'La storia è vuota: aggiungi almeno un elemento prima di creare un template.' }); return; }

  const scelta = await modale({
    titolo: 'Nuovo template',
    testo: 'Viene salvata solo l\'impaginazione: nome, date, frase ed età tornano segnaposto e la foto del defunto non viene inclusa.',
    campo: { etichetta: 'Nome del template', valore: 'Storia ' + new Date().toLocaleDateString('it-IT') },
    azioni: [{ testo: 'Annulla', valore: null, tipo: 'neutro' }, { testo: 'Salva template', valore: 'ok', tipo: 'primario' }],
  });
  if (!scelta.azione || !scelta.valore) return;

  anteprimaTemplate(layout, function(thumbnail) {
    fetch('/admin/api/storia-templates', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ nome: scelta.valore, layout: JSON.stringify(layout), anteprima: thumbnail }),
    }).then(r => r.json()).then(res => {
      if (!res.success) { modale({ titolo: 'Salvataggio non riuscito', testo: _esc(res.error || 'Riprova fra un momento.') }); return; }
      loadSavedTemplates();
    }).catch(() => modale({ titolo: 'Salvataggio non riuscito', testo: 'Non è stato possibile contattare il server.' }));
  });
}
async function loadSavedTemplate(id) {
  const templates = await fetch('/admin/api/storia-templates').then(r => r.json());
  const t = templates.filter(function(x) { return x.id == id; })[0];
  if (!t) return;

  if (canvas.getObjects().length) {
    const conferma = await modale({
      titolo: 'Applicare il template?', testo: '<strong>' + _esc(t.nome) + '</strong><br>Il contenuto attuale verrà sostituito. I dati del defunto vengono riempiti in automatico.',
      azioni: [{ testo: 'Annulla', valore: null, tipo: 'neutro' }, { testo: 'Applica', valore: 'ok', tipo: 'primario' }],
    });
    if (!conferma.azione) return;
  }
  canvas.loadFromJSON(t.layout, function() { riempiConDefunto(); canvas.renderAll(); refreshLayers(); });
}
function riempiConDefunto() {
  canvas.getObjects().forEach(function(o) {
    if ((o.type === 'textbox' || o.type === 'text') && !o.styles) o.styles = {};
    if (BLOCCHI_PERSONALI.indexOf(o.customBlockType) !== -1) o.set('text', testoPersonale(o.customBlockType, praticaData));
  });
  canvas.renderAll();
}
async function deleteSavedTemplate(id, nome) {
  const conferma = await modale({
    titolo: 'Eliminare il template?', testo: '<strong>' + _esc(nome || '') + '</strong> verrà rimosso dall\'elenco.',
    azioni: [{ testo: 'Annulla', valore: null, tipo: 'neutro' }, { testo: 'Elimina', valore: 'ok', tipo: 'pericolo' }],
  });
  if (!conferma.azione) return;
  fetch('/admin/api/storia-templates/'+id, { method: 'DELETE' }).then(r=>r.json()).then(res => {
    if (!res.success) { modale({ titolo: 'Eliminazione non riuscita', testo: _esc(res.error || 'Riprova fra un momento.') }); return; }
    loadSavedTemplates();
  });
}

// ── PANNELLO LIVELLI (porting diretto) ──
function layerLabel(o, idx) {
  if (o.customType === 'divisore') return { icon: '─', name: 'Divisore' };
  if (o.type === 'image') return { icon: '\u{1F5BC}', name: 'Immagine' };
  if (o.tipoTracciato) return { icon: '\u{1F300}', name: 'Testo curvo' };
  if (o.type === 'textbox' || o.type === 'text') {
    var t = (o.text || '').replace(/\s+/g, ' ').trim();
    return { icon: 'T', name: t ? (t.length > 22 ? t.slice(0,22) + '…' : t) : 'Testo vuoto' };
  }
  if (o.type === 'line') return { icon: '╱', name: 'Linea' };
  return { icon: '◆', name: (o.type || 'Oggetto') + ' ' + (idx+1) };
}
function refreshLayers() {
  var box = document.getElementById('layers-list');
  if (!box) return;
  var objs = window.canvas.getObjects().filter(function(o){ return o.customType !== 'background' && !o.isCorniceMaschera; });
  if (!objs.length) { box.innerHTML = '<div class="layers-empty">Nessun elemento nella storia</div>'; return; }
  var active = window.canvas.getActiveObjects();
  var html = '';
  for (var i = objs.length - 1; i >= 0; i--) {
    var o = objs[i], lab = layerLabel(o, i), isSel = active.indexOf(o) !== -1;
    var oid = o.__layerId || (o.__layerId = 'ly_' + Math.random().toString(36).slice(2,9));
    html += '<div class="layer-row' + (isSel ? ' active' : '') + '" data-oid="' + oid + '" onclick="onLayerRowClick(event,\'' + oid + '\')">'
         +    '<input type="checkbox" ' + (isSel ? 'checked' : '') + ' onclick="event.stopPropagation();onLayerCheck(\'' + oid + '\',this.checked)">'
         +    '<span class="layer-icon">' + lab.icon + '</span>'
         +    '<span class="layer-name" title="' + lab.name.replace(/"/g,'&quot;') + '">' + lab.name + '</span>'
         +  '</div>';
  }
  box.innerHTML = html;
}
function findLayerObj(oid) { return window.canvas.getObjects().filter(function(o){ return o.__layerId === oid; })[0] || null; }
function applyLayerSelection(objs) {
  window.canvas.discardActiveObject();
  if (objs.length === 1) { window.canvas.setActiveObject(objs[0]); }
  else if (objs.length > 1) { var sel = new fabric.ActiveSelection(objs, { canvas: window.canvas }); window.canvas.setActiveObject(sel); }
  window.canvas.requestRenderAll();
  refreshLayers();
}
function currentCheckedObjs() {
  var rows = document.querySelectorAll('#layers-list .layer-row'), objs = [];
  rows.forEach(function(r){ var cb = r.querySelector('input[type=checkbox]'); if (cb && cb.checked) { var o = findLayerObj(r.getAttribute('data-oid')); if (o) objs.push(o); } });
  return objs;
}
function onLayerCheck(oid, checked) { applyLayerSelection(currentCheckedObjs()); }
function onLayerRowClick(ev, oid) { var o = findLayerObj(oid); if (o) applyLayerSelection([o]); }
function selectAllLayers() { applyLayerSelection(window.canvas.getObjects().filter(function(o){ return o.customType !== 'background'; })); }
function deselectAllLayers() { window.canvas.discardActiveObject(); window.canvas.requestRenderAll(); refreshLayers(); }

// ── UNDO / REDO (porting diretto) ──
var undoStack = [], redoStack = [], isUndoRedo = false, undoTimer = null;
function saveState() {
  if (isUndoRedo) return;
  clearTimeout(undoTimer);
  undoTimer = setTimeout(function() {
    var state = JSON.stringify(window.canvas.toJSON(CUSTOM_PROPS));
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
  window.canvas.loadFromJSON(JSON.parse(state), function() { window.canvas.renderAll(); refreshLayers(); setTimeout(function(){ isUndoRedo = false; updateUndoRedoBtns(); }, 100); });
}
function redoAction() {
  if (!redoStack.length) return;
  isUndoRedo = true;
  var state = redoStack.pop();
  undoStack.push(state);
  window.canvas.loadFromJSON(JSON.parse(state), function() { window.canvas.renderAll(); refreshLayers(); setTimeout(function(){ isUndoRedo = false; updateUndoRedoBtns(); }, 100); });
}
function updateUndoRedoBtns() {
  var u = document.getElementById('btn-undo'), r = document.getElementById('btn-redo');
  if (u) { u.disabled = undoStack.length <= 1; u.style.opacity = undoStack.length <= 1 ? '.4' : '1'; }
  if (r) { r.disabled = redoStack.length === 0; r.style.opacity = redoStack.length === 0 ? '.4' : '1'; }
}

// ── LINEE GUIDA (porting diretto) ──
var guideLines = [], guideVisible = false;
function toggleGuide() {
  var btn = document.getElementById('btn-guide');
  if (guideVisible) {
    guideLines.forEach(function(l){ window.canvas.remove(l); });
    guideLines = []; guideVisible = false;
    if (btn) { btn.style.borderColor=''; btn.style.color=''; btn.style.background=''; }
  } else {
    var w = window.canvas.width, h = window.canvas.height;
    var defs = [
      [0, h/2, w, h/2, 'rgba(0,150,255,.6)', [5,5]], [w/2, 0, w/2, h, 'rgba(0,150,255,.6)', [5,5]],
      [0, h/3, w, h/3, 'rgba(255,80,80,.4)', [3,7]], [0, h*2/3, w, h*2/3, 'rgba(255,80,80,.4)', [3,7]],
      [w/3, 0, w/3, h, 'rgba(255,80,80,.4)', [3,7]], [w*2/3, 0, w*2/3, h, 'rgba(255,80,80,.4)', [3,7]],
    ];
    defs.forEach(function(d){
      var l = new fabric.Line([d[0],d[1],d[2],d[3]], {stroke:d[4],strokeWidth:1,strokeDashArray:d[5],selectable:false,evented:false,excludeFromExport:true});
      window.canvas.add(l); l.bringToFront(); guideLines.push(l);
    });
    guideVisible = true;
    if (btn) { btn.style.borderColor='#c8a96e'; btn.style.color='#c8a96e'; btn.style.background='rgba(200,169,110,.15)'; }
  }
  window.canvas.renderAll();
}

document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undoAction(); }
  if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); redoAction(); }
  if (e.key === 'Delete' || e.key === 'Backspace') {
    var c = window.canvas;
    if (!c) return;
    var active = c.getActiveObject();
    if (!active || active.isEditing) return;
    var t = e.target;
    var isUiField = (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable));
    if (isUiField) return;
    e.preventDefault();
    if (active.type === 'activeSelection') { active.forEachObject(function(o) { c.remove(o); }); }
    else { c.remove(active); }
    c.discardActiveObject();
    clearPropsPanel();
    c.requestRenderAll();
    saveState();
  }
});

// ── MODALE (Promise-based, porting diretto) ──
let _modaleChiudi = null;
function modale(opzioni) {
  const box = document.getElementById('app-modal'), input = document.getElementById('app-modal-input'), campo = document.getElementById('app-modal-field');
  const azioni = opzioni.azioni || [{ testo: 'Ho capito', valore: 'ok', tipo: 'primario' }];
  document.getElementById('app-modal-title').textContent = opzioni.titolo || '';
  document.getElementById('app-modal-text').innerHTML = opzioni.testo || '';
  if (opzioni.campo) { campo.style.display = 'block'; document.getElementById('app-modal-label').textContent = opzioni.campo.etichetta || 'Nome'; input.value = opzioni.campo.valore || ''; }
  else { campo.style.display = 'none'; input.value = ''; }
  const stili = { primario: 'background:#c8a96e;color:#fff;border:1px solid #c8a96e', neutro: 'background:#f5f0e8;color:#1a1a2e;border:1px solid #ddd8d0', pericolo: 'background:#c44b3a;color:#fff;border:1px solid #c44b3a' };
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
      if (e.key === 'Enter') { const p = azioni.filter(function(a) { return a.tipo === 'primario'; })[0]; if (p) { e.preventDefault(); chiudiModale(p.valore); } }
    }
    _modaleChiudi = function(valore) { document.removeEventListener('keydown', tasti); box.style.display = 'none'; _modaleChiudi = null; resolve({ azione: valore, valore: input.value.trim() }); };
    document.addEventListener('keydown', tasti);
  });
}
function chiudiModale(valore) { if (_modaleChiudi) _modaleChiudi(valore); }
</script>
</body>
</html>
