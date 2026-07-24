<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestione Foto | MemorAI</title>
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
<script src="/vendor/libs/fabric.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Inter,sans-serif;background:#1a1a2e;color:#e8e4dc;min-height:100vh;display:flex;flex-direction:column}
nav{background:rgba(0,0,0,.3);padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;border-bottom:1px solid rgba(200,169,110,.2);flex-shrink:0}
.logo{color:#c8a96e;font-weight:600;font-size:.95rem}
.nav-back{color:rgba(255,255,255,.6);font-size:.82rem;text-decoration:none;display:flex;align-items:center;gap:.4rem}
.layout{display:flex;flex:1;overflow:hidden}

/* SIDEBAR SINISTRA - GALLERIA */
.gallery-panel{width:220px;background:rgba(0,0,0,.25);border-right:1px solid rgba(200,169,110,.1);display:flex;flex-direction:column;flex-shrink:0}
.panel-title{font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;color:#c8a96e;padding:.6rem 1rem .4rem;border-bottom:1px solid rgba(200,169,110,.1);font-weight:500}
.gallery-list{flex:1;overflow-y:auto;padding:.5rem}
.gallery-item{position:relative;border-radius:6px;overflow:hidden;cursor:pointer;margin-bottom:.5rem;border:2px solid transparent;transition:all .2s}
.gallery-item:hover{border-color:rgba(200,169,110,.5)}
.gallery-item.active{border-color:#c8a96e}
.gallery-item img{width:100%;height:200px;object-fit:cover;display:block}
.gallery-item .badge-principale{position:absolute;top:3px;left:3px;background:#c8a96e;color:#1a1a2e;font-size:.6rem;padding:.1rem .4rem;border-radius:3px;font-weight:600}
.gallery-item .btn-delete{position:absolute;top:3px;right:3px;background:rgba(196,75,58,.9);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:.6rem;cursor:pointer;display:flex;align-items:center;justify-content:center}
.gallery-item .tipo-badge{position:absolute;bottom:3px;left:3px;background:rgba(0,0,0,.7);color:#fff;font-size:.58rem;padding:.1rem .35rem;border-radius:3px}
.upload-zone{margin:.5rem;border:2px dashed rgba(200,169,110,.3);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s}
.upload-zone:hover{border-color:#c8a96e;background:rgba(200,169,110,.05)}
.upload-zone-icon{font-size:1.5rem;margin-bottom:.35rem}
.upload-zone-text{font-size:.72rem;color:rgba(255,255,255,.5)}

/* CANVAS AREA */
.canvas-area{flex:1;display:flex;align-items:center;justify-content:center;background:#13131f;position:relative;overflow:hidden}
.canvas-wrapper{position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}

/* SIDEBAR DESTRA - STRUMENTI */
.tools-panel{width:260px;background:rgba(0,0,0,.25);border-left:1px solid rgba(200,169,110,.1);display:flex;flex-direction:column;overflow-y:auto;flex-shrink:0}
.tool-section{padding:.75rem 1rem;border-bottom:1px solid rgba(200,169,110,.1)}
.tool-label{font-size:.7rem;color:rgba(255,255,255,.5);margin-bottom:.5rem;font-weight:500;letter-spacing:.05em;text-transform:uppercase}
.slider-row{display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem}
.slider-row label{font-size:.72rem;color:rgba(255,255,255,.6);min-width:70px}
.slider-row input[type=range]{flex:1;accent-color:#c8a96e}
.slider-row span{font-size:.72rem;color:#c8a96e;min-width:30px;text-align:right}
.btn-tool{width:100%;padding:.55rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#e8e4dc;font-size:.8rem;cursor:pointer;font-family:Inter,sans-serif;margin-bottom:.4rem;transition:all .2s;text-align:center}
.btn-tool:hover{background:rgba(200,169,110,.15);border-color:#c8a96e}
.btn-tool.active{background:rgba(200,169,110,.2);border-color:#c8a96e;color:#c8a96e}
.btn-row{display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-bottom:.4rem}

/* SFONDO AI */
.sfondo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.35rem}
.sfondo-btn{border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:.4rem;cursor:pointer;text-align:center;transition:all .2s;background:rgba(255,255,255,.04)}
.sfondo-btn:hover,.sfondo-btn.active{border-color:#c8a96e;background:rgba(200,169,110,.1)}
.sfondo-btn .icon{font-size:1.1rem}
.sfondo-btn .name{font-size:.62rem;color:rgba(255,255,255,.6);margin-top:.2rem}

/* BOTTOM BAR */
.bottom-bar{background:rgba(0,0,0,.4);border-top:1px solid rgba(200,169,110,.15);padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;flex-shrink:0}
.btn-primary{background:#c8a96e;color:#1a1a2e;border:none;border-radius:6px;padding:.55rem 1.1rem;font-size:.82rem;font-weight:600;cursor:pointer;font-family:Inter,sans-serif;transition:opacity .2s}
.btn-primary:hover{opacity:.85}
.btn-secondary{background:rgba(255,255,255,.08);color:#e8e4dc;border:1px solid rgba(255,255,255,.15);border-radius:6px;padding:.55rem 1.1rem;font-size:.82rem;cursor:pointer;font-family:Inter,sans-serif;transition:all .2s}
.btn-secondary:hover{background:rgba(255,255,255,.15)}
.btn-success{background:#2d6a4f;color:#fff;border:none;border-radius:6px;padding:.55rem 1.1rem;font-size:.82rem;font-weight:600;cursor:pointer;font-family:Inter,sans-serif}
.btn-danger-sm{background:rgba(196,75,58,.8);color:#fff;border:none;border-radius:6px;padding:.55rem 1.1rem;font-size:.82rem;cursor:pointer;font-family:Inter,sans-serif}
.status-msg{font-size:.8rem;color:rgba(255,255,255,.5);flex:1;text-align:center}

/* LOADING */
.loading-overlay{display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.75);z-index:100;align-items:center;justify-content:center;flex-direction:column;gap:1rem}
.loading-overlay.active{display:flex}
.spinner{width:40px;height:40px;border:3px solid rgba(200,169,110,.3);border-top-color:#c8a96e;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.loading-text{color:#c8a96e;font-size:.9rem}

input[type=file]{display:none}

/* WIZARD BFL */
.wizard-overlay{display:none;position:fixed;inset:0;background:rgba(5,5,15,.92);z-index:200;align-items:flex-end;justify-content:center;backdrop-filter:blur(6px)}
.wizard-overlay.open{display:flex}
.wizard-box{background:#111128;border:1px solid rgba(200,169,110,.2);border-radius:16px 16px 0 0;width:100%;max-width:680px;max-height:92vh;display:flex;flex-direction:column}
.wizard-header{padding:1.25rem 1.5rem;border-bottom:1px solid rgba(200,169,110,.1);display:flex;align-items:center;justify-content:space-between}
.wizard-title{font-size:1rem;font-weight:600;color:#e8e0d0}
.wizard-close{background:none;border:none;color:rgba(232,224,208,.5);font-size:1.5rem;cursor:pointer}
.wizard-steps{display:flex;padding:.75rem 1.5rem;gap:.5rem;border-bottom:1px solid rgba(200,169,110,.08)}
.wstep{flex:1;text-align:center;font-size:.7rem;color:rgba(255,255,255,.3);padding:.4rem;border-radius:6px;transition:all .2s}
.wstep.active{background:rgba(200,169,110,.15);color:#c8a96e;font-weight:500}
.wstep.done{color:rgba(200,169,110,.6)}
.wizard-body{flex:1;overflow-y:auto;padding:1.5rem}
.wizard-footer{padding:1rem 1.5rem;border-top:1px solid rgba(200,169,110,.08);display:flex;gap:.75rem;justify-content:flex-end}
.wbtn{padding:.65rem 1.5rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;font-family:Inter,sans-serif;border:none;transition:opacity .2s}
.wbtn-gold{background:linear-gradient(135deg,#c8a96e,#b8944a);color:#0d0d1a}
.wbtn-outline{background:transparent;border:1px solid rgba(200,169,110,.3);color:#c8a96e}
.wbtn:hover{opacity:.85}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid rgba(255,255,255,.05)}
.toggle-info{font-size:.875rem;color:#e8e0d0}
.toggle-sub{font-size:.75rem;color:rgba(255,255,255,.4);margin-top:.2rem}
.toggle-sw{width:44px;height:24px;background:#333;border-radius:12px;position:relative;cursor:pointer;transition:background .2s;flex-shrink:0}
.toggle-sw.on{background:#c8a96e}
.toggle-sw::after{content:'';position:absolute;width:20px;height:20px;background:#fff;border-radius:50%;top:2px;left:2px;transition:left .2s}
.toggle-sw.on::after{left:22px}
.outpaint-sliders{margin-top:1rem}
.outpaint-row{display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem}
.outpaint-row label{font-size:.8rem;color:rgba(255,255,255,.6);width:70px}
.outpaint-row input[type=range]{flex:1;accent-color:#c8a96e}
.outpaint-row span{font-size:.8rem;color:#c8a96e;width:40px;text-align:right}
.sfondo-galleria{display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-top:1rem}
.sfondo-card{border:2px solid rgba(255,255,255,.1);border-radius:8px;overflow:hidden;cursor:pointer;transition:all .2s;aspect-ratio:1}
.sfondo-card:hover,.sfondo-card.sel{border-color:#c8a96e}
.sfondo-card img{width:100%;height:100%;object-fit:cover;display:block}
.sfondo-card .sfondo-label{font-size:.65rem;text-align:center;padding:.3rem;background:rgba(0,0,0,.6);color:#e8e0d0}
.upload-sfondo{border:2px dashed rgba(200,169,110,.3);border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;color:rgba(255,255,255,.4);font-size:.8rem;transition:all .2s}
.upload-sfondo:hover{border-color:#c8a96e;color:#c8a96e}
.preview-compare{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem}
.preview-compare img{width:100%;border-radius:8px;border:1px solid rgba(200,169,110,.2)}
.preview-compare .plabel{font-size:.72rem;color:rgba(255,255,255,.4);text-align:center;margin-top:.35rem}
</style>
</head>
<body>

<nav>
    <div class="logo">MemorAI — Gestione Foto</div>
    <div style="display:flex;align-items:center;gap:1rem">
        <span style="color:rgba(255,255,255,.5);font-size:.8rem">{{ $nomePratica }}</span>
        <a href="{{ url('/') }}" class="nav-back">← Torna al sito</a>
    </div>
</nav>

<div class="layout">

    <!-- GALLERIA SINISTRA -->
    <div class="gallery-panel">
        <div class="panel-title">Galleria foto ({{ count($photos) }})</div>
        <div class="upload-zone" onclick="document.getElementById('upload-input').click()">
            <div class="upload-zone-icon">📷</div>
            <div class="upload-zone-text">Carica nuova foto</div>
        </div>
        <input type="file" id="upload-input" accept="image/*" onchange="uploadFoto(this)">
        <div class="gallery-list" id="gallery-list">
            @foreach($photos as $photo)
            <div class="gallery-item {{ $photo->is_principale ? 'active' : '' }}" 
                 id="gallery-item-{{ $photo->id }}"
                 onclick="loadPhotoInCanvas('{{ $photo->url }}', {{ $photo->id }})">
                <img src="{{ $photo->url }}" alt="">
                @if($photo->is_principale)
                <div class="badge-principale">★ Principale</div>
                @endif
                <div class="tipo-badge">{{ ucfirst($photo->tipo) }}</div>
                <button class="btn-delete" onclick="eliminaFoto(event, {{ $photo->id }})">✕</button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- CANVAS -->
    <div class="canvas-area" id="canvas-area">
        <div id="empty-state" style="text-align:center;color:rgba(255,255,255,.3)">
            <div style="font-size:3rem;margin-bottom:1rem">🖼</div>
            <div style="font-size:.9rem">Carica una foto o selezionala dalla galleria</div>
        </div>
        <div class="canvas-wrapper" id="canvas-wrapper" style="display:none">
            <canvas id="foto-canvas"></canvas>
        </div>
        <div class="loading-overlay" id="loading-overlay">
            <div class="spinner"></div>
            <div class="loading-text" id="loading-text">Elaborazione in corso...</div>
        </div>
    </div>

    <!-- STRUMENTI DESTRA -->
    <div class="tools-panel">

        <div class="tool-section">
            <div class="tool-label">Regolazioni</div>
            <div class="slider-row">
                <label>Luminosità</label>
                <input type="range" id="sl-brightness" min="-1" max="1" step="0.05" value="0" oninput="applyFilters()">
                <span id="val-brightness">0</span>
            </div>
            <div class="slider-row">
                <label>Contrasto</label>
                <input type="range" id="sl-contrast" min="-1" max="1" step="0.05" value="0" oninput="applyFilters()">
                <span id="val-contrast">0</span>
            </div>
            <div class="slider-row">
                <label>Saturazione</label>
                <input type="range" id="sl-saturation" min="-1" max="1" step="0.05" value="0" oninput="applyFilters()">
                <span id="val-saturation">0</span>
            </div>
            <div class="slider-row">
                <label>Nitidezza</label>
                <input type="range" id="sl-sharpness" min="0" max="1" step="0.05" value="0" oninput="applyFilters()">
                <span id="val-sharpness">0</span>
            </div>
            <button class="btn-tool" onclick="resetFilters()">↺ Reset filtri</button>
        </div>

        <div class="tool-section">
            <div class="tool-label">Trasforma</div>
            <div class="btn-row">
                <button class="btn-tool" onclick="rotatePhoto(-90)">↺ -90°</button>
                <button class="btn-tool" onclick="rotatePhoto(90)">↻ +90°</button>
            </div>
            <div class="btn-row">
                <button class="btn-tool" onclick="flipPhoto('h')">↔ Specchio H</button>
                <button class="btn-tool" onclick="flipPhoto('v')">↕ Specchio V</button>
            </div>
            <button class="btn-tool" onclick="cropMode()" id="btn-crop">✂ Ritaglia</button>
            <button class="btn-tool" onclick="resetTransform()">↺ Reset trasformazioni</button>
        </div>

        <div class="tool-section">
            <div class="tool-label">✨ AI Photo Wizard</div>
            <div style="font-size:.72rem;color:rgba(255,255,255,.4);margin-bottom:.75rem;line-height:1.5">
                Migliora, espandi e cambia sfondo con MemorAI
            </div>
            <button class="btn-tool" onclick="apriWizard()" style="border-color:#c8a96e;color:#c8a96e;background:rgba(200,169,110,.08);font-weight:600">
                ✨ Avvia AI Photo Wizard
            </button>
        </div>

        <div class="tool-section">
            <div class="tool-label">Salva</div>
            <button class="btn-tool" onclick="salvaFoto(false)" style="border-color:#c8a96e;color:#c8a96e">
                💾 Salva nella galleria
            </button>
            <button class="btn-tool" onclick="salvaFoto(true)" style="border-color:#2d6a4f;color:#4ade80">
                ★ Salva come principale
            </button>
        </div>

    </div>

</div>

<!-- BOTTOM BAR -->
<div class="bottom-bar">
    <span class="status-msg" id="status-msg">Seleziona una foto dalla galleria o caricane una nuova</span>
    <a href="{{ url('/studio/ricordino') }}" class="btn-secondary" style="font-size:.75rem">→ Ricordino</a>
</div>

<script>
const praticaId = {{ $praticaId }};
const csrfToken = '{{ csrf_token() }}';
const studioToken = '{{ config('photoprint.studio_token') }}';
// Allega automaticamente il token a ogni chiamata verso /admin/api/ (guard Fase 1).
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
let canvas = null;
let currentPhotoId = null;
let currentImg = null;
let sfondoSelezionato = 'cielo celeste con nuvole bianche delicate';
let originalImageData = null;

function initCanvas(w, h) {
    if (canvas) { canvas.dispose(); }
    document.getElementById('canvas-wrapper').style.display = 'block';
    document.getElementById('empty-state').style.display = 'none';
    const area = document.getElementById('canvas-area');
    const maxW = area.clientWidth - 40;
    const maxH = area.clientHeight - 40;
    const scale = Math.min(maxW / w, maxH / h, 1);
    const cW = Math.round(w * scale);
    const cH = Math.round(h * scale);
    document.getElementById('foto-canvas').width = cW;
    document.getElementById('foto-canvas').height = cH;
    canvas = new fabric.Canvas('foto-canvas', { selection: false });
    return { cW, cH, scale };
}

function loadPhotoInCanvas(url, photoId) {
    url = (url || "").replace(/^https?:\/\/[^\/]+/i, "");
    currentPhotoId = photoId;
    document.querySelectorAll('.gallery-item').forEach(el => el.style.borderColor = 'transparent');
    const item = document.getElementById('gallery-item-' + photoId);
    if (item) item.style.borderColor = '#c8a96e';
    setStatus('Caricamento foto...');
    fabric.Image.fromURL(url, function(img) {
        const { cW, cH, scale } = initCanvas(img.width, img.height);
        img.set({ left: 0, top: 0, scaleX: cW / img.width, scaleY: cH / img.height, selectable: false });
        canvas.add(img);
        canvas.renderAll();
        currentImg = img;
        originalImageData = url;
        resetSliders();
        setStatus('Foto caricata — usa gli strumenti per modificarla');
    }, { crossOrigin: 'anonymous' });
}

function uploadFoto(input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('pratica_id', praticaId);
    formData.append('_token', csrfToken);
    setStatus('Caricamento in corso...');
    showLoading('Caricamento foto...');
    fetch('/admin/api/foto-pratica/upload', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                addToGallery(data.photo);
                loadPhotoInCanvas(data.photo.url, data.photo.id);
                setStatus('Foto caricata con successo');
            } else {
                setStatus('Errore: ' + (data.error || 'upload fallito'));
            }
        }).catch(() => { hideLoading(); setStatus('Errore di connessione'); });
    input.value = '';
}

function addToGallery(photo) {
    const list = document.getElementById('gallery-list');
    const div = document.createElement('div');
    div.className = 'gallery-item';
    div.id = 'gallery-item-' + photo.id;
    div.onclick = function() { loadPhotoInCanvas(photo.url, photo.id); };
    div.innerHTML = '<img src="' + photo.url + '" style="width:100%;height:140px;object-fit:cover;display:block">' +
        '<div class="tipo-badge">' + (photo.tipo || 'originale') + '</div>' +
        '<button class="btn-delete" onclick="eliminaFoto(event,' + photo.id + ')">✕</button>';
    list.appendChild(div);
}

function eliminaFoto(e, id) {
    e.stopPropagation();
    if (!confirm('Eliminare questa foto?')) return;
    fetch('/admin/api/foto-pratica/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('gallery-item-' + id)?.remove();
            if (currentPhotoId === id) {
                canvas?.dispose();
                canvas = null;
                currentPhotoId = null;
                document.getElementById('canvas-wrapper').style.display = 'none';
                document.getElementById('empty-state').style.display = 'block';
            }
        }
    });
}

function applyFilters() {
    if (!currentImg) return;
    const brightness = parseFloat(document.getElementById('sl-brightness').value);
    const contrast = parseFloat(document.getElementById('sl-contrast').value);
    const saturation = parseFloat(document.getElementById('sl-saturation').value);
    const sharpness = parseFloat(document.getElementById('sl-sharpness').value);
    document.getElementById('val-brightness').textContent = brightness.toFixed(2);
    document.getElementById('val-contrast').textContent = contrast.toFixed(2);
    document.getElementById('val-saturation').textContent = saturation.toFixed(2);
    document.getElementById('val-sharpness').textContent = sharpness.toFixed(2);
    currentImg.filters = [];
    if (brightness !== 0) currentImg.filters.push(new fabric.Image.filters.Brightness({ brightness }));
    if (contrast !== 0) currentImg.filters.push(new fabric.Image.filters.Contrast({ contrast }));
    if (saturation !== 0) currentImg.filters.push(new fabric.Image.filters.Saturation({ saturation }));
    if (sharpness > 0) currentImg.filters.push(new fabric.Image.filters.Convolute({
        matrix: [0, -sharpness, 0, -sharpness, 1 + 4 * sharpness, -sharpness, 0, -sharpness, 0]
    }));
    currentImg.applyFilters();
    canvas.renderAll();
}

function resetFilters() {
    resetSliders();
    if (currentImg) { currentImg.filters = []; currentImg.applyFilters(); canvas.renderAll(); }
}

function resetSliders() {
    ['brightness','contrast','saturation','sharpness'].forEach(function(f) {
        document.getElementById('sl-' + f).value = 0;
        document.getElementById('val-' + f).textContent = '0';
    });
}

function rotatePhoto(angle) {
    if (!currentImg) return;
    currentImg.rotate((currentImg.angle || 0) + angle);
    canvas.renderAll();
}

function flipPhoto(dir) {
    if (!currentImg) return;
    if (dir === 'h') currentImg.set('flipX', !currentImg.flipX);
    else currentImg.set('flipY', !currentImg.flipY);
    canvas.renderAll();
}

function resetTransform() {
    if (!currentImg) return;
    currentImg.set({ angle: 0, flipX: false, flipY: false, scaleX: canvas.getWidth() / currentImg.width, scaleY: canvas.getHeight() / currentImg.height, left: 0, top: 0 });
    canvas.renderAll();
}

function cropMode() {
    if (!currentImg) return;
    const btn = document.getElementById('btn-crop');
    if (btn.classList.contains('active')) {
        const rect = canvas.getObjects().find(o => o.customType === 'crop-rect');
        if (!rect) { btn.classList.remove('active'); return; }

        // Usa toDataURL di Fabric con clipping — funziona correttamente su iPad/touch
        const left = Math.max(0, Math.round(rect.left));
        const top = Math.max(0, Math.round(rect.top));
        const width = Math.min(Math.round(rect.getScaledWidth()), canvas.getWidth() - left);
        const height = Math.min(Math.round(rect.getScaledHeight()), canvas.getHeight() - top);

        // Nascondi il rect temporaneamente per non includerlo nel crop
        rect.visible = false;
        canvas.renderAll();

        // Esporta l'area tramite Fabric.js con moltiplicatore 1 (coordinate canvas reali)
        const croppedUrl = canvas.toDataURL({
            format: 'jpeg',
            quality: 0.92,
            left: left,
            top: top,
            width: width,
            height: height,
            multiplier: 1
        });

        canvas.remove(rect);

        fabric.Image.fromURL(croppedUrl, function(img) {
            const area = document.getElementById('canvas-area');
            const maxW = area.clientWidth - 40;
            const maxH = area.clientHeight - 40;
            const scale = Math.min(maxW / img.width, maxH / img.height, 1);
            const cW = Math.round(img.width * scale);
            const cH = Math.round(img.height * scale);
            document.getElementById('foto-canvas').width = cW;
            document.getElementById('foto-canvas').height = cH;
            canvas.setWidth(cW);
            canvas.setHeight(cH);
            img.set({ left: 0, top: 0, scaleX: cW / img.width, scaleY: cH / img.height, selectable: false });
            canvas.clear();
            canvas.add(img);
            canvas.renderAll();
            currentImg = img;
        });

        btn.classList.remove('active');
        setStatus('Ritaglio applicato — salva per conservare');
    } else {
        btn.classList.add('active');
        const w = canvas.getWidth();
        const h = canvas.getHeight();
        const rect = new fabric.Rect({
            left: w * 0.1, top: h * 0.1,
            width: w * 0.8, height: h * 0.8,
            fill: 'rgba(200,169,110,0.08)',
            stroke: '#c8a96e', strokeWidth: 3,
            strokeDashArray: [8, 4],
            selectable: true, hasControls: true,
            hasBorders: true,
            lockRotation: true,
            customType: 'crop-rect',
            cornerColor: '#c8a96e',
            cornerSize: 14,
            transparentCorners: false,
        });
        canvas.add(rect);
        canvas.setActiveObject(rect);
        canvas.renderAll();
        setStatus('Trascina e ridimensiona il riquadro dorato — clicca ✂ di nuovo per ritagliare');
    }
}

function selectSfondo(el, sfondo) {
    document.querySelectorAll('.sfondo-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    sfondoSelezionato = sfondo;
}

function elaboraConAI() {
    if (!currentPhotoId) { alert('Carica prima una foto'); return; }
    showLoading('MemorAI sta elaborando la foto (30-60 secondi)...');
    // Esporta canvas come blob
    const dataUrl = canvas.toDataURL({ format: 'png', quality: 1 });
    fetch(dataUrl).then(r => r.blob()).then(blob => {
        const formData = new FormData();
        formData.append('photo', blob, 'foto.png');
        formData.append('sfondo', sfondoSelezionato);
        formData.append('pratica_id', praticaId);
        formData.append('photo_id', currentPhotoId);
        formData.append('_token', csrfToken);
        return fetch('/admin/api/foto-pratica/elabora', { method: 'POST', body: formData });
    }).then(r => r.json()).then(data => {
        hideLoading();
        if (data.success) {
            addToGallery(data.photo);
            loadPhotoInCanvas(data.photo.url, data.photo.id);
            setStatus('✓ Foto elaborata con AI e salvata nella galleria');
        } else {
            setStatus('Errore: ' + (data.error || 'elaborazione fallita'));
        }
    }).catch(() => { hideLoading(); setStatus('Errore di connessione'); });
}

function salvaFoto(isPrincipale) {
    if (!canvas) { alert('Nessuna foto da salvare'); return; }
    showLoading('Salvataggio...');
    const dataUrl = canvas.toDataURL({ format: 'jpeg', quality: 0.92 });
    fetch('/admin/api/foto-pratica/salva', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            pratica_id: praticaId,
            image: dataUrl,
            tipo: 'ritagliata',
            is_principale: isPrincipale ? 1 : 0,
            source_id: currentPhotoId
        })
    }).then(r => r.json()).then(data => {
        hideLoading();
        if (data.success) {
            if (isPrincipale) {
                document.querySelectorAll('.badge-principale').forEach(b => b.remove());
                document.querySelectorAll('.gallery-item').forEach(b => b.classList.remove('active'));
            }
            addToGallery(data.photo);
            setStatus(isPrincipale ? '★ Salvata come foto principale!' : '✓ Salvata nella galleria');
        }
    }).catch(() => { hideLoading(); setStatus('Errore nel salvataggio'); });
}

function showLoading(text) {
    document.getElementById('loading-text').textContent = text || 'Elaborazione...';
    document.getElementById('loading-overlay').classList.add('active');
}

function hideLoading() {
    document.getElementById('loading-overlay').classList.remove('active');
}

function setStatus(msg) {
    document.getElementById('status-msg').textContent = msg;
}

// Carica prima foto se presente
window.addEventListener('load', function() {
    @if(count($photos) > 0)
    loadPhotoInCanvas('{{ $photos[0]->url }}', {{ $photos[0]->id }});
    @endif
});

// ── TOUCH / PINCH ZOOM per iPad ──
(function() {
    var lastDist = 0;
    var lastZoomScale = 1;

    function getTouchDist(touches) {
        var dx = touches[0].clientX - touches[1].clientX;
        var dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx*dx + dy*dy);
    }

    var canvasEl = document.getElementById('canvas-area');
    if (!canvasEl) return;

    canvasEl.addEventListener('touchstart', function(e) {
        if (e.touches.length === 2 && canvas) {
            e.preventDefault();
            lastDist = getTouchDist(e.touches);
            lastZoomScale = canvas.getZoom();
        }
    }, { passive: false });

    canvasEl.addEventListener('touchmove', function(e) {
        if (e.touches.length === 2 && canvas) {
            e.preventDefault();
            var dist = getTouchDist(e.touches);
            var ratio = dist / lastDist;
            var newZoom = Math.max(0.3, Math.min(3, lastZoomScale * ratio));
            var center = {
                x: (e.touches[0].clientX + e.touches[1].clientX) / 2,
                y: (e.touches[0].clientY + e.touches[1].clientY) / 2
            };
            var canvasRect = canvas.getElement().getBoundingClientRect();
            var point = new fabric.Point(
                center.x - canvasRect.left,
                center.y - canvasRect.top
            );
            canvas.zoomToPoint(point, newZoom);
            canvas.renderAll();
        }
    }, { passive: false });

    canvasEl.addEventListener('touchend', function(e) {
        if (e.touches.length < 2) lastDist = 0;
    }, { passive: false });
})();
</script>
<!-- AI PHOTO WIZARD -->
<div class="wizard-overlay" id="wizard-overlay">
<div class="wizard-box">
    <div class="wizard-header">
        <div class="wizard-title">✨ AI Photo Wizard</div>
        <button class="wizard-close" onclick="chiudiWizard()">✕</button>
    </div>
    <div class="wizard-steps">
        <div class="wstep active" id="wstep-1">1. Migliora</div>
        <div class="wstep" id="wstep-2">2. Outpainting</div>
        <div class="wstep" id="wstep-3">3. Sfondo</div>
        <div class="wstep" id="wstep-4">4. Risultato</div>
    </div>
    <div class="wizard-body" id="wizard-body">

        <!-- STEP 1: MIGLIORA -->
        <div id="ws-1">
            <div style="text-align:center;margin-bottom:1rem">
                <img id="ws1-preview" src="" style="max-height:200px;max-width:100%;border-radius:8px;border:1px solid rgba(200,169,110,.2);object-fit:contain">
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-info">Isola soggetto e migliora qualità</div>
                    <div class="toggle-sub">Rimuove elementi estranei, isola il soggetto e migliora la qualità del ritratto</div>
                </div>
                <div class="toggle-sw" id="toggle-enhance" onclick="this.classList.toggle('on')"></div>
            </div>
        </div>

        <!-- STEP 2: OUTPAINTING -->
        <div id="ws-2" style="display:none">
            <div class="toggle-row">
                <div>
                    <div class="toggle-info">Espandi immagine (Outpainting)</div>
                    <div class="toggle-sub">Aggiunge spazio attorno alla foto generato dall'AI</div>
                </div>
                <div class="toggle-sw" id="toggle-outpaint" onclick="toggleOutpaint(this)"></div>
            </div>
            <div id="outpaint-sliders" style="display:none">
                <!-- Anteprima visiva outpainting -->
                <div style="margin:.75rem 0;text-align:center">
                    <div style="position:relative;display:inline-block;background:rgba(200,169,110,.08);border:1px dashed rgba(200,169,110,.3);padding:0" id="outpaint-preview-wrap">
                        <div id="outpaint-top-bar" style="background:rgba(200,169,110,.2);width:100%;transition:height .15s" ></div>
                        <div style="display:flex">
                            <div id="outpaint-left-bar" style="background:rgba(200,169,110,.2);transition:width .15s"></div>
                            <img id="ws2-preview" src="" style="width:120px;height:160px;object-fit:cover;display:block">
                            <div id="outpaint-right-bar" style="background:rgba(200,169,110,.2);transition:width .15s"></div>
                        </div>
                        <div id="outpaint-bottom-bar" style="background:rgba(200,169,110,.2);width:100%;transition:height .15s"></div>
                    </div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.3);margin-top:.4rem">Area dorata = zona espansa dall'AI</div>
                </div>
                <div class="outpaint-sliders">
                <div class="outpaint-row">
                    <label>⬆ Alto</label>
                    <input type="range" id="op-top" min="0" max="400" step="10" value="0" oninput="aggiornaPrevOutpaint()">
                    <span id="op-top-val">0px</span>
                </div>
                <div class="outpaint-row">
                    <label>⬇ Basso</label>
                    <input type="range" id="op-bottom" min="0" max="400" step="10" value="0" oninput="aggiornaPrevOutpaint()">
                    <span id="op-bottom-val">0px</span>
                </div>
                <div class="outpaint-row">
                    <label>⬅ Sinistra</label>
                    <input type="range" id="op-left" min="0" max="400" step="10" value="0" oninput="aggiornaPrevOutpaint()">
                    <span id="op-left-val">0px</span>
                </div>
                <div class="outpaint-row">
                    <label>➡ Destra</label>
                    <input type="range" id="op-right" min="0" max="400" step="10" value="0" oninput="aggiornaPrevOutpaint()">
                    <span id="op-right-val">0px</span>
                </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: SFONDO -->
        <div id="ws-3" style="display:none">
            <div style="font-size:.875rem;color:#e8e0d0;margin-bottom:1rem">Scegli il nuovo sfondo</div>
            <div class="sfondo-galleria" id="sfondo-galleria">
                <div class="sfondo-card sel" data-prompt="plain white background" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem">⬜</div>
                    <div class="sfondo-label">Bianco</div>
                </div>
                <div class="sfondo-card" data-prompt="light gray neutral background, elegant studio" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:#ccc;display:flex;align-items:center;justify-content:center;font-size:2rem">🌫️</div>
                    <div class="sfondo-label">Grigio</div>
                </div>
                <div class="sfondo-card" data-prompt="soft light blue sky background, peaceful" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#87CEEB,#b0e0f7);display:flex;align-items:center;justify-content:center;font-size:2rem">☁️</div>
                    <div class="sfondo-label">Celeste</div>
                </div>
                <div class="sfondo-card" data-prompt="soft blue sky with light clouds, gentle gradient" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#4a90d9,#87CEEB);display:flex;align-items:center;justify-content:center;font-size:2rem">🌤️</div>
                    <div class="sfondo-label">Cielo sfumato</div>
                </div>
                <div class="sfondo-card" data-prompt="golden divine light background, celestial, peaceful" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#f9d423,#f4a61a);display:flex;align-items:center;justify-content:center;font-size:2rem">✨</div>
                    <div class="sfondo-label">Dorato</div>
                </div>
                <div class="sfondo-card" data-prompt="serene ocean sea background, blue calm water" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#006994,#0099cc);display:flex;align-items:center;justify-content:center;font-size:2rem">🌊</div>
                    <div class="sfondo-label">Mare</div>
                </div>
                <div class="sfondo-card" data-prompt="green nature mountains hills background, peaceful landscape" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#2d6a4f,#52b788);display:flex;align-items:center;justify-content:center;font-size:2rem">⛰️</div>
                    <div class="sfondo-label">Montagne</div>
                </div>
                <div class="sfondo-card" data-prompt="green hills and countryside landscape background" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#74b72e,#a8d5a2);display:flex;align-items:center;justify-content:center;font-size:2rem">🌄</div>
                    <div class="sfondo-label">Colline</div>
                </div>
                <div class="sfondo-card" data-prompt="lush green plants and leaves background, nature" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#1b4332,#40916c);display:flex;align-items:center;justify-content:center;font-size:2rem">🌿</div>
                    <div class="sfondo-label">Piante</div>
                </div>
                <div class="sfondo-card" data-prompt="warm golden sunset sky background, orange pink colors" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#f4831a,#f9c74f);display:flex;align-items:center;justify-content:center;font-size:2rem">🌅</div>
                    <div class="sfondo-label">Tramonto</div>
                </div>
                <div class="sfondo-card" data-prompt="white roses flowers background, floral elegant" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#fce4ec,#f8bbd0);display:flex;align-items:center;justify-content:center;font-size:2rem">🌸</div>
                    <div class="sfondo-label">Floreale</div>
                </div>
                <div class="sfondo-card" data-prompt="dark blue night sky with subtle stars background" onclick="selSfondo(this)">
                    <div style="width:100%;height:100%;background:linear-gradient(180deg,#0d0d2b,#1a1a4e);display:flex;align-items:center;justify-content:center;font-size:2rem">🌙</div>
                    <div class="sfondo-label">Notte</div>
                </div>
            </div>
            <div class="upload-sfondo" style="margin-top:.75rem" onclick="document.getElementById('sfondo-upload-input').click()">
                📂 Carica sfondo dal dispositivo
            </div>
            <input type="file" id="sfondo-upload-input" accept="image/*" onchange="caricaSfondoCustom(this)">
        </div>

        <!-- STEP 4: RISULTATO -->
        <div id="ws-4" style="display:none">
            <div style="font-size:.875rem;color:#e8e0d0;margin-bottom:1rem;text-align:center" id="ws4-status">Elaborazione in corso...</div>
            <div id="ws4-loading" style="text-align:center;padding:2rem">
                <div class="spinner" style="margin:0 auto 1rem"></div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.4)" id="ws4-step-label">Avvio wizard...</div>
            </div>
            <div id="ws4-result" style="display:none">
                <div class="preview-compare">
                    <div>
                        <img id="ws4-original" src="" alt="Originale">
                        <div class="plabel">Originale</div>
                    </div>
                    <div>
                        <img id="ws4-elaborata" src="" alt="Elaborata">
                        <div class="plabel">Elaborata AI ✨</div>
                    </div>
                </div>
                <div style="display:flex;gap:.75rem;margin-top:1.25rem;justify-content:center;flex-wrap:wrap">
                    <button class="wbtn wbtn-gold" onclick="salvaRisultato(false)">💾 Salva in galleria</button>
                    <button class="wbtn wbtn-gold" onclick="salvaRisultato(true)">★ Salva come principale</button>
                    <button class="wbtn wbtn-outline" onclick="chiudiWizard()">Annulla</button>
                </div>
            </div>
        </div>

    </div>
    <div class="wizard-footer">
        <button class="wbtn wbtn-outline" id="wbtn-back" style="display:none" onclick="wizardBack()">← Indietro</button>
        <button class="wbtn wbtn-gold" id="wbtn-next" onclick="wizardNext()">Avanti →</button>
    </div>
</div>
</div>

<script>
// ── WIZARD BFL ──
let wizardStep = 1;
let wizardImageUrl = null;
let wizardResultUrl = null;
let sfondoSelezionatoWizard = 'plain white background';
let sfondoCustomBase64 = null;

function apriWizard() {
    if (!currentPhotoId) { alert('Seleziona prima una foto dalla galleria'); return; }
    wizardStep = 1;
    // Usa il canvas corrente (già croppato) invece dell'originale
    if (canvas) {
        // Usa multiplier 2 per alta qualità, esporta solo l'immagine senza oggetti UI
        var objs = canvas.getObjects();
        objs.forEach(function(o){ if(o.customType === 'crop-rect') o.visible = false; });
        canvas.renderAll();
        // Clamp lato lungo a 2048 per non sforare il budget BFL (2048*2048 px)
        var BFL_MAX_SIDE = 2048;
        var _bw = canvas.getWidth(), _bh = canvas.getHeight();
        var _mult = Math.min(2, BFL_MAX_SIDE / Math.max(_bw, _bh));
        wizardImageUrl = canvas.toDataURL({ format: 'jpeg', quality: 0.95, multiplier: _mult });
        objs.forEach(function(o){ if(o.customType === 'crop-rect') o.visible = true; });
        canvas.renderAll();
    } else {
        wizardImageUrl = originalImageData;
    }
    wizardResultUrl = null;
    document.getElementById('wizard-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    // Mostra anteprima nel wizard
    document.getElementById('ws1-preview').src = wizardImageUrl;
    aggiornaWizardUI();
}

function toggleOutpaint(el) {
    el.classList.toggle('on');
    var show = el.classList.contains('on');
    document.getElementById('outpaint-sliders').style.display = show ? 'block' : 'none';
    if (show) {
        document.getElementById('ws2-preview').src = wizardImageUrl;
        // Reset sliders
        ['op-top','op-bottom','op-left','op-right'].forEach(function(id){
            document.getElementById(id).value = 0;
        });
        aggiornaPrevOutpaint();
    }
}

function aggiornaWs2Preview() {
    if (document.getElementById('ws-2').style.display !== 'none') {
        document.getElementById('ws2-preview').src = wizardImageUrl;
    }
}

function aggiornaPrevOutpaint() {
    var maxPx = 400;
    var top = parseInt(document.getElementById('op-top').value);
    var bottom = parseInt(document.getElementById('op-bottom').value);
    var left = parseInt(document.getElementById('op-left').value);
    var right = parseInt(document.getElementById('op-right').value);
    document.getElementById('op-top-val').textContent = top + 'px';
    document.getElementById('op-bottom-val').textContent = bottom + 'px';
    document.getElementById('op-left-val').textContent = left + 'px';
    document.getElementById('op-right-val').textContent = right + 'px';
    // Scala i valori per la preview (max 50px visivi)
    var scale = 50 / maxPx;
    var tH = Math.max(top > 0 ? 4 : 0, Math.round(top * scale));
    var bH = Math.max(bottom > 0 ? 4 : 0, Math.round(bottom * scale));
    var lW = Math.max(left > 0 ? 4 : 0, Math.round(left * scale));
    var rW = Math.max(right > 0 ? 4 : 0, Math.round(right * scale));
    document.getElementById('outpaint-top-bar').style.height = tH + 'px';
    document.getElementById('outpaint-bottom-bar').style.height = bH + 'px';
    document.getElementById('outpaint-left-bar').style.width = lW + 'px';
    document.getElementById('outpaint-right-bar').style.width = rW + 'px';
}

function chiudiWizard() {
    document.getElementById('wizard-overlay').classList.remove('open');
    document.body.style.overflow = '';
}

function aggiornaWizardUI() {
    // Steps
    for (var i = 1; i <= 4; i++) {
        var el = document.getElementById('wstep-' + i);
        el.classList.remove('active','done');
        if (i === wizardStep) el.classList.add('active');
        else if (i < wizardStep) el.classList.add('done');
    }
    // Panels
    for (var j = 1; j <= 4; j++) {
        document.getElementById('ws-' + j).style.display = j === wizardStep ? 'block' : 'none';
    }
    // Bottoni
    document.getElementById('wbtn-back').style.display = wizardStep > 1 && wizardStep < 4 ? 'block' : 'none';
    document.getElementById('wbtn-next').style.display = wizardStep < 4 ? 'block' : 'none';
    if (wizardStep === 3) document.getElementById('wbtn-next').textContent = '✨ Avvia elaborazione';
    else document.getElementById('wbtn-next').textContent = 'Avanti →';
}

function wizardBack() {
    if (wizardStep > 1) { wizardStep--; aggiornaWizardUI(); }
}

async function wizardNext() {
    if (wizardStep < 3) { wizardStep++; aggiornaWizardUI(); return; }
    if (wizardStep === 3) {
        wizardStep = 4;
        aggiornaWizardUI();
        await eseguiWizard();
    }
}

async function eseguiWizard() {
    var label = document.getElementById('ws4-step-label');
    document.getElementById('ws4-loading').style.display = 'block';
    document.getElementById('ws4-result').style.display = 'none';
    document.getElementById('ws4-status').textContent = 'Elaborazione in corso...';

    var currentUrl = wizardImageUrl;
    // Se è base64, carica prima sul server e ottieni URL pubblico
    if (currentUrl && currentUrl.startsWith('data:')) {
        label.textContent = '⬆️ Caricamento immagine...';
        try {
            var uploadResp = await fetch('/admin/api/foto-pratica/upload-temp', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({ image_data: currentUrl, pratica_id: praticaId })
            });
            var uploadData = await uploadResp.json();
            if (uploadData.url) currentUrl = uploadData.url;
            else throw new Error('Upload fallito');
        } catch(e) {
            throw new Error('Errore caricamento immagine: ' + e.message);
        }
    }
    if (currentUrl && !currentUrl.startsWith('http')) { currentUrl = window.location.protocol + '//' + window.location.host + currentUrl; }
    var enhance = document.getElementById('toggle-enhance').classList.contains('on');
    var outpaint = document.getElementById('toggle-outpaint').classList.contains('on');

    try {
        // STEP 1: ENHANCE
        if (enhance) {
            label.textContent = '✨ Miglioramento qualità...';
            var r = await fetch('/admin/api/bfl/enhance', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({image_url: currentUrl})
            });
            var d = await r.json();
            if (d.url) currentUrl = d.url;
            else throw new Error('Enhance fallito: ' + JSON.stringify(d));
        }

        // STEP 2: OUTPAINT
        if (outpaint) {
            label.textContent = '🖼️ Espansione immagine...';
            // Clamp padding: la dimensione finale (immagine+padding) non deve superare 2048*2048 px
            var OUTPAINT_BUDGET = 2048 * 2048;
            var _om = canvas ? Math.min(2, 2048 / Math.max(canvas.getWidth(), canvas.getHeight())) : 1;
            var _iw = canvas ? Math.round(canvas.getWidth() * _om) : 2048;
            var _ih = canvas ? Math.round(canvas.getHeight() * _om) : 2048;
            var _pt = parseInt(document.getElementById('op-top').value) || 0;
            var _pb = parseInt(document.getElementById('op-bottom').value) || 0;
            var _pl = parseInt(document.getElementById('op-left').value) || 0;
            var _pr = parseInt(document.getElementById('op-right').value) || 0;
            var _fw = _iw + _pl + _pr, _fh = _ih + _pt + _pb;
            if (_fw * _fh > OUTPAINT_BUDGET) {
                var _factor = Math.sqrt(OUTPAINT_BUDGET / (_fw * _fh));
                _pt = Math.floor(_pt * _factor); _pb = Math.floor(_pb * _factor);
                _pl = Math.floor(_pl * _factor); _pr = Math.floor(_pr * _factor);
            }
            var r2 = await fetch('/admin/api/bfl/outpaint', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({
                    image_url: currentUrl,
                    top: _pt,
                    bottom: _pb,
                    left: _pl,
                    right: _pr,
                })
            });
            var d2 = await r2.json();
            if (d2.url) currentUrl = d2.url;
            else throw new Error('Outpaint fallito: ' + JSON.stringify(d2));
        }

        // STEP 3: REMOVE BG + SFONDO
        label.textContent = '🎨 Rimozione sfondo e applicazione...';
        var bgPrompt = sfondoCustomBase64 ? 'background from reference image' : sfondoSelezionatoWizard;
        var r3 = await fetch('/admin/api/bfl/remove-bg', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
            body: JSON.stringify({image_url: currentUrl, background_prompt: bgPrompt})
        });
        var d3 = await r3.json();
        if (d3.url) currentUrl = d3.url;
        else throw new Error('Remove BG fallito: ' + JSON.stringify(d3));

        // RISULTATO
        wizardResultUrl = currentUrl;
        document.getElementById('ws4-original').src = wizardImageUrl;
        document.getElementById('ws4-elaborata').src = currentUrl;
        document.getElementById('ws4-loading').style.display = 'none';
        document.getElementById('ws4-result').style.display = 'block';
        document.getElementById('ws4-status').textContent = '✅ Elaborazione completata!';
        document.getElementById('wbtn-next').style.display = 'none';

    } catch(err) {
        document.getElementById('ws4-loading').style.display = 'none';
        document.getElementById('ws4-status').textContent = '❌ Errore: ' + err.message;
        document.getElementById('wbtn-back').style.display = 'block';
    }
}

function selSfondo(el) {
    document.querySelectorAll('.sfondo-card').forEach(c => c.classList.remove('sel'));
    el.classList.add('sel');
    sfondoSelezionatoWizard = el.dataset.prompt;
    sfondoCustomBase64 = null;
}

function caricaSfondoCustom(input) {
    if (!input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        sfondoCustomBase64 = e.target.result;
        sfondoSelezionatoWizard = 'custom uploaded background';
        document.querySelectorAll('.sfondo-card').forEach(c => c.classList.remove('sel'));
        var preview = document.querySelector('.upload-sfondo');
        preview.textContent = '✓ Sfondo caricato — ' + input.files[0].name;
        preview.style.borderColor = '#c8a96e';
        preview.style.color = '#c8a96e';
    };
    reader.readAsDataURL(input.files[0]);
}

function salvaRisultato(isPrincipale) {
    if (!wizardResultUrl) return;
    showLoading('Salvataggio in galleria...');
    chiudiWizard();
    fetch('/admin/api/foto-pratica/salva-url', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
        body: JSON.stringify({
            pratica_id: praticaId,
            image_url: wizardResultUrl,
            tipo: 'elaborata_ai',
            is_principale: isPrincipale ? 1 : 0,
            source_id: currentPhotoId
        })
    }).then(r => r.json()).then(data => {
        hideLoading();
        if (data.success) {
            addToGallery(data.photo);
            loadPhotoInCanvas(data.photo.url, data.photo.id);
            setStatus(isPrincipale ? '★ Foto AI salvata come principale!' : '✓ Foto AI salvata in galleria');
        } else {
            setStatus('Errore salvataggio: ' + (data.error || ''));
        }
    }).catch(() => { hideLoading(); setStatus('Errore di connessione'); });
}
</script>

</body>
</html>