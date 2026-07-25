# Ambiente di sviluppo degli editor — avvio, verifica, trappole

## Avvio

```bash
cd /var/www/eshop

# 1) app Laravel (single-worker: vedi trappola PHP_CLI_SERVER_WORKERS)
php artisan serve --host=0.0.0.0 --port=8000

# 2) static server threaded per il proxy BFL (obbligatorio, vedi deadlock)
cd public && python3 -m http.server 8010 --bind 127.0.0.1
```

Microservizi Python già attivi come servizi systemd (non vanno avviati a mano):
`bfl-proxy.service` :5000, `incisione-proxy.service` :5001, `mail-proxy.service` :5002.

Verifica che il proxy BFL risponda e abbia la chiave:

```bash
curl -s http://127.0.0.1:5000/health   # {"status":...,"key_set":true}
```

Indirizzi utili: `/studio/foto`, `/studio/ricordino` (richiedono login),
`/account/ordini/{id}/lavorazione`, `/bozza/{token}`, `/account/necrologi`,
`/ricordi/{slug-agenzia}/{percorso}`.

## Gli editor non si aprono più senza login

`/studio/*` e `/admin/api/*` stanno dietro `auth` + `AccessoStudio`: una
richiesta anonima viene reindirizzata a `/accedi` (o riceve 401 JSON se si
annuncia come XHR). Uno screenshot preso puntando l'URL secco fotografa la
pagina di accesso — non è una regressione dell'editor.

Utenti di lavoro sul DB di sviluppo: `staff@memorai.test` (staff),
`agenzia@memorai.test` e `aurora@memorai.test` (agenzie), `prova@memorai.test`
(privato). Se la password non è nota, riassegnala:

```bash
php artisan tinker --execute="\App\Models\User::where('email','staff@memorai.test')->update(['password'=>bcrypt('password')]);"
```

Per Playwright: fai login una volta e riusa il contesto.

```js
const page = await ctx.newPage();
await page.goto(B + '/accedi');
await page.fill('#email', 'staff@memorai.test');
await page.fill('#password', 'password');
await page.click('button[type=submit]');
await page.waitForURL('**/account**');
// da qui in poi le pagine /studio/* si aprono davvero
```

Per vedere gli editor **puntati su una pratica vera** non basta il login: passa
prima da `/account/ordini/{id}/lavorazione`, che è ciò che mette l'ordine in
sessione (`LavorazioneCorrente`). Entrando dritti su `/studio/ricordino` si
lavora sulla pratica di esempio.

## Perché serve il server su :8010 (deadlock)

Il proxy BFL non riceve i byte dell'immagine: riceve un **URL** e se lo scarica.
Se quell'URL rientra sulla stessa `php artisan serve` :8000 — che è
**single-worker** — la richiesta si blocca ad aspettare se stessa: deadlock.

Soluzione in `WizardApiController::rewriteForProxy()`: gli URL con path
`/storage/` (qualsiasi host) vengono riscritti su `127.0.0.1:8010`, servito da
`python3 -m http.server` (threaded) radicato in `public/`. Con nginx + php-fpm
concorrente questa riscrittura si può togliere.

## Trappole (costate tempo, non ricascarci)

**`PHP_CLI_SERVER_WORKERS` è instabile qui.** `php -S` in modalità worker dà 500
vuoti e crash in questo ambiente. Usa single-worker e risolvi la concorrenza con
:8010 come sopra.

**`APP_URL=http://localhost`.** `url()` e `Storage::url()` generano
`http://localhost/...` — host sbagliato, porta assente. Il proxy finisce a
scaricare da nginx:80 (un altro sito) → 404 → l'enhance risponde 400. Nei
controller degli editor gli URL immagine si costruiscono dall'host reale della
richiesta (`$request->getSchemeAndHttpHost()`); i modelli (es. `Santo::url()`,
`FotoPratica::url()`) tornano path relativi `/storage/...`.

**Mai `pkill -f "8000"`.** Il pattern matcha anche il comando Bash che lo sta
eseguendo → si autouccide. Killa per PID preciso (`ss -ltnp | grep :8000`).

**403 sugli `/admin/api/*`**: non è più il token (non esiste più) — è
`AccessoStudio`. L'account non è staff né agenzia approvata e non ha un ordine
aperto in lavorazione. **419**: hai messo l'endpoint fuori dal prefisso
`admin/api`, quindi il wrapper su `window.fetch` non gli allega il CSRF.
**401 JSON** su una pagina aperta da un pezzo: sessione scaduta, basta rifare
login.

**Limite di upload delle foto.** `upload_max_filesize` di sistema era 2M:
qualsiasi foto da telefono veniva respinta con un **413** generato da
`ValidatePostSize`, prima del controller. Il limite vero sta nella
configurazione **CLI** (`/etc/php/8.3/cli/conf.d/99-memorai.ini`, 24M/32M/512M),
perché `artisan serve` non propaga i flag `-d` al processo figlio che serve
l'HTTP: passare `php -d upload_max_filesize=24M artisan serve` **non serve a
niente**. In produzione (nginx + php-fpm) va nel pool fpm, non lì. Il Foto
Manager legge il limite (`limiteFotoMb`) e blocca prima di spedire.

**`@disabled(...)` dentro un tag componente Blade** (`<x-...>`) rompe il
compilatore con un ParseError su `endif`: usalo solo su HTML normale.

**Rumore noto, non nostro**: `/studio/ricordino` produce un `Permissions check
failed` nella console headless. C'era anche prima dell'auth (verificato con uno
stash del blade): non è una regressione e non impedisce nulla.

## Verifica visiva

Screenshot con Playwright/chromium headless — vedi la memoria `verifica-visiva`.
Dopo ogni modifica ai blade, guarda davvero la pagina: sono editor canvas, un
errore JS non si vede nei log di Laravel. Controlla anche la console del browser.

Per la card del necrologio guarda anche i **meta Open Graph** nel sorgente: è
quello che vedono WhatsApp e Facebook, che JavaScript non lo eseguono.

La cartella di scambio con l'utente è `/var/www/eshop/FileMemorai/` (screenshot,
font, foto di test). Contiene anche i **blade sorgente originali** dei quattro
editor di memoraiengine (foto manager, ricordino, card necrologio, manifesti):
sono riferimento per il diff, non vanno modificati.
