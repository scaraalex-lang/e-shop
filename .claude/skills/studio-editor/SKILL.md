---
name: studio-editor
description: Sottosistema editor MemorAI - Foto Manager (/studio/foto) e Ricordino Designer (/studio/ricordino), moduli PhotoPrint e Memorial, lavorazione fotografica legata all'ordine, bozza condivisibile con la famiglia, necrologio pubblico, proxy BFL, canvas Fabric.js, export PDF jsPDF, defunto e consenso GDPR, galleria santi, font self-hosted. Usa questa skill prima di toccare qualunque cosa in Modules/PhotoPrint, Modules/Memorial, i due blade degli editor, gli endpoint /admin/api/*, la lavorazione di un ordine o i necrologi, o quando serve avviare/verificare a schermo gli editor.
---

# Studio editor (Foto Manager + Ricordino Designer)

Porting dei due editor di **memoraiengine.com** (produzione attiva, multi-tenant)
dentro l'e-shop. Stato: **Fase 2 in corso**. Gli editor non sono più una demo
isolata: si aprono dalla lavorazione di un ordine vero, salvano foto e bozze
sulla pratica, e la bozza si manda alla famiglia con un link. Restano da portare
il **card designer del necrologio** e il **designer manifesti**.

## Mappa rapida

| Cosa | Dove |
|---|---|
| Foto Manager (view, ~1070 righe, HTML+CSS+JS inline) | `Modules/PhotoPrint/resources/views/foto-manager.blade.php` |
| Ricordino Designer (view, ~1900 righe, idem) | `Modules/PhotoPrint/resources/views/ricordino-designer.blade.php` |
| Route (studio, lavorazione, bozza, `/admin/api/*`) | `Modules/PhotoPrint/routes/web.php` |
| Guard degli editor | `app/Http/Middleware/AccessoStudio.php` (PhotoPrint) |
| Su quale ordine si sta lavorando | `app/Servizi/LavorazioneCorrente.php` (PhotoPrint) |
| Pagina di lavorazione dell'ordine | `app/Http/Controllers/LavorazioneController.php` + `resources/views/lavorazione/show.blade.php` |
| Proxy BFL, upload e galleria foto | `app/Http/Controllers/WizardApiController.php` (PhotoPrint) |
| API ricordino/santi/template/GDPR | `app/Http/Controllers/RicordinoApiController.php` (PhotoPrint) |
| Dati passati alle view degli editor | `app/Http/Controllers/PhotoPrintController.php` (PhotoPrint) |
| Invio bozza alla famiglia | `app/Http/Controllers/ApprovazioneController.php` (PhotoPrint) |
| Bozza vista dalla famiglia (pubblica) | `app/Http/Controllers/BozzaPubblicaController.php` + `resources/views/bozza/show.blade.php` |
| Foto della pratica | `Modules/PhotoPrint/app/Models/FotoPratica.php` |
| Defunto / Ricordino / RevisioneRicordino / Necrologio / Santo | `Modules/Memorial/app/Models/` |
| Necrologi lato agenzia + pagina pubblica | `Modules/Memorial/app/Http/Controllers/NecrologiController.php`, `NecrologioPubblicoController.php` |
| Sorgenti originali intoccati (4 editor) | `FileMemorai/*.blade.php` (riferimento, non modificare) |

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
   `<script src="https://cdn...">` o un `@import` di Google Fonts. Vale anche
   per i servizi: niente generatori di QR remoti (vedi il designer manifesti).
3. **I due blade sono monoliti** (HTML+CSS+JS in un file). È voluto: sono
   importati quasi as-is per restare allineati alla sorgente. Modificali in
   place con Edit chirurgici, non "riorganizzarli" in componenti.
4. **Ogni chiamata JS degli editor sta sotto `/admin/api/`.** Il wrapper su
   `window.fetch` in cima allo `<script>` di entrambi i blade allega
   `X-CSRF-TOKEN` e `X-Requested-With: XMLHttpRequest`. Un endpoint nuovo sotto
   quel prefisso è già coperto; uno fuori si becca un **419** e nessuno capisce
   perché (è già successo con l'invio della bozza, per questo sta lì anche se
   non è "una API di admin").
5. **Mai costruire URL immagine con `url()`/`Storage::url()`** in questi
   controller: `APP_URL=http://localhost` genera URL sbagliati e il proxy BFL
   fallisce. Usa l'host reale della richiesta (`$request->getSchemeAndHttpHost()`)
   o path relativi `/storage/...`.
6. **L'id in sessione non è un permesso.** `LavorazioneCorrente` rilegge
   l'ordine a ogni richiesta e ricontrolla che sia di chi chiede e ancora
   aperto. Chi aggiunge un endpoint che tocca la pratica passa di lì, non si
   fida di quello che arriva dal client.
7. **Dopo aver toccato i moduli**: `composer dump-autoload`.

## Cosa è già fatto (non rifarlo)

**Editor e canvas**
- Foto Manager con galleria della pratica (carica, salva dal canvas, elimina) +
  **wizard AI end-to-end verificato** (enhance BFL: ~14s, volto preservato).
- Testo: **traccia (contorno)** e **ombra** configurabili.
- **Maschere foto** (ovale, cerchio, angoli arrotondati, arco centinato) via
  `clipPath` Fabric costruito sui `width/height` intrinseci dell'immagine, così
  segue spostamenti e ridimensionamenti. Il tipo sta su `obj.maschera`, incluso
  nelle `toJSON([...])` insieme a `customType`.
- **Modale propria** (`modale({titolo, testo, campo, azioni})` → Promise) al
  posto di `prompt/confirm/alert`: usarla anche per i prossimi dialoghi.
- **Pannello Livelli**: elenco blocchi del lato attivo, selezione (anche se
  coperti), riordino z-index ▲/▼, mostra/nascondi, elimina, deseleziona tutto.
- **Sistema template**: salva/applica/elimina layout riusabili
  (`ricordino_templates`) + 3 **predefiniti MemorAI** (Classico, Con foto,
  Sobrio) per 7x10 e 6x9 da `RicordinoTemplateSeeder`, in coordinate relative.
  I template non contengono dati personali: vedi `references/api-e-dati.md`.
- **Sidebar a fisarmonica**: ogni sezione è un `<details>` nativo; stato in
  `localStorage` (`ricordino-designer:sezioni`). Una sezione nuova va aggiunta
  come `<details class="acc" id="acc-...">` — `initAccordion()` la aggancia da sola.
- Uscita dagli editor: si torna **da dove si è entrati** (ordine / account /
  gestione), non in vetrina — `PhotoPrintController::ritorno()`.
- Self-host completo: Fabric.js 5.3.1, jsPDF 2.5.1, 55 woff2 Google Fonts,
  4 TTF **Monotype Corsiva** (font proprietario fornito dal committente).

**Accesso e legame con l'ordine** (2026-07-25)
- `/studio/*` e `/admin/api/*` sono dietro **`auth` + `AccessoStudio`**. Entrano
  staff, agenzie approvate e **chiunque abbia un proprio ordine aperto che
  richiede la lavorazione**: è così che il privato arriva alla sua fotografia.
  Il token condiviso `X-Studio-Token` / `VerifyStudioToken` **non esiste più**.
- **Pagina di lavorazione** `/account/ordini/{ordine}/lavorazione`: dati del
  defunto, consenso, ingresso negli editor, stato della bozza. È da qui che
  `LavorazioneCorrente` mette l'ordine in sessione e gli editor sanno su cosa
  lavorano (i due blade non leggono l'ordine dall'indirizzo).
- **Foto persistenti**: tabella `foto_pratica` legata a `ordine_id`;
  `WizardApiController` registra da solo quello che passa dal wizard quando c'è
  una lavorazione in corso.
- Il Foto Manager conosce il **limite di upload reale** del server
  (`limiteFotoMb`) e blocca prima di spedire, invece di prendersi un 413 muto.

**Bozza condivisibile e necrologio** (2026-07-25)
- **Invio alla famiglia** dal Designer: `POST /admin/api/ricordino/{id}/invia-approvazione`
  → nasce una `revisioni_ricordino` col suo token, parte l'email
  (`BozzaDaApprovare`) e torna anche un link WhatsApp. L'anteprima viene
  **congelata sulla revisione**: resta scritto cosa la famiglia aveva davanti.
- **Pagina pubblica della bozza** `/bozza/{token}`: nessun account, il link è la
  credenziale; approva o chiede modifiche con una nota; storico dei giri
  precedenti. Vale finché l'ordine è aperto, poi 404.
  Stati: `bozza → in_approvazione → approvato`; "modifiche" riporta a `bozza`.
- **Necrologio** = card social con **data, ora e luogo del trigesimo** (non è la
  pagina QR, che sarà il video memoriale). Gestione lato agenzia sotto
  `/account/necrologi`, pagina pubblica `/ricordi/{agenzia}/{percorso}` su
  `layouts/nudo` (la card sta da sola, senza la vetrina intorno), con meta Open
  Graph. Pubblica solo se **consenso alla pubblicazione + interruttore acceso +
  non scaduto**: se manca una condizione, 404 secco.
- Modulo **Memorial**: `defunti` / `ricordini` / `revisioni_ricordino` /
  `necrologi` / `santi` / `ricordino_templates`; consenso GDPR sul defunto
  registrabile dal designer e dalla lavorazione.

**Necrologio pubblico: una card sola, non più due pagine** (2026-08-27)
- `Necrologio::pubblico()` resta il gate a tre condizioni (consenso +
  interruttore + non scaduto): finché una manca, `/ricordi/{agenzia}/{percorso}`
  mostra il modulo per pubblicare invece dell'indirizzo, e la sezione embed
  mostra "il codice compare qui appena è pubblicato" — **non è un bug**, è
  il flusso in due passi (`necrologi.consenso` poi `necrologi.pubblica`,
  separati apposta). Capita spesso che sembri "non funzioni niente": prima di
  toccare codice, controllare `pubblicazione_consenso`/`pubblicato`/
  `pubblicato_fino_al` sul record.
- I **messaggi di cordoglio** (`MessaggioCordoglio`, tabella
  `messaggi_cordoglio`) sono ora sulla card principale del necrologio
  (`pubblico.blade.php`), non più su `/ricordi/{agenzia}/{percorso}/manifesto`:
  un solo URL per il defunto con tutto il materiale dentro (necrologio +
  anteprima manifesto cliccabile + messaggi), com'è oggi lo strumento che
  l'agenzia condivide e tiene sotto controllo. Route `POST
  ricordi/{agenzia}/{percorso}/messaggio` (`necrologio.messaggio`); la pagina
  `/manifesto` resta solo la visualizzazione a piena pagina (PDF o immagine).
- **Bordo della card**: `border-caffe` pieno (non più `/15`, quasi invisibile).
- **Luogo/indirizzo dell'evento** (chiesa, trigesimo, funerale) è un link a
  Google Maps (`maps/search/?api=1&query=...`), icona pin SVG inline (niente
  servizi esterni, coerente con la regola GDPR sulle risorse remote).
- **Manifesto multi-orientamento**: i formati vanno da verticali (50x70,
  70x100) a orizzontali (61x45, 50x32) — ogni `<img>` di anteprima deve avere
  sia un limite di altezza che `max-w-full`/`w-full`, mai solo uno dei due,
  altrimenti un formato orizzontale trabocca dal contenitore. Punti toccati:
  `pubblico.blade.php` (card pubblica) e `necrologi/form.blade.php` (thumbnail
  lato agenzia); `manifesto-pubblico.blade.php` e `defunti/show.blade.php`
  erano già a posto (`w-full`/`h-auto`, nessuna altezza fissa).

## Prossimi passi, in ordine di priorità

Il cliente vero del sottosistema editor è l'**agenzia B2B**: fa decine di
ricordini al mese, quindi è lì che stanno il volume, il valore e i flussi da
costruire. Il B2C usa gli stessi editor ma in versione ridotta (un ricordino,
nessun archivio).

**1. Card designer del necrologio** (`FileMemorai/necro-card-designer.blade.php`,
534 righe, terzo editor di memoraiengine). Oggi `necrologi.og_image` è una foto
scelta d'ufficio: la card vera va **disegnata e salvata come file**, perché
WhatsApp e Facebook non eseguono JavaScript e mostrano solo un'immagine statica
già pronta a un indirizzo. Portandolo: Fabric da `/vendor/libs/`, endpoint sotto
`/admin/api/`, layout nostro, modello mappato su `Defunto` + `Necrologio`.
Aperto col committente: template della card curati da noi o caricati
dall'agenzia; il **manifesto allegato** caricato come file o prodotto dal
designer manifesti.

**2. Template per agenzia** (il pezzo B2B più immediato). Decisione presa:
- **predefiniti MemorAI** curati da noi, uguali per tutti, sola lettura → si
  continuano a versionare nel seeder, non serve un CRUD admin;
- **template dell'agenzia**: ogni account approvato salva e ritocca i suoi, e
  non vede quelli delle altre agenzie (è informazione commerciale loro);
- **B2C**: nessuna libreria personale, parte da un predefinito e le modifiche
  restano sulla bozza in `ricordini`.

  Serve: colonna proprietario nullable su `ricordino_templates` (`null` =
  predefinito MemorAI, oggi **non c'è ancora**), filtro nell'elenco e controllo
  su `PUT`/`DELETE`. Ipotesi di lavoro: `agenzia_id`, dato che il B2C non ha
  archivio; `owner_id` polimorfo solo se servirà davvero.

**3. Archivio pratiche per staff e agenzie.** Oggi chi entra negli editor senza
un ordine (staff, agenzia abbonata) trova la **pratica di esempio**:
`PhotoPrintController` ripiega su `Defunto::first()` e su due foto demo.
Serve l'elenco delle proprie pratiche e la scelta di quale aprire.

**4. Endpoint del designer rimasti scoperti**: `/admin/api/necrologio-pratica/{id}`
e `salva-preghiera` — il blade li chiama già (righe ~1733 e ~1815) e oggi
falliscono in silenzio. Vanno agganciati alla nuova entità `Necrologio`.
Da togliere invece `elabora`/`elaboraConAI`: codice morto, superato dal wizard BFL.

**5. Designer manifesti** (`FileMemorai/manifesto-designer.blade.php`, 2979
righe, il più grosso). Formati A4/A3, 50×70, 70×100, 61×45, 50×32; **condivide
`/admin/api/santi`** col ricordino designer, non duplicarla. Stampa il QR del
necrologio sul manifesto. Tre violazioni GDPR da sanare portandolo: Google Fonts
da CDN, Fabric/jsPDF da cdnjs, e **`api.qrserver.com`** (l'URL di un defunto
mandato a un fornitore esterno a ogni apertura) → QR generato in locale.

**6. QR → video memoriale**: pagina con slider di foto legata all'acquisto.
È un'altra cosa rispetto al necrologio, da non confondere.

**7. Abbonamento agenzie** (idea del committente): accesso a manifesti,
necrologio e ricordini anche senza acquistare il kit. Conseguenza:
in `AccessoStudio` la regola per le agenzie diventa "abbonamento attivo" invece
di "agenzia approvata" — meglio saperlo prima di moltiplicare i punti che
decidono chi entra.

**8. Rifiniture editor** (non bloccano nulla, si fanno a richiesta):
cornice dorata che segue la maschera; il pannello proprietà lascia i valori del
testo precedente quando si seleziona un'immagine; azione "promuovi a
predefinito" nell'area staff per pescare un layout riuscito da quelli reali.

**Pulizia tecnica dovuta**: passando a nginx + php-fpm concorrente, togliere la
riscrittura URL verso :8010 in `WizardApiController::rewriteForProxy()`.

## Nodo architetturale da tenere a mente

memoraiengine è **pratica/defunto-centrico** (l'agenzia crea la pratica).
L'e-shop MemorAI è **ordine/cliente-centrico**. Il modulo Memorial è il ponte:
il defunto è l'entità che collega ordine ↔ ricordino, e il ponte concreto è
`ordini.defunto_id` (+ `defunti.ordine_id` per la direzione opposta).

Corollario per il B2B: l'agenzia lavora **per conto di** una famiglia, quindi
ogni cosa che il designer produce ha tre soggetti distinti — l'account che la
crea (agenzia), la persona a cui si riferisce (defunto) e chi la approva
(famiglia). Non collassarli: è la ragione per cui il consenso GDPR sta sul
defunto, l'approvazione viaggia su un token dato alla famiglia, e il consenso
alla **pubblicazione** del necrologio è un terzo consenso ancora, che non si
eredita dal primo.

Non tutto passa dall'ordine, però: i **necrologi stanno sull'agenzia**, non
sull'ordine. Un'agenzia ne fa tutte le settimane e compra un kit trigesimale
ogni tanto — sono lo strumento con cui lavora, non il prodotto che compra.
