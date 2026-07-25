# Endpoint, tabelle e contratti dati degli editor

## Rotte del sottosistema (`Modules/PhotoPrint/routes/web.php`)

| Gruppo | Middleware | Cosa |
|---|---|---|
| `/studio/foto`, `/studio/ricordino` | `auth` + `AccessoStudio` | i due editor |
| `/account/ordini/{ordine}/lavorazione` (+ `lavorazione/defunto`, `lavorazione/approva`) | `auth` | la pratica vista dal cliente; è qui che l'ordine entra in sessione |
| `/bozza/{token}` (+ `approva`, `modifiche`) | **nessuno** | la bozza vista dalla famiglia: il link è la credenziale |
| `/admin/api/*` | `auth` + `AccessoStudio` + `throttle:30,1` | tutte le chiamate JS degli editor |

Necrologi in `Modules/Memorial/routes/web.php`: `/account/necrologi/*` (`auth`,
lato agenzia) e `/ricordi/{agenzia}/{percorso}` (pubblica).

**`AccessoStudio`** ammette staff, agenzie **approvate** e chiunque abbia un
proprio ordine aperto che richiede la lavorazione (`Ordine::lavorazioneApribile()`).
Va sempre dopo `auth`, che gestisce chi non ha fatto login: redirect alla pagina
di accesso, oppure **401 JSON** per le chiamate degli editor
(`shouldRenderJsonWhen` in `bootstrap/app.php` copre `admin/api/*`).

**CSRF**: `admin/api/*` **non** è più escluso. Il wrapper su `window.fetch` in
cima allo `<script>` dei due blade allega `X-CSRF-TOKEN` e
`X-Requested-With: XMLHttpRequest`. Endpoint fuori dal prefisso → 419.

## Endpoint `/admin/api/*`

I path replicano quelli attesi dal frontend importato da memoraiengine: non
rinominarli senza toccare anche il JS nei blade.

### Wizard AI e galleria (`WizardApiController`)

| Endpoint | Cosa fa |
|---|---|
| `POST bfl/enhance` | proxy `:5000/enhance` — `{image_url}`, isola il soggetto, pulisce lo sfondo |
| `POST bfl/outpaint` | proxy `:5000/outpaint` — `{image_url, top, bottom, left, right}` |
| `POST bfl/remove-bg` | proxy `:5000/remove-bg` — `{image_url, background_prompt}` |
| `POST foto-pratica/upload` | carica un file nella galleria della pratica |
| `POST foto-pratica/salva` | salva quello che c'è sul canvas (data URL) |
| `DELETE foto-pratica/{id}` | elimina una foto della pratica |
| `POST foto-pratica/upload-temp` | upload locale, ritorna URL temporaneo (passaggio del wizard) |
| `POST foto-pratica/salva-url` | scarica il risultato BFL e lo salva in storage |

Il proxy BFL è **asincrono**: POST → `polling_url` → poll ogni 3s (max 120s) →
`{url}` su `delivery.eu2.bfl.ai`. Un enhance reale impiega ~14s. La chiave
`BFL_API_KEY` vive **solo** nel proxy Python, mai in Laravel.
`rewriteForProxy()` riscrive gli URL con path `/storage/` su `127.0.0.1:8010`
(vedi `dev-setup.md`, deadlock).

Quando c'è una lavorazione in corso, upload/salva/salva-url **registrano** la
foto in `foto_pratica` (`registra()`); la prima foto della pratica diventa
principale da sola. Fuori da una lavorazione non registrano nulla: l'editor
funziona lo stesso, in modalità demo.

### Ricordino / Memorial (`RicordinoApiController`)

| Endpoint | Cosa fa |
|---|---|
| `GET santi` / `POST santi` | galleria santi condivisa (la userà anche il designer manifesti) |
| `GET ricordino-templates` | elenco (`{id, name, format, thumbnail, canvas_fronte, canvas_retro}`) |
| `POST ricordino-templates` | salva il layout corrente già ripulito dal designer; 422 se entrambi i canvas sono vuoti |
| `PUT ricordino-templates/{template}` | sovrascrive un template dell'utente (e lo rinomina); 403 sui predefiniti |
| `DELETE ricordino-templates/{template}` | elimina template + anteprima; 403 sui predefiniti |
| `POST defunto/{defunto}/ricordino` | salva la bozza: `canvas_fronte`, `canvas_retro`, `format`, `preview`, `preview_retro` → un solo ricordino per defunto (`firstOrNew`), `stato = bozza` |
| `POST defunto/{defunto}/gdpr` | consenso sul defunto: `autorizzato_da` (obbl.), `parentela`, `note` |

### Approvazione (`ApprovazioneController`)

`POST ricordino/{ricordino}/invia-approvazione` — `{email, immagine?}`.
Sta sotto `/admin/api/` **di proposito**: è il prefisso coperto dal wrapper
fetch. Può inviare lo staff o chi sta lavorando quella pratica (l'ordine in
sessione ha lo stesso `defunto_id`), altrimenti 403.
Risponde `{success, email, email_inviata, approva_url, whatsapp_url}`: se la
mail non parte non si annulla niente, il link esiste e l'agenzia lo manda a mano.

**Chiamate ancora scoperte** dal blade del designer: `necrologio-pratica/{id}`
(riga ~1733) e `salva-preghiera` (~1815) — falliscono in silenzio, vanno
agganciate a `Necrologio`. `elabora`/`elaboraConAI` è codice morto: nessun
bottone la chiama, superata dal wizard BFL.

## Tabelle

### PhotoPrint

**`foto_pratica`** (`Modules\PhotoPrint\Models\FotoPratica`)
`ordine_id` (indicizzato, no FK), `path` (relativo sul disk `public`), `tipo`
(`originale|ritagliata|elaborata_ai`), `is_principale`.
`url()` ritorna **path relativo** `/storage/...`; `perGalleria()` produce la
forma che si aspetta il Foto Manager; `rendiPrincipale()` toglie il segno alle
altre foto dello stesso ordine.

### Memorial

**`defunti`** (`Defunto`)
`nome, cognome, data_nascita, data_morte, anni, frase, preghiera` + consenso
GDPR in-app (`gdpr_consenso, gdpr_autorizzato_da, gdpr_parentela,
gdpr_autorizzato_at, gdpr_note`) + `ordine_id` nullable indicizzato.
Metodi: `nomeCompleto()`, `autorizzaGdpr()`, `toPraticaData()` (precompila i
testi del ricordino), rel `ricordini()`.
Il legame con Commerce è nei due sensi: `ordini.defunto_id` (usato dagli editor
per sapere di chi si parla) e `defunti.ordine_id` (usato dalla bozza pubblica
per sapere se la pratica è ancora aperta).

**`ricordini`** (`Ricordino`)
`defunto_id` FK, `formato` (`7x10` | `6x9`), `fronte`/`retro` JSON (stato canvas
Fabric), `stato` (`bozza` | `in_approvazione` | `approvato`),
`anteprima_fronte`/`anteprima_retro`. Indice `[defunto_id, stato]`.
`inviaInApprovazione($email, $utenteId, $anteprima)` crea la revisione e porta
lo stato a `in_approvazione`; `registraRisposta()` lo chiude in `approvato`
oppure lo riporta a `bozza` se la famiglia ha chiesto modifiche.
`revisioneAperta()` = il giro mandato e ancora senza risposta.

**`revisioni_ricordino`** (`RevisioneRicordino`)
`ricordino_id` FK, `token` (64 char, unique — **è la route key**), `inviata_a`,
`inviata_da` FK users, `inviata_at`, `anteprima`, `vista_at`, `esito`
(`approvata` | `modifiche` | null), `nota`, `risposta_at`.
Due scelte da non rompere: l'anteprima è **congelata sulla revisione** (la bozza
può cambiare mentre la famiglia guarda, deve restare scritto cosa aveva
davanti), e `vista_at` registra **solo la prima apertura** — dice quando ha
guardato, non quante volte ci è tornata sopra.
Il link vale finché l'ordine collegato è aperto, altrimenti **404**; se il
defunto non ha `ordine_id` è una pratica di prova dello staff e si vede.

**`necrologi`** (`Necrologio`)
`defunto_id` FK, `agenzia_id` (indicizzato), `percorso` (unique per agenzia),
`trigesimo_at`, `trigesimo_luogo`, `trigesimo_indirizzo`, `testo`, `og_image`,
`manifesto`, consenso alla **pubblicazione** (`pubblicazione_consenso`,
`_autorizzata_da`, `_parentela`, `_autorizzata_at`), `pubblicato`,
`pubblicato_fino_al`.
- `componiPercorso()` = slug del nome + 4 caratteri casuali. Il codice in coda
  non è decorazione: senza, si tirerebbe fuori l'elenco dei defunti di
  un'agenzia cambiando il nome nell'indirizzo.
- `pubblico()` vuole **tre condizioni insieme**: consenso del familiare,
  interruttore dell'agenzia, scadenza non passata. Se ne manca una: 404, non una
  pagina che spiega perché.
- `scadenzaPredefinita()` = trigesimo + 15 giorni.
- Il consenso alla pubblicazione **non si eredita** da quello sul defunto.

**`ricordino_templates`** (`RicordinoTemplate`)
`nome`, `formato`, `is_predefinito`, `sort_order`, `fronte`/`retro` JSON,
`anteprima`. È un **layout riusabile**, non la bozza di una persona: il designer
lo salva già ripulito (blocchi personali riportati a segnaposto via
`canvasTemplateJSON`, foto del defunto esclusa) e lo ricompila coi dati del
defunto corrente all'applicazione (`riempiConDefunto`). Regola da non rompere:
**nel DB dei template non devono mai finire dati di una persona reale**, né nel
JSON né nell'anteprima.
I **predefiniti MemorAI** (`is_predefinito = true`) arrivano da
`RicordinoTemplateSeeder` in coordinate relative, per entrambi i formati; il
seeder è rilanciabile (`updateOrCreate`). Stanno in cima all'elenco, non hanno
file di anteprima (la renderizza il designer) e la `DELETE` su uno di loro
risponde 403. **Non hanno ancora una colonna proprietario**: è il punto 2 della
roadmap.

**`santi`** (`Santo`) — `nome`, `path`. `url()` ritorna path **relativo**
`/storage/...`, di proposito, per aggirare `APP_URL=http://localhost`.

Migrazione e seed: `php artisan module:migrate Memorial` /
`php artisan module:seed Memorial`. Il seeder crea il defunto demo *Maria Rossi*
con `gdpr_consenso = false` — è voluto, serve a provare il flusso di consenso.

### Commerce (quel che serve di qua)

`ordini.defunto_id` e `ordini.richiede_lavorazione`; `Ordine::aperto()` e
`Ordine::lavorazioneApribile()` (richiede lavorazione + aperto + non `nuovo`).
`agenzie.slug` è il nome dell'agenzia nell'indirizzo pubblico del necrologio:
ogni condivisione porta in giro il suo nome.

## Contratto dati verso i blade (`PhotoPrintController`)

**`foto-manager`**: `$photos` (collection di oggetti `{id, url, is_principale,
tipo}`), `$praticaId`, `$nomePratica`, `$limiteFotoMb`, `$ritorno`
(`{url, etichetta}`).
Con una lavorazione in corso: foto vere da `foto_pratica`, `praticaId` =
`ordine->id`, nome = `Ordine MEM-…`. Senza: pratica di esempio (due foto demo).

**`ricordino-designer`**: `$praticaId` (= `defunto->id`), `$ricordinoId`
(null finché non esiste una bozza — il pulsante di invio alla famiglia compare
solo se c'è), `$praticaData` (da `toPraticaData()`), `$agenziaData`,
`$fotoElaborata` (la principale della pratica, altrimenti la foto demo),
`$fotoGalleria`, `$savedFronte`, `$savedRetro`, `$savedFormat`, `$gdpr`
(stato per banner/modale), `$ritorno`.

`ritorno()` decide dove si torna uscendo: dalla lavorazione si rientra
nell'ordine, agenzia e privato nella propria area account, lo staff nella
gestione. In vetrina finisce solo chi non ha fatto accesso.

Rimossi rispetto alla sorgente: "Genera Dedica AI" (scelta dell'utente) e
`@include('components.chatbox-memorai')` (non esiste nell'e-shop).

## Asset self-hosted (GDPR)

| Asset | Path |
|---|---|
| Fabric.js 5.3.1 | `public/vendor/libs/fabric.min.js` |
| jsPDF 2.5.1 | `public/vendor/libs/jspdf.umd.min.js` (lazy alla generazione PDF) |
| Google Fonts editor (55 woff2) | `public/vendor/fonts/` + `editor-fonts.css` |
| Monotype Corsiva (4 TTF, proprietario) | `public/fonts/Monotype-Corsiva-*.ttf`, `@font-face` inline nel designer |

Monotype Corsiva viene precaricato con `document.fonts.load(...)` seguito dal
re-render di entrambi i canvas: senza quel preload Fabric misura il testo con il
font di fallback e l'impaginazione salta.
