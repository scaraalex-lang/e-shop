<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="google" content="notranslate">
<title>Card Designer Necrologio | MemorAI</title>
<script src="/vendor/libs/fabric.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#2a2a3e;color:#1a1a2e;height:100vh;overflow:hidden;display:flex;flex-direction:column}
nav{background:#1a1a2e;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;flex-shrink:0;z-index:100}
.logo{color:#fff;font-size:1rem;font-weight:600;font-family:'Cormorant Garamond',serif}
.nav-links{display:flex;align-items:center;gap:.5rem}
.nav-btn{padding:.4rem .9rem;border-radius:6px;font-size:.8rem;font-weight:500;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none}
.btn-gold{background:linear-gradient(135deg,#c8a96e,#b8944a);color:#0d0d1a}
.btn-ghost{background:rgba(255,255,255,.1);color:#fff}

.cdn-wrap{display:flex;flex:1;overflow:hidden}
.cdn-sidebar{width:280px;flex-shrink:0;background:#1a1a2e;color:#e8e0d0;display:flex;flex-direction:column;overflow-y:auto}
.cdn-sidebar-top{padding:1rem;border-bottom:1px solid rgba(200,169,110,.2)}
.cdn-sidebar-title{font-size:.85rem;font-weight:600;color:#c8a96e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem}
.cdn-canvas-wrap{flex:1;background:#2a2a3e;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.cdn-toolbar{background:#0d0d1a;padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;border-bottom:1px solid rgba(200,169,110,.15);flex-shrink:0}
.cdn-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.btn-dark{background:rgba(255,255,255,.08);color:#e8e0d0;border:1px solid rgba(200,169,110,.2);padding:.5rem 1rem;border-radius:6px;font-size:.8rem;cursor:pointer;font-family:inherit}
.btn-dark:hover{background:rgba(200,169,110,.15)}
.layers-panel{width:200px;flex-shrink:0;background:#111128;border-left:1px solid rgba(200,169,110,.15);display:flex;flex-direction:column;overflow:hidden}
.layers-title{padding:.6rem 1rem;font-size:.72rem;font-weight:600;color:#c8a96e;text-transform:uppercase;letter-spacing:.08em;border-bottom:1px solid rgba(200,169,110,.15);flex-shrink:0}
.layers-list{flex:1;overflow-y:auto;padding:.4rem}
.layer-item{display:flex;align-items:center;gap:.5rem;padding:.45rem .6rem;border-radius:5px;cursor:pointer;border:1px solid transparent;margin-bottom:.25rem;transition:all .15s;font-size:.75rem;color:#e8e0d0}
.layer-item:hover{background:rgba(200,169,110,.08);border-color:rgba(200,169,110,.2)}
.layer-item.active{background:rgba(200,169,110,.15);border-color:#c8a96e;color:#c8a96e}
.layer-thumb{width:28px;height:28px;object-fit:cover;border-radius:3px;background:#1a1a2e;flex-shrink:0;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:.9rem}
.layer-name{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.layer-btns{display:flex;flex-direction:column;gap:1px}
.layer-btn{background:none;border:none;color:rgba(200,169,110,.5);cursor:pointer;font-size:.7rem;padding:1px 3px;line-height:1}
.layer-btn:hover{color:#c8a96e}
.layer-type-badge{font-size:.58rem;color:rgba(255,255,255,.3);display:block}
.tpl-grid{display:grid;grid-template-columns:1fr 1fr;gap:.4rem;padding:.5rem;overflow-y:auto;flex:1}
.tpl-item{border:2px solid transparent;border-radius:8px;overflow:hidden;cursor:pointer;transition:border-color .2s;position:relative}
.tpl-item:hover,.tpl-item.active{border-color:#c8a96e}
.tpl-item img{width:100%;aspect-ratio:1200/630;object-fit:contain;display:block;background:#0d0d1a}
.tpl-item-name{font-size:.7rem;color:#c8a96e;text-align:center;padding:.3rem .5rem;background:rgba(0,0,0,.6);position:absolute;bottom:0;left:0;right:0}
.tpl-empty{padding:1.5rem;text-align:center;color:rgba(200,169,110,.4);font-size:.8rem}
.upload-tpl{margin:.5rem .75rem;display:flex;flex-direction:column;gap:.4rem}
.campo-info{background:rgba(200,169,110,.06);border:1px solid rgba(200,169,110,.15);border-radius:6px;padding:.6rem .75rem;margin:.5rem .75rem}
.campo-info label{font-size:.68rem;color:#c8a96e;display:block;margin-bottom:.2rem;text-transform:uppercase;letter-spacing:.04em}
.campo-info input{width:100%;background:rgba(255,255,255,.05);border:none;color:#e8e0d0;font-size:.82rem;padding:.3rem .5rem;border-radius:4px;font-family:inherit;outline:none}
.sep{height:1px;background:rgba(200,169,110,.15);margin:.5rem 0}
#canvas-container{position:relative}
#canvas-container canvas{border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.5)}
.saving-overlay{display:none;position:absolute;inset:0;background:rgba(0,0,0,.6);align-items:center;justify-content:center;color:#c8a96e;font-size:1rem;border-radius:8px}
</style>
</head>
<body>
{{-- Dentro l'overlay iframe (vedi partials.designer-overlay) il link "torna
     indietro" navigherebbe l'iframe stesso, confuso — si chiude con la ×
     dell'overlay. --}}
<style>body.in-iframe a.nav-btn.btn-ghost { display: none; }</style>
<script>if (window !== window.top) document.body.classList.add('in-iframe');</script>

<nav>
  <div class="logo">MemorAI — Card Necrologio</div>
  <div class="nav-links">
    <span style="color:rgba(255,255,255,.5);font-size:.78rem">{{ $defunto->nomeCompleto() }}</span>
    <a href="{{ route('necrologi.modifica', $necrologio) }}" class="nav-btn btn-ghost">← Necrologio</a>
  </div>
</nav>

<div class="cdn-wrap">
  {{-- SIDEBAR --}}
  <div class="cdn-sidebar">
    <div class="cdn-sidebar-top">
      <div class="cdn-sidebar-title">Template</div>
      <div class="upload-tpl">
        <input type="file" id="upload-tpl-file" accept="image/png" style="display:none" onchange="uploadTemplate(this)">
        <button class="btn-dark" style="width:100%;font-size:.75rem" onclick="document.getElementById('upload-tpl-file').click()">+ Carica template PNG</button>
      </div>
    </div>

    <div class="tpl-grid" id="tpl-grid">
      @if ($templates->isEmpty())
        <div class="tpl-empty" style="grid-column:1/-1">Nessun template.<br>Carica un PNG.</div>
      @else
        @foreach ($templates as $t)
        <div class="tpl-item" data-id="{{ $t->id }}" data-path="{{ $t->url() }}" onclick="selectTemplate(this)">
          <img src="{{ $t->url() }}" alt="{{ $t->nome }}">
          <div class="tpl-item-name">{{ $t->nome }}</div>
          <button onclick="event.stopPropagation();deleteTemplate({{ $t->id }},this)" style="position:absolute;top:3px;right:3px;background:rgba(196,75,58,.8);border:none;color:#fff;border-radius:3px;width:18px;height:18px;font-size:.6rem;cursor:pointer;line-height:1">✕</button>
        </div>
        @endforeach
      @endif
    </div>

    <div class="sep"></div>

    <div class="cdn-sidebar-top" style="padding-bottom:.5rem">
      <div class="cdn-sidebar-title">Testo</div>
    </div>
    <div class="campo-info">
      <label>Nome e Cognome</label>
      <input type="text" id="txt-nome" value="{{ strtoupper($defunto->nomeCompleto()) }}" oninput="updateTesto()">
    </div>
    <div class="campo-info">
      <label>Date</label>
      <input type="text" id="txt-date"
             value="{{ optional($defunto->data_nascita)->format('d/m/Y') }} - {{ optional($defunto->data_morte)->format('d/m/Y') }}"
             oninput="updateTesto()">
    </div>
    <div class="campo-info">
      <label>Testo libero</label>
      <textarea id="txt-libero" rows="3" placeholder="Scrivi qui..." style="width:100%;background:#111128;border:1px solid rgba(200,169,110,.2);color:#e8e0d0;font-size:.82rem;padding:.5rem;border-radius:5px;resize:vertical;font-family:inherit"></textarea>
      <div style="display:flex;gap:.4rem;margin-top:.35rem">
        <input type="color" id="txt-libero-colore" value="#1a1a2e" style="width:28px;height:26px;border:none;border-radius:3px;cursor:pointer" title="Colore testo">
        <select id="txt-libero-size" style="flex:1;background:#111128;border:1px solid rgba(200,169,110,.2);color:#e8e0d0;font-size:.72rem;padding:.25rem;border-radius:4px;font-family:inherit">
          <option value="14">Piccolo</option>
          <option value="20" selected>Medio</option>
          <option value="28">Grande</option>
          <option value="38">Molto grande</option>
        </select>
        <button onclick="aggiungiTestoLibero()" style="background:#c8a96e;color:#1a1a2e;border:none;padding:.25rem .65rem;border-radius:4px;font-size:.75rem;cursor:pointer;font-weight:600">+ Aggiungi</button>
      </div>
    </div>
    <div class="campo-info">
      <label>Dimensione foto %</label>
      <input type="range" id="foto-scale" min="30" max="120" value="80" oninput="scaleFoto(this.value)" style="width:100%;accent-color:#c8a96e">
    </div>
  </div>

  {{-- MAIN --}}
  <div class="cdn-main">
    <div class="cdn-toolbar">
      <button class="btn-dark" onclick="undoCard()" title="Annulla (Ctrl+Z)">↩ Annulla</button>
      <button class="btn-dark" onclick="redoCard()" title="Ripristina (Ctrl+Y)">↪ Ripristina</button>
      <button class="btn-dark" onclick="document.getElementById('upload-foto-file').click()">Carica foto</button>
      <input type="file" id="upload-foto-file" accept="image/*" style="display:none" onchange="loadFoto(this)">
      <button class="btn-dark" onclick="centerFoto()">⊙ Centra foto</button>
      <div style="flex:1"></div>
      <button class="nav-btn btn-gold" id="btn-salva" onclick="salvaCard()">Salva come card</button>
      <a href="{{ route('necrologi.modifica', $necrologio) }}" id="btn-pubblica" class="nav-btn btn-gold"
         style="{{ $necrologio->og_image ? '' : 'display:none' }};text-decoration:none">→ Vai al necrologio</a>
    </div>

    <div class="cdn-toolbar" style="background:#111128;padding:.4rem 1rem;gap:.5rem;flex-wrap:wrap;border-top:1px solid rgba(200,169,110,.08)">
      <span style="font-size:.7rem;color:rgba(200,169,110,.5)">Font:</span>
      <select id="txt-font" onchange="updateTesto()" style="background:#1a1a2e;border:1px solid rgba(200,169,110,.2);color:#e8e0d0;font-size:.75rem;padding:.25rem .5rem;border-radius:4px;font-family:inherit;outline:none">
        <option value="Georgia, serif">Georgia</option>
        <option value="Times New Roman, serif">Times New Roman</option>
        <option value="Palatino, serif">Palatino</option>
        <option value="Arial, sans-serif">Arial</option>
        <option value="Verdana, sans-serif">Verdana</option>
      </select>
      <span style="font-size:.7rem;color:rgba(200,169,110,.5)">Colore:</span>
      <input type="color" id="txt-colore-nome" value="#1a1a2e" oninput="updateTesto()" title="Colore nome" style="width:28px;height:24px;border:none;border-radius:3px;cursor:pointer">
      <input type="color" id="txt-colore-date" value="#2a3a5a" oninput="updateTesto()" title="Colore date" style="width:28px;height:24px;border:none;border-radius:3px;cursor:pointer">
      <span style="font-size:.7rem;color:rgba(200,169,110,.5)">Simboli:</span>
      <button onclick="aggiungiSimbolo('✝')" class="btn-dark" style="padding:.2rem .5rem;font-size:.95rem">✝</button>
      <button onclick="aggiungiSimbolo('✦')" class="btn-dark" style="padding:.2rem .5rem;font-size:.95rem">✦</button>
      <button onclick="aggiungiSimbolo('♥')" class="btn-dark" style="padding:.2rem .5rem;font-size:.95rem">♥</button>
      <button onclick="aggiungiSimbolo('★')" class="btn-dark" style="padding:.2rem .5rem;font-size:.95rem">★</button>
      <button onclick="aggiungiSimbolo('🕊')" class="btn-dark" style="padding:.2rem .5rem;font-size:.85rem">🕊</button>
      <div style="width:1px;height:20px;background:rgba(200,169,110,.2)"></div>
      <span style="font-size:.7rem;color:rgba(200,169,110,.5)">Livelli:</span>
      <button onclick="livelloSu()" class="btn-dark" style="padding:.2rem .5rem;font-size:.75rem" title="Porta avanti">⬆</button>
      <button onclick="livelloGiu()" class="btn-dark" style="padding:.2rem .5rem;font-size:.75rem" title="Porta indietro">⬇</button>
      <button onclick="livelloTop()" class="btn-dark" style="padding:.2rem .5rem;font-size:.75rem" title="In primo piano">⤒</button>
      <button onclick="livelloBottom()" class="btn-dark" style="padding:.2rem .5rem;font-size:.75rem" title="In fondo">⤓</button>
      <button onclick="templateInTop()" class="btn-dark" style="padding:.2rem .5rem;font-size:.7rem;border-color:#c8a96e;color:#c8a96e">TPL sopra</button>
    </div>
    <div class="cdn-canvas-wrap">
      <div id="canvas-container">
        <canvas id="necro-canvas"></canvas>
        <div class="saving-overlay" id="saving-overlay">Salvataggio...</div>
      </div>
    </div>
  </div>

  {{-- PANNELLO LIVELLI --}}
  <div class="layers-panel">
    <div class="layers-title">Livelli</div>
    <div class="layers-list" id="layers-list">
      <div style="font-size:.7rem;color:rgba(255,255,255,.3);text-align:center;padding:1rem">Nessun oggetto</div>
    </div>
    <div style="padding:.5rem;border-top:1px solid rgba(200,169,110,.1);display:flex;flex-direction:column;gap:.3rem">
      <button onclick="eliminaSelezionato()" class="btn-dark" style="font-size:.7rem;padding:.35rem;color:#c44b3a;border-color:rgba(196,75,58,.3)">Elimina selezionato</button>
      <button onclick="templateInTop()" class="btn-dark" style="font-size:.7rem;padding:.35rem;border-color:#c8a96e;color:#c8a96e">Template sopra</button>
    </div>
  </div>
</div>

{{-- Modale propria: al posto di prompt/confirm/alert, come nel Ricordino Designer. --}}
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
const NECRO_ID = {{ $necrologio->id }};
const csrfToken = '{{ csrf_token() }}';
const savedCanvas = @json($savedCanvas ?? null);

// Allega CSRF e sessione a tutte le chiamate /admin/api/, come nel Ricordino
// Designer: gli endpoint sono protetti dall'autenticazione dell'area studio.
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

// Canvas 1200x630 (1.91:1) scalato per schermo: è il formato che Facebook e
// WhatsApp si aspettano per l'anteprima grande di un link. Un formato diverso
// non viene ridimensionato ma ritagliato al centro, perdendo quello che sta
// sopra e sotto (successo con la vecchia card verticale 910x1093).
const ORIG_W = 1200, ORIG_H = 630;
let canvasScale = 1;

function initCanvas() {
  const wrap = document.querySelector('.cdn-canvas-wrap');
  const maxH = wrap.clientHeight - 40;
  const maxW = wrap.clientWidth - 40;
  canvasScale = Math.min(maxW / ORIG_W, maxH / ORIG_H);
  const cw = Math.floor(ORIG_W * canvasScale);
  const ch = Math.floor(ORIG_H * canvasScale);
  window.canvas = new fabric.Canvas('necro-canvas', { width: cw, height: ch, backgroundColor: '#1a1a2e' });
}

// ── UNDO/REDO ──
var _history = [], _historyRedo = [], _historyLock = false;
function saveHistory() {
  if (_historyLock) return;
  _history.push(JSON.stringify(canvas.toJSON(['name','selectable','evented','customType'])));
  if (_history.length > 30) _history.shift();
  _historyRedo = [];
}
function undoCard() {
  if (_history.length < 2) return;
  _historyLock = true;
  _historyRedo.push(_history.pop());
  var state = _history[_history.length-1];
  canvas.loadFromJSON(state, function() { canvas.renderAll(); aggiornaLayersPanel(); _historyLock = false; });
}
function redoCard() {
  if (!_historyRedo.length) return;
  _historyLock = true;
  var state = _historyRedo.pop();
  _history.push(state);
  canvas.loadFromJSON(state, function() { canvas.renderAll(); aggiornaLayersPanel(); _historyLock = false; });
}
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey||e.metaKey) && e.key==='z') { e.preventDefault(); undoCard(); }
  if ((e.ctrlKey||e.metaKey) && (e.key==='y'||(e.shiftKey&&e.key==='z'))) { e.preventDefault(); redoCard(); }
});
initCanvas();
setTimeout(function() {
  saveHistory();
  canvas.on('object:added', saveHistory);
  canvas.on('object:modified', saveHistory);
  canvas.on('object:removed', saveHistory);
}, 500);
window.addEventListener('resize', function() {
  clearTimeout(window._ncrTimer);
  window._ncrTimer = setTimeout(function() {
    if (!window.canvas) return;
    var wrap = document.querySelector('.cdn-canvas-wrap');
    var maxH = wrap.clientHeight - 20;
    var maxW = wrap.clientWidth - 20;
    var scale = Math.min(maxW / ORIG_W, maxH / ORIG_H);
    var cw = Math.floor(ORIG_W * scale);
    var ch = Math.floor(ORIG_H * scale);
    canvas.setWidth(cw);
    canvas.setHeight(ch);
    canvasScale = scale;
    canvas.renderAll();
  }, 150);
});

let fotoObj = null, templateObj = null, nomeObj = null, dateObj = null;

// ── TESTO ──
function addTesto() {
  const nome = document.getElementById('txt-nome').value;
  const date = document.getElementById('txt-date').value;
  const s = canvasScale;

  if (nomeObj) canvas.remove(nomeObj);
  if (dateObj) canvas.remove(dateObj);

  var font = document.getElementById('txt-font').value;
  var colorNome = document.getElementById('txt-colore-nome').value;
  var colorDate = document.getElementById('txt-colore-date').value;

  nomeObj = new fabric.Text(nome, {
    left: 880 * s, top: 270 * s,
    originX: 'center', originY: 'center',
    fontSize: 52 * s, fontWeight: 'bold',
    fill: colorNome, fontFamily: font,
    selectable: true, customType: 'nome'
  });

  dateObj = new fabric.Text(date, {
    left: 880 * s, top: 340 * s,
    originX: 'center', originY: 'center',
    fontSize: 30 * s,
    fill: colorDate, fontFamily: font,
    selectable: true, customType: 'date'
  });

  canvas.add(nomeObj);
  canvas.add(dateObj);
  if (templateObj) templateObj.bringToFront();
  canvas.renderAll();
}

function updateTesto() {
  var font = document.getElementById('txt-font').value;
  var colorNome = document.getElementById('txt-colore-nome').value;
  var colorDate = document.getElementById('txt-colore-date').value;
  if (nomeObj) { nomeObj.set({text: document.getElementById('txt-nome').value, fontFamily: font, fill: colorNome}); }
  if (dateObj) { dateObj.set({text: document.getElementById('txt-date').value, fontFamily: font, fill: colorDate}); }
  if (templateObj) templateObj.bringToFront();
  canvas.renderAll();
}

// ── TEMPLATE ──
function selectTemplate(el) {
  document.querySelectorAll('.tpl-item').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
  loadTemplateImage(el.dataset.path);
}

function loadTemplateImage(path) {
  if (templateObj) canvas.remove(templateObj);
  fabric.Image.fromURL(path, function(img) {
    img.scaleToWidth(canvas.width);
    img.set({ left: 0, top: 0, selectable: false, evented: false, customType: 'template' });
    templateObj = img;
    canvas.add(img);
    img.bringToFront();
    canvas.renderAll();
  });
}

function uploadTemplate(input) {
  const file = input.files[0];
  if (!file) return;
  modale({
    titolo: 'Nome del template',
    campo: { etichetta: 'Nome', valore: file.name.replace('.png','') },
    azioni: [{ testo: 'Annulla', valore: null, tipo: 'neutro' }, { testo: 'Carica', valore: 'ok', tipo: 'primario' }],
  }).then(function(r) {
    if (r.azione !== 'ok') { input.value = ''; return; }
    const fd = new FormData();
    fd.append('template', file);
    fd.append('nome', r.valore || file.name);
    fetch('/admin/api/necrologio-card-templates', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
      .then(res => res.json().then(d => ({ ok: res.ok, d: d })))
      .then(({ ok, d }) => {
        if (!ok || !d.success) {
          modale({ titolo: 'Errore', testo: (d && (d.error || d.message)) || 'Caricamento del template fallito, riprova.' });
          return;
        }
        const grid = document.getElementById('tpl-grid');
        const empty = grid.querySelector('.tpl-empty');
        if (empty) empty.remove();
        const div = document.createElement('div');
        div.className = 'tpl-item';
        div.dataset.id = d.id;
        div.dataset.path = d.url;
        div.onclick = function(){ selectTemplate(this); };
        div.innerHTML = '<img src="'+d.url+'" alt="'+d.nome+'"><div class="tpl-item-name">'+d.nome+'</div><button onclick="event.stopPropagation();deleteTemplate('+d.id+',this)" style="position:absolute;top:3px;right:3px;background:rgba(196,75,58,.8);border:none;color:#fff;border-radius:3px;width:18px;height:18px;font-size:.6rem;cursor:pointer;line-height:1">✕</button>';
        grid.appendChild(div);
      })
      .catch(() => {
        modale({ titolo: 'Errore di connessione', testo: 'Riprova.' });
      });
    input.value = '';
  });
}

function deleteTemplate(id, btn) {
  modale({
    titolo: 'Eliminare il template?',
    testo: 'Non si può annullare.',
    azioni: [{ testo: 'Annulla', valore: null, tipo: 'neutro' }, { testo: 'Elimina', valore: 'ok', tipo: 'pericolo' }],
  }).then(function(r) {
    if (r.azione !== 'ok') return;
    fetch('/admin/api/necrologio-card-templates/'+id, { method: 'DELETE' })
      .then(res => res.json()).then(d => { if (d.success) btn.closest('.tpl-item').remove(); });
  });
}

// ── FOTO ──
function loadFoto(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    if (fotoObj) canvas.remove(fotoObj);
    fabric.Image.fromURL(e.target.result, function(img) {
      const s = canvasScale;
      img.scaleToWidth(280 * s * 2 * 0.8);
      img.set({ left: 340 * s, top: 315 * s, originX: 'center', originY: 'center', selectable: true, hasControls: true, lockRotation: false, cornerColor: '#c8a96e', cornerStrokeColor: '#0d1128', cornerStyle: 'circle', cornerSize: 16, borderColor: '#c8a96e', transparentCorners: false, rotatingPointOffset: 40, customType: 'foto' });
      fotoObj = img;
      canvas.add(img);
      if (nomeObj) nomeObj.bringToFront();
      if (dateObj) dateObj.bringToFront();
      if (templateObj) templateObj.bringToFront();
      canvas.renderAll();
    });
  };
  reader.readAsDataURL(file);
  input.value = '';
}

function scaleFoto(val) {
  if (!fotoObj) return;
  const s = canvasScale;
  fotoObj.scaleToWidth(280 * s * 2 * (val / 100));
  canvas.renderAll();
}

function centerFoto() {
  if (!fotoObj) return;
  fotoObj.set({ left: 340 * canvasScale, top: 315 * canvasScale, originX: 'center', originY: 'center' });
  canvas.renderAll();
}

// ── SALVA ──
function salvaCard() {
  document.getElementById('saving-overlay').style.display = 'flex';

  const multiplier = ORIG_W / canvas.width;
  const dataURL = canvas.toDataURL({ format: 'png', multiplier: multiplier });

  const canvasJson = canvas.toJSON(['customType']);
  canvasJson._scale = canvasScale;

  fetch('/admin/api/necrologi/'+NECRO_ID+'/salva-card', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ image_data: dataURL, canvas: JSON.stringify(canvasJson) })
  }).then(r => r.json().then(d => ({ ok: r.ok, d: d }))).then(({ ok, d }) => {
    document.getElementById('saving-overlay').style.display = 'none';
    if (ok && d.success) {
      document.getElementById('btn-pubblica').style.display = 'inline-flex';
      document.getElementById('btn-salva').textContent = '✓ Salvata';
    } else {
      modale({ titolo: 'Errore', testo: (d && (d.error || d.message)) || 'Sconosciuto' });
    }
  }).catch(() => {
    document.getElementById('saving-overlay').style.display = 'none';
    modale({ titolo: 'Errore di connessione', testo: 'Riprova.' });
  });
}

// Testo libero
function aggiungiTestoLibero() {
  var testo = document.getElementById('txt-libero').value.trim();
  if (!testo) return;
  var colore = document.getElementById('txt-libero-colore').value;
  var size = parseInt(document.getElementById('txt-libero-size').value);
  var font = document.getElementById('txt-font').value;
  var obj = new fabric.Textbox(testo, {
    left: canvas.getWidth() / 2,
    top: canvas.getHeight() / 2,
    originX: 'center',
    originY: 'center',
    fontSize: size,
    fill: colore,
    fontFamily: font,
    textAlign: 'center',
    width: canvas.getWidth() * 0.8,
    selectable: true,
    editable: true,
    name: 'testo_libero'
  });
  canvas.add(obj);
  canvas.setActiveObject(obj);
  canvas.renderAll();
  aggiornaLayersPanel();
  document.getElementById('txt-libero').value = '';
}

// Gestione livelli
function livelloSu() { var o=canvas.getActiveObject(); if(o){o.bringForward();canvas.renderAll();aggiornaLayersPanel();} }
function livelloGiu() { var o=canvas.getActiveObject(); if(o){o.sendBackwards();canvas.renderAll();aggiornaLayersPanel();} }
function livelloTop() { var o=canvas.getActiveObject(); if(o){o.bringToFront();canvas.renderAll();aggiornaLayersPanel();} }
function livelloBottom() { var o=canvas.getActiveObject(); if(o){o.sendToBack();canvas.renderAll();aggiornaLayersPanel();} }
function templateInTop() { if(templateObj){templateObj.bringToFront();canvas.renderAll();aggiornaLayersPanel();} }
function eliminaSelezionato() { var o=canvas.getActiveObject(); if(o&&o!==templateObj){canvas.remove(o);if(o===fotoObj)fotoObj=null;if(o===nomeObj)nomeObj=null;if(o===dateObj)dateObj=null;canvas.renderAll();aggiornaLayersPanel();} }

function aggiornaLayersPanel() {
  var list = document.getElementById("layers-list");
  var objects = canvas.getObjects();
  if (!objects.length) { list.innerHTML = '<div style="font-size:.7rem;color:rgba(255,255,255,.3);text-align:center;padding:1rem">Nessun oggetto</div>'; return; }
  list.innerHTML = "";
  for (var i = objects.length-1; i >= 0; i--) {
    var obj = objects[i];
    var idx = i;
    var nome = obj === templateObj ? "Template" : obj === fotoObj ? "Foto" : obj === nomeObj ? "Nome" : obj === dateObj ? "Date" : "Oggetto";
    var tipo = obj.type || "";
    var isActive = canvas.getActiveObject() === obj;
    var item = document.createElement("div");
    item.className = "layer-item" + (isActive?" active":"");
    item.draggable = true;
    item.dataset.idx = idx;
    item.addEventListener('dragstart', function(e) {
      e.dataTransfer.setData('text/plain', this.dataset.idx);
      this.style.opacity = '0.5';
    });
    item.addEventListener('dragend', function() { this.style.opacity = '1'; });
    item.addEventListener('dragover', function(e) { e.preventDefault(); this.style.background='rgba(200,169,110,.2)'; });
    item.addEventListener('dragleave', function() { this.style.background=''; });
    item.addEventListener('drop', function(e) {
      e.preventDefault();
      this.style.background='';
      var fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
      var toIdx = parseInt(this.dataset.idx);
      if (fromIdx === toIdx) return;
      var objs = canvas.getObjects();
      var fromObj = objs[fromIdx];
      canvas.remove(fromObj);
      var newObjs = canvas.getObjects();
      newObjs.splice(toIdx, 0, fromObj);
      canvas.clear();
      newObjs.forEach(function(o) { canvas.add(o); });
      canvas.renderAll();
      aggiornaLayersPanel();
    });
    item.innerHTML = '<div class="layer-thumb">'+
      (obj===templateObj||obj===fotoObj ? '🖼' : obj.text ? obj.text.substring(0,2) : '○')+
      '</div><div class="layer-name">'+nome+'<span class="layer-type-badge">'+tipo+'</span></div>'+
      '<div class="layer-btns"><button class="layer-btn" onclick="mvUp('+idx+')">▲</button><button class="layer-btn" onclick="mvDown('+idx+')">▼</button></div>';
    item.addEventListener("click", function(ii){ return function(){ canvas.setActiveObject(objects[ii]); canvas.renderAll(); aggiornaLayersPanel(); }; }(idx));
    list.appendChild(item);
  }
}

function mvUp(idx) { var objs=canvas.getObjects(); if(idx<objs.length-1){objs[idx].bringForward();canvas.renderAll();aggiornaLayersPanel();} }
function mvDown(idx) { var objs=canvas.getObjects(); if(idx>0){objs[idx].sendBackwards();canvas.renderAll();aggiornaLayersPanel();} }

canvas.on("object:added", aggiornaLayersPanel);
canvas.on("object:removed", aggiornaLayersPanel);
canvas.on("selection:created", aggiornaLayersPanel);
canvas.on("selection:cleared", aggiornaLayersPanel);
canvas.preserveObjectStacking = true;

function aggiungiSimbolo(sym) {
  var s = canvasScale;
  var simObj = new fabric.Text(sym, {
    left: canvas.width/2, top: canvas.height/2,
    originX: 'center', originY: 'center',
    fontSize: 60*s, fill: '#c8a96e', selectable: true
  });
  canvas.add(simObj);
  if (templateObj) templateObj.bringToFront();
  canvas.setActiveObject(simObj);
  canvas.renderAll();
}

// Progetto già salvato in precedenza: lo si ritrova così com'era, invece di
// ripartire sempre da un canvas vuoto (foto della pratica + nome/date di
// default). Il canvas è stato serializzato in coordinate della finestra di
// chi ha salvato (`_scale`): si riproporziona su quella di chi lo riapre,
// altrimenti su uno schermo diverso il progetto torna spostato o fuori scala.
if (savedCanvas && savedCanvas.objects && savedCanvas.objects.length) {
  canvas.loadFromJSON(savedCanvas, function () {
    var savedScale = savedCanvas._scale || canvasScale;
    var ratio = canvasScale / savedScale;
    if (Math.abs(ratio - 1) > 0.001) {
      canvas.getObjects().forEach(function (o) {
        var upd = { left: o.left * ratio, top: o.top * ratio, scaleX: o.scaleX * ratio, scaleY: o.scaleY * ratio };
        if (o.fontSize) upd.fontSize = o.fontSize * ratio;
        o.set(upd);
        o.setCoords();
      });
    }
    canvas.getObjects().forEach(function (o) {
      if (o.customType === 'foto') fotoObj = o;
      if (o.customType === 'nome') nomeObj = o;
      if (o.customType === 'date') dateObj = o;
      if (o.customType === 'template') templateObj = o;
    });
    canvas.renderAll();
    aggiornaLayersPanel();
  });
} else {
  addTesto();

  @if ($fotoPrincipale)
  (function() {
    const s = canvasScale;
    fabric.Image.fromURL("{{ $fotoPrincipale }}", function(img) {
      img.scaleToWidth(280 * s * 2 * 0.8);
      img.set({ left: 340*s, top: 315*s, originX:"center", originY:"center", selectable:true, hasControls: true, lockRotation: false, cornerColor: '#c8a96e', cornerStrokeColor: '#0d1128', cornerStyle: 'circle', cornerSize: 16, borderColor: '#c8a96e', transparentCorners: false, rotatingPointOffset: 40, customType: 'foto' });
      fotoObj = img;
      canvas.add(img);
      if (nomeObj) nomeObj.bringToFront();
      if (dateObj) dateObj.bringToFront();
      if (templateObj) templateObj.bringToFront();
      canvas.renderAll();
    });
  })();
  @endif
}
</script>
</body>
</html>
