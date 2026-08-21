{{--
    Editor timeline delle foto: riordino via drag&drop, didascalia per foto,
    durata/zoom opzionali. Sostituisce il semplice <input multiple>.

    Vanilla JS, nessuna libreria: il brief da cui nasce questo editor lo dice
    esplicitamente ("non serve Fabric.js per questa parte"). L'input file
    reale resta `name="foto[]"` e contiene SOLO le foto nuove (un browser non
    può prevalorizzare un <input type=file> con file già sul server) — le
    foto già esistenti (parametro opzionale $fotoEsistenti, passato in
    modifica da DefuntoVideoController::edit) sono identificate per id.
    L'ordine finale — che interleaves foto vecchie e nuove — viaggia in un
    solo campo hidden JSON `sequenza`, decodificato lato server.
--}}
@php
    $fotoEsistentiJson = ($fotoEsistenti ?? collect())->map(fn ($f) => [
        'id' => $f->id,
        'url' => $f->url(),
        'testo' => $f->testo ?? '',
        'posizione' => $f->testo_posizione?->value ?? 'basso',
        'durata' => $f->durata_secondi,
        'zoom' => $f->zoom_attivo,
    ])->values();
@endphp
<div>
    <x-input-label value="Fotografie (in ordine, almeno una)" />

    <div id="tv-dropzone"
         class="cursor-pointer border-2 border-dashed border-caffe/25 px-6 py-8 text-center
                hover:border-oro transition-colors duration-300">
        <p class="font-sans text-[13px] text-testo-soft">
            Clicca per scegliere le foto — puoi ripetere per aggiungerne altre,
            poi trascinale per riordinarle.
        </p>
    </div>
    <input id="tv-foto-input" name="foto[]" type="file" accept="image/*" multiple class="hidden">
    <x-input-error :messages="$errors->get('foto')" class="mt-2" />
    <x-input-error :messages="$errors->get('foto.*')" class="mt-1" />
    <x-input-error :messages="$errors->get('sequenza')" class="mt-1" />
    <p id="tv-foto-errore-vuoto" class="hidden mt-2 font-sans text-[13px] text-errore">
        Carica almeno una fotografia.
    </p>

    <ul id="tv-foto-strip" class="mt-4 flex items-start gap-3 overflow-x-auto pb-2"></ul>
    <p id="tv-foto-durata-info" class="mt-2 font-sans text-[12px] text-testo-soft"></p>

    <div id="tv-foto-proprieta" class="hidden mt-4 border border-caffe/25 bg-panna/40 p-4 space-y-3">
        <p class="font-sans text-[11px] tracking-[0.18em] uppercase text-testo-soft">
            Proprietà della foto selezionata
        </p>

        <div>
            <x-input-label value="Didascalia (facoltativa, max 180 caratteri)" />
            <textarea id="tv-prop-testo" rows="2" maxlength="180"
                      class="block w-full bg-bianco border border-caffe/25 px-4 py-3 font-sans font-light text-[14px]
                             focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40"
                      placeholder="Es. Il matrimonio, 1978"></textarea>
        </div>

        <div class="flex flex-wrap items-end gap-5">
            <div>
                <x-input-label value="Posizione" />
                <select id="tv-prop-posizione"
                        class="bg-bianco border border-caffe/25 px-3 py-2 font-sans font-light text-[14px]
                               focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
                    <option value="basso">In basso</option>
                    <option value="centro">Al centro</option>
                    <option value="alto">In alto</option>
                </select>
            </div>

            <div>
                <x-input-label value="Durata (secondi)" />
                <input id="tv-prop-durata" type="number" min="1" max="15" placeholder="4,5 predefinita"
                       class="w-32 bg-bianco border border-caffe/25 px-3 py-2 font-sans font-light text-[14px]
                              focus:border-oro focus:outline-none focus:ring-1 focus:ring-oro/40">
            </div>

            <label class="flex items-center gap-2 font-sans text-[13px] text-testo pb-2">
                <input id="tv-prop-zoom" type="checkbox" checked>
                Movimento (zoom lento)
            </label>
        </div>
    </div>

    <input type="hidden" name="sequenza" id="tv-sequenza">
</div>
<script id="tv-foto-esistenti-data" type="application/json">{!! $fotoEsistentiJson->toJson() !!}</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Deferred a DOMContentLoaded: il partial può essere incluso prima del
    // campo #audio nel markup della pagina, serve che tutto il form esista già.
    const dropzone = document.getElementById('tv-dropzone');
    const fileInput = document.getElementById('tv-foto-input');
    const strip = document.getElementById('tv-foto-strip');
    const durataInfo = document.getElementById('tv-foto-durata-info');
    const erroreVuoto = document.getElementById('tv-foto-errore-vuoto');
    const sequenzaField = document.getElementById('tv-sequenza');
    const pannello = document.getElementById('tv-foto-proprieta');
    const propTesto = document.getElementById('tv-prop-testo');
    const propPosizione = document.getElementById('tv-prop-posizione');
    const propDurata = document.getElementById('tv-prop-durata');
    const propZoom = document.getElementById('tv-prop-zoom');
    const audioInput = document.getElementById('audio');
    const form = fileInput.closest('form');

    const PHOTO_DURATION = 4.5;
    const CARD_DURATION = 4.0;
    const CROSSFADE = 1.0;

    let foto = [];
    let nextId = 1;
    let selectedId = null;
    let audioDurata = null;

    // Foto già salvate (in modifica): precaricate come voci "esistente",
    // riconoscibili dal server per id — nessun file JS da portarsi dietro.
    JSON.parse(document.getElementById('tv-foto-esistenti-data').textContent || '[]').forEach((f) => {
        foto.push({
            id: nextId++,
            kind: 'esistente',
            existingId: f.id,
            file: null,
            url: f.url,
            testo: f.testo || '',
            posizione: f.posizione || 'basso',
            durata: f.durata,
            zoom: !!f.zoom,
        });
    });

    function addFiles(fileList) {
        Array.from(fileList).forEach((file) => {
            foto.push({
                id: nextId++,
                kind: 'nuova',
                file: file,
                url: URL.createObjectURL(file),
                testo: '',
                posizione: 'basso',
                durata: null,
                zoom: true,
            });
        });
        syncFileInput();
        renderStrip();
    }

    function removeFoto(id) {
        const item = foto.find((f) => f.id === id);
        if (item && item.kind === 'nuova') URL.revokeObjectURL(item.url);
        foto = foto.filter((f) => f.id !== id);
        if (selectedId === id) selectedId = null;
        syncFileInput();
        renderStrip();
    }

    function selectFoto(id) {
        selectedId = id;
        const item = foto.find((f) => f.id === id);
        if (item) {
            propTesto.value = item.testo;
            propPosizione.value = item.posizione;
            propPosizione.disabled = !item.testo;
            propDurata.value = item.durata ?? '';
            propZoom.checked = item.zoom;
            pannello.classList.remove('hidden');
        } else {
            pannello.classList.add('hidden');
        }
        renderStrip();
    }

    function syncFileInput() {
        const dt = new DataTransfer();
        foto.filter((f) => f.kind === 'nuova').forEach((f) => dt.items.add(f.file));
        fileInput.files = dt.files;
        syncSequenza();
    }

    // Un'unica lista ordinata, foto vecchie e nuove interleaved: per le
    // "nuova" l'indice deve coincidere con la posizione del file nell'array
    // ricostruito sopra — stesso filtro, stesso ordine, quindi si allineano.
    function syncSequenza() {
        let indiceNuova = 0;
        const sequenza = foto.map((f) => {
            const voce = {
                tipo: f.kind,
                testo: f.testo || '',
                posizione: f.testo ? f.posizione : '',
                durata: f.durata ?? null,
                zoom: !!f.zoom,
            };
            if (f.kind === 'esistente') {
                voce.id = f.existingId;
            } else {
                voce.indice = indiceNuova++;
            }
            return voce;
        });
        sequenzaField.value = JSON.stringify(sequenza);
    }

    function renderStrip() {
        strip.innerHTML = '';
        foto.forEach((f) => {
            const li = document.createElement('li');
            li.draggable = true;
            li.dataset.id = f.id;
            // Non più un quadrato fisso: la card cresce in altezza per fare
            // posto alla didascalia sotto la foto (prima si vedeva solo
            // un'iconcina 📝, bisognava selezionare la foto per leggerla).
            li.className = 'shrink-0 w-24 cursor-pointer';

            const cornice = document.createElement('div');
            cornice.className = 'relative w-24 h-24 border-2 transition-colors duration-200 '
                + (f.id === selectedId ? 'border-oro' : 'border-caffe/20');

            const img = document.createElement('img');
            img.src = f.url;
            img.className = 'w-full h-full object-cover';
            cornice.appendChild(img);

            if (f.durata) cornice.appendChild(badge(f.durata + 's', 'top-1 left-1'));
            if (!f.zoom) cornice.appendChild(badge('⏸', 'top-1 right-1'));

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = '×';
            remove.className = 'absolute -top-2 -right-2 w-5 h-5 rounded-full bg-testo text-bianco text-[12px] leading-5';
            remove.addEventListener('click', (e) => { e.stopPropagation(); removeFoto(f.id); });
            cornice.appendChild(remove);

            li.appendChild(cornice);

            const didascalia = document.createElement('p');
            didascalia.className = 'mt-1 text-[10px] leading-snug line-clamp-2 '
                + (f.testo ? 'text-testo' : 'italic text-testo-soft/50');
            didascalia.textContent = f.testo || 'Nessuna didascalia';
            li.appendChild(didascalia);

            li.addEventListener('click', () => selectFoto(f.id));
            li.addEventListener('dragstart', (e) => e.dataTransfer.setData('text/plain', String(f.id)));
            li.addEventListener('dragover', (e) => e.preventDefault());
            li.addEventListener('drop', (e) => {
                e.preventDefault();
                const draggedId = Number(e.dataTransfer.getData('text/plain'));
                if (draggedId === f.id) return;
                const from = foto.findIndex((x) => x.id === draggedId);
                const to = foto.findIndex((x) => x.id === f.id);
                if (from === -1 || to === -1) return;
                const [moved] = foto.splice(from, 1);
                foto.splice(to, 0, moved);
                syncFileInput();
                renderStrip();
            });

            strip.appendChild(li);
        });

        renderDurataInfo();
    }

    function badge(text, positionClass) {
        const span = document.createElement('span');
        span.textContent = text;
        span.className = 'absolute ' + positionClass + ' px-1 text-[10px] bg-testo/80 text-bianco leading-none py-0.5';
        return span;
    }

    function renderDurataInfo() {
        if (foto.length === 0) {
            durataInfo.textContent = '';
            return;
        }
        const durataFoto = foto.reduce((tot, f) => tot + (f.durata || PHOTO_DURATION), 0);
        const nClip = foto.length + 2; // + card apertura e chiusura
        const durataTotale = durataFoto + (2 * CARD_DURATION) - (CROSSFADE * (nClip - 1));
        let testo = 'Durata indicativa del video: circa ' + durataTotale.toFixed(1) + ' secondi.';
        if (audioDurata) {
            testo += ' Audio scelto: ' + audioDurata.toFixed(1) + ' secondi'
                + (audioDurata < durataTotale ? ' (verrà ripetuto in loop).' : ' (verrà troncato).');
        }
        durataInfo.textContent = testo;
    }

    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('click', (e) => e.stopPropagation());
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) addFiles(fileInput.files);
        erroreVuoto.classList.add('hidden');
    });

    propTesto.addEventListener('input', () => {
        const item = foto.find((f) => f.id === selectedId);
        if (!item) return;
        item.testo = propTesto.value;
        propPosizione.disabled = !item.testo;
        syncSequenza();
        renderStrip();
    });
    propPosizione.addEventListener('change', () => {
        const item = foto.find((f) => f.id === selectedId);
        if (!item) return;
        item.posizione = propPosizione.value;
        syncSequenza();
    });
    propDurata.addEventListener('input', () => {
        const item = foto.find((f) => f.id === selectedId);
        if (!item) return;
        const val = parseInt(propDurata.value, 10);
        item.durata = Number.isFinite(val) && val > 0 ? val : null;
        syncSequenza();
        renderDurataInfo();
        renderStrip();
    });
    propZoom.addEventListener('change', () => {
        const item = foto.find((f) => f.id === selectedId);
        if (!item) return;
        item.zoom = propZoom.checked;
        syncSequenza();
        renderStrip();
    });

    if (audioInput) {
        audioInput.addEventListener('change', () => {
            audioDurata = null;
            if (audioInput.files[0]) {
                const audioEl = new Audio(URL.createObjectURL(audioInput.files[0]));
                audioEl.addEventListener('loadedmetadata', () => {
                    audioDurata = audioEl.duration;
                    renderDurataInfo();
                });
            } else {
                renderDurataInfo();
            }
        });
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            if (foto.length === 0) {
                e.preventDefault();
                erroreVuoto.classList.remove('hidden');
                dropzone.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    // Popola subito la sequenza e disegna la strip per le foto già esistenti
    // (in modifica) — in creazione `foto` è vuoto, non cambia nulla.
    syncSequenza();
    renderStrip();
});
</script>
