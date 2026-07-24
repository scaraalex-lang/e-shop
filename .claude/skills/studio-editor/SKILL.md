---
name: studio-editor
description: Sottosistema editor MemorAI - Foto Manager (/studio/foto) e Ricordino Designer (/studio/ricordino), moduli PhotoPrint e Memorial, proxy BFL, canvas Fabric.js, export PDF jsPDF, defunto e consenso GDPR, galleria santi, font self-hosted. Usa questa skill prima di toccare qualunque cosa in Modules/PhotoPrint, Modules/Memorial, i due blade degli editor, gli endpoint /admin/api/*, o quando serve avviare/verificare a schermo gli editor.
---

# Studio editor (Foto Manager + Ricordino Designer)

Porting dei due editor di **memoraiengine.com** (produzione attiva, multi-tenant)
dentro l'e-shop. Stato: **Fase 1 completa e funzionante** — visivo + wizard AI
reale collegato. La persistenza vera (foto/ordini) è Fase 2 e dipende da Commerce.

## Mappa rapida

| Cosa | Dove |
|---|---|
| Foto Manager (view, ~1040 righe, HTML+CSS+JS inline) | `Modules/PhotoPrint/resources/views/foto-manager.blade.php` |
| Ricordino Designer (view, ~1780 righe, idem) | `Modules/PhotoPrint/resources/views/ricordino-designer.blade.php` |
| Route + guard | `Modules/PhotoPrint/routes/web.php` |
| Proxy BFL + upload temp | `app/Http/Controllers/WizardApiController.php` (PhotoPrint) |
| API ricordino/santi/GDPR | `app/Http/Controllers/RicordinoApiController.php` (PhotoPrint) |
| Dati passati alle view | `app/Http/Controllers/PhotoPrintController.php` (PhotoPrint) |
| Guard token | `app/Http/Middleware/VerifyStudioToken.php` (PhotoPrint) |
| Defunto / Ricordino / Santo | `Modules/Memorial/app/Models/` |
| Sorgenti originali intoccati | `FileMemorai/*.blade.php` (riferimento, non modificare) |

Dettagli operativi:
- **Avvio ambiente, verifica a schermo, trappole** → `references/dev-setup.md`
- **Endpoint, tabelle, contratti dati blade** → `references/api-e-dati.md`

## Regole da rispettare

1. **Non toccare i microservizi Python sul VPS.** `bfl_proxy.py` :5000 è
   condiviso e in uso live da tutti i tenant di memoraiengine. L'e-shop è solo
   un consumatore in più: gli si passano URL assoluti, punto. Niente restart
   disinvolti, niente modifiche al file.
2. **Zero richieste esterne dal browser (GDPR).** Fabric.js, jsPDF e i font sono
   tutti self-hosted (`public/vendor/libs/`, `public/vendor/fonts/`,
   `public/fonts/`). Se aggiungi una libreria o un font, self-hostalo: mai un
   `<script src="https://cdn...">` o un `@import` di Google Fonts.
3. **I due blade sono monoliti** (HTML+CSS+JS in un file). È voluto: sono
   importati quasi as-is per restare allineati alla sorgente. Modificali in
   place con Edit chirurgici, non "riorganizzarli" in componenti.
4. **Ogni chiamata JS a `/admin/api/`** passa dal wrapper su `window.fetch` che
   allega `X-Studio-Token` (in cima allo `<script>` di entrambi i blade). Un
   endpoint nuovo sotto `/admin/api/` è già coperto; uno fuori da quel prefisso
   perde il token *e* si becca il CSRF.
5. **Mai costruire URL immagine con `url()`/`Storage::url()`** in questi
   controller: `APP_URL=http://localhost` genera URL sbagliati e il proxy BFL
   fallisce. Usa l'host reale della richiesta (`$request->getSchemeAndHttpHost()`)
   o path relativi `/storage/...`.
6. **Dopo aver toccato i moduli**: `composer dump-autoload`.

## Cosa è già fatto (non rifarlo)

- Foto Manager con foto di test reale sul canvas + **wizard AI end-to-end
  verificato** (enhance BFL: ~14s, volto preservato, sfondo pulito).
- Guard Fase 1 su `/admin/api/*`: token condiviso `X-Studio-Token` +
  `throttle:30,1`, esclusi dal CSRF in `bootstrap/app.php`.
- Modulo **Memorial**: tabelle `defunti` / `ricordini` / `santi`, consenso GDPR
  registrabile in-app dal designer (banner + modale), seeder demo.
- Designer cablato sui dati reali del defunto: precompila i blocchi testo, salva
  fronte/retro sul DB, galleria santi.
- Testo: **traccia (contorno)** e **ombra** configurabili.
- **Maschere foto** (ovale, cerchio, angoli arrotondati, arco centinato) nella
  sezione "Maschere foto" della sidebar, via `clipPath` Fabric costruito su
  `width/height` intrinseci dell'immagine, così segue spostamenti e
  ridimensionamenti. Il tipo sta su `obj.maschera`, incluso nelle
  `toJSON([...])` insieme a `customType`.
- **Modale propria** (`modale({titolo, testo, campo, azioni})` → Promise) al
  posto di `prompt/confirm/alert`: usarla anche per i prossimi dialoghi.
- **Pannello Livelli**: elenco blocchi del lato attivo, selezione (anche se
  coperti), riordino z-index ▲/▼, mostra/nascondi, elimina, deseleziona tutto.
- **Sistema template**: salva/applica/elimina layout riusabili (tabella
  `ricordino_templates` in Memorial) + 3 **predefiniti MemorAI** (Classico,
  Con foto, Sobrio) per 7x10 e 6x9, generati da `RicordinoTemplateSeeder` in
  coordinate relative. I template non contengono dati personali: vedi
  `references/api-e-dati.md`.
- **Sidebar a fisarmonica**: ogni sezione è un `<details>` nativo; stato
  aperto/chiuso in `localStorage` (`ricordino-designer:sezioni`). Una sezione
  nuova va aggiunta come `<details class="acc" id="acc-...">` — `initAccordion()`
  la aggancia da sola.
- Self-host completo: Fabric.js 5.3.1, jsPDF 2.5.1, 55 woff2 Google Fonts,
  4 TTF **Monotype Corsiva** (font proprietario fornito dal committente).

## Prossimi passi, in ordine di priorità

Il cliente vero del sottosistema editor è l'**agenzia B2B**: fa decine di
ricordini al mese, quindi è lì che stanno il volume, il valore e i flussi da
costruire. Il B2C usa gli stessi editor ma in versione ridotta (un ricordino,
nessun archivio). L'ordine qui sotto segue le dipendenze reali, non i desideri.

**1. Commerce — sblocca tutto il resto.** Account B2B con approvazione + ordini.
Senza account non esiste il proprietario dei template, non esiste auth, non
esiste l'ordine a cui agganciare defunto e ricordino. Ogni punto seguente lo
presuppone: non provare a scavalcarlo con soluzioni tampone.

**2. Chiudere l'accesso agli editor** (sicurezza, appena c'è auth). Oggi
`/studio/*` è pubblica e il token `X-Studio-Token` è iniettato nella pagina,
quindi scrapeabile: è un lucchetto contro gli scanner, non una protezione.
Sostituire `VerifyStudioToken` con l'auth dell'area agenzia/staff.

**3. Template per account** (il pezzo B2B più immediato). Decisione presa:
- **predefiniti MemorAI** curati da noi, uguali per tutti, sola lettura → si
  continuano a versionare nel seeder, non serve un CRUD admin;
- **template dell'agenzia**: ogni account approvato salva e ritocca i suoi, e
  non vede quelli delle altre agenzie (è informazione commerciale loro);
- **B2C**: nessuna libreria personale, parte da un predefinito e le modifiche
  restano sulla bozza in `ricordini`.

  Serve: colonna proprietario nullable su `ricordino_templates` (`null` =
  predefinito MemorAI), filtro nell'elenco (predefiniti + i propri) e controllo
  su `PUT`/`DELETE`. Ipotesi di lavoro: `agenzia_id`, dato che il B2C non ha
  archivio; passare a un `owner_id` polimorfo solo se servirà davvero.

**4. Persistenza foto/pratica legata all'ordine.** Oggi `PhotoPrintController`
serve dati mock e un solo defunto (`Defunto::firstOrFail()`): va sostituito con
il defunto dell'ordine in lavorazione, e le foto vanno legate alla pratica.

**5. Bozza condivisibile con la famiglia** (il flusso che vende il B2B). Link con
token che l'agenzia manda alla famiglia per l'approvazione, storico revisioni,
transizioni di `ricordini.stato` (`bozza` → `in_approvazione` → `approvato`; la
colonna c'è già). Link valido finché l'ordine è aperto.

**6. Necrologio e invio-approvazione** dal designer: i pulsanti sono già nel
blade, nascosti in attesa del backend.

**7. Aggancio `defunti.ordine_id`** all'ordine trigesimo (colonna pronta, no FK).

**8. Rifiniture editor** (non bloccano nulla, si fanno a richiesta):
cornice dorata che segue la maschera; il pannello proprietà lascia i valori del
testo precedente quando si seleziona un'immagine; azione "promuovi a
predefinito" nell'area staff per pescare un layout riuscito da quelli reali.

**Pulizia tecnica dovuta**: passando a nginx + php-fpm concorrente, togliere la
riscrittura URL verso :8010 in `WizardApiController::rewriteForProxy()`.

## Nodo architetturale da tenere a mente

memoraiengine è **pratica/defunto-centrico** (l'agenzia crea la pratica).
L'e-shop MemorAI è **ordine/cliente-centrico**. Il modulo Memorial è il ponte:
il defunto è l'entità che collega ordine ↔ ricordino. Ogni scelta di modellazione
qui deve restare compatibile con quel ponte.

Corollario per il B2B: l'agenzia lavora **per conto di** una famiglia, quindi
ogni cosa che il designer produce ha tre soggetti distinti — l'account che la
crea (agenzia), la persona a cui si riferisce (defunto) e chi la approva
(famiglia). Non collassarli: è la ragione per cui il consenso GDPR sta sul
defunto, i template staranno sull'agenzia e l'approvazione viaggerà su un token
dato alla famiglia.
