# Endpoint, tabelle e contratti dati degli editor

## Endpoint `/admin/api/*`

Tutti nel gruppo `Route::prefix('admin/api')->middleware([VerifyStudioToken::class, 'throttle:30,1'])`
di `Modules/PhotoPrint/routes/web.php`, esclusi dal CSRF in `bootstrap/app.php`.
I path replicano quelli attesi dal frontend importato da memoraiengine: non
rinominarli senza toccare anche il JS nei blade.

### Wizard AI (`WizardApiController`) — proxy verso :5000

| Endpoint | Proxy a | Input |
|---|---|---|
| `POST bfl/enhance` | `:5000/enhance` | `{image_url}` — isola il soggetto, pulisce lo sfondo, volto preservato |
| `POST bfl/outpaint` | `:5000/outpaint` | `{image_url, top, bottom, left, right}` |
| `POST bfl/remove-bg` | `:5000/remove-bg` | `{image_url, background_prompt}` |
| `POST foto-pratica/upload-temp` | — | upload locale, ritorna URL temporaneo |
| `POST foto-pratica/salva-url` | — | scarica il risultato BFL e lo salva in storage |

Il proxy è **asincrono**: POST → `polling_url` → poll ogni 3s (max 120s) → `{url}`
su `delivery.eu2.bfl.ai`. Un enhance reale impiega ~14s. La chiave `BFL_API_KEY`
vive **solo** nel proxy Python, mai in Laravel.

`rewriteForProxy()` riscrive gli URL con path `/storage/` su `127.0.0.1:8010`
prima di passarli al proxy (vedi `dev-setup.md`, deadlock).

### Ricordino / Memorial (`RicordinoApiController`)

| Endpoint | Cosa fa |
|---|---|
| `GET santi` | galleria santi condivisa |
| `POST santi` | upload nuovo santo |
| `GET ricordino-templates` | elenco template (`{id, name, format, thumbnail, canvas_fronte, canvas_retro}`) |
| `POST ricordino-templates` | salva il layout corrente: `name`, `format`, `thumbnail` (data URL), `canvas_fronte`, `canvas_retro` già ripuliti dal designer. 422 se entrambi i canvas sono vuoti |
| `DELETE ricordino-templates/{template}` | elimina template + file anteprima |
| `POST defunto/{defunto}/ricordino` | salva la bozza: `canvas_fronte`, `canvas_retro`, `format`, `preview`, `preview_retro` (data URL PNG) → un solo ricordino per defunto (`firstOrNew`), `stato = bozza` |
| `POST defunto/{defunto}/gdpr` | registra il consenso: `autorizzato_da` (obbl.), `parentela`, `note` |

## Tabelle Memorial

**`defunti`** (`Modules\Memorial\Models\Defunto`)
`nome, cognome, data_nascita, data_morte, anni, frase, preghiera` +
consenso GDPR in-app: `gdpr_consenso, gdpr_autorizzato_da, gdpr_parentela,
gdpr_autorizzato_at, gdpr_note` + `ordine_id` nullable indicizzato (aggancio
Commerce futuro, nessuna FK).
Metodi: `nomeCompleto()`, `autorizzaGdpr($da, $parentela, $note)`,
`toPraticaData()` (precompila i testi del ricordino), rel `ricordini()`.

**`ricordini`** (`Ricordino`)
`defunto_id` FK, `formato` (`7x10` | `6x9`), `fronte`/`retro` JSON (stato canvas
Fabric), `stato` (`bozza` | `in_approvazione` | `approvato`),
`anteprima_fronte`/`anteprima_retro` (path PNG). Indice `[defunto_id, stato]`.

**`ricordino_templates`** (`RicordinoTemplate`)
`nome`, `formato`, `fronte`/`retro` JSON, `anteprima` (path JPG), indice su
`formato`. È un **layout riusabile**, non la bozza di una persona: il designer
lo salva già ripulito (blocchi personali riportati a segnaposto via
`canvasTemplateJSON`, foto del defunto esclusa) e lo ricompila coi dati del
defunto corrente all'applicazione (`riempiConDefunto`). Regola da non rompere:
**nel DB dei template non devono mai finire dati di una persona reale**, né nel
JSON né nell'anteprima (che infatti si renderizza dal JSON ripulito, non dal
canvas a schermo).

**`santi`** (`Santo`)
`nome`, `path` (relativo sul disk `public`). `url()` ritorna path **relativo**
`/storage/...` — di proposito, per aggirare `APP_URL=http://localhost`.

Migrazione e seed: `php artisan module:migrate Memorial` /
`php artisan module:seed Memorial`. Il seeder crea il defunto demo *Maria Rossi*
con `gdpr_consenso = false` — è voluto, serve a provare il flusso di consenso.

## Contratto dati verso i blade (`PhotoPrintController`)

**`foto-manager`**: `$photos` (collection di oggetti `{id, url, is_principale,
tipo}` con `tipo` in `originale|ritagliata|elaborata_ai`), `$praticaId`,
`$nomePratica`. Fase 1: dati mock, foto reale di test in
`storage/app/public/photoprint-demo/test-sacro-cuore.jpg`.

**`ricordino-designer`**: `$praticaId` (= `defunto->id`), `$praticaData` (da
`toPraticaData()`), `$agenziaData`, `$fotoElaborata`, `$fotoGalleria`,
`$savedFronte`, `$savedRetro`, `$savedFormat`, `$gdpr` (stato per banner/modale).

Nascosti in Fase 1 perché richiedono il backend: pulsanti B2B (necrologio,
invia-approvazione), Salva Template. Rimossi: "Genera Dedica AI" (scelta
dell'utente) e `@include('components.chatbox-memorai')` (non esiste nell'e-shop).

## Asset self-hosted (GDPR)

| Asset | Path |
|---|---|
| Fabric.js 5.3.1 | `public/vendor/libs/fabric.min.js` |
| jsPDF 2.5.1 | `public/vendor/libs/jspdf.umd.min.js` (caricato lazy alla generazione PDF) |
| Google Fonts editor (55 woff2) | `public/vendor/fonts/` + `editor-fonts.css` |
| Monotype Corsiva (4 TTF, proprietario) | `public/fonts/Monotype-Corsiva-*.ttf`, `@font-face` inline nel designer |

Monotype Corsiva viene precaricato con `document.fonts.load(...)` seguito dal
re-render di entrambi i canvas: senza quel preload Fabric misura il testo con il
font di fallback e l'impaginazione salta.
