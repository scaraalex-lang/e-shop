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

URL degli editor: `http://<host>:8000/studio/foto` e `http://<host>:8000/studio/ricordino`.

## Perché serve il server su :8010 (deadlock)

Il proxy BFL non riceve i byte dell'immagine: riceve un **URL** e se lo scarica.
Se quell'URL rientra sulla stessa `php artisan serve` :8000 — che è
**single-worker** — la richiesta si blocca ad aspettare se stessa: deadlock.

Soluzione in `WizardApiController::rewriteForProxy()`: gli URL con path
`/storage/` (qualsiasi host) vengono riscritti su `127.0.0.1:8010`, servito da
`python3 -m http.server` (threaded) radicato in `public/`. In Fase 2, con
nginx + php-fpm concorrente, questa riscrittura si può togliere.

## Trappole (costate tempo, non ricascarci)

**`PHP_CLI_SERVER_WORKERS` è instabile qui.** `php -S` in modalità worker dà 500
vuoti e crash in questo ambiente. Usa single-worker e risolvi la concorrenza con
:8010 come sopra.

**`APP_URL=http://localhost`.** `url()` e `Storage::url()` generano
`http://localhost/...` — host sbagliato, porta assente. Il proxy finisce a
scaricare da nginx:80 (un altro sito) → 404 → l'enhance risponde 400. Nei
controller degli editor gli URL immagine si costruiscono dall'host reale della
richiesta (`$request->getSchemeAndHttpHost()`); i modelli (es. `Santo::url()`)
tornano path relativi `/storage/...`.

**Mai `pkill -f "8000"`.** Il pattern matcha anche il comando Bash che lo sta
eseguendo → si autouccide. Killa per PID preciso (`ss -ltnp | grep :8000`).

**Errore 403 `Accesso non autorizzato` sugli `/admin/api/*`.** Manca
`X-Studio-Token`: o `PHOTOPRINT_STUDIO_TOKEN` non è in `.env`, o la chiamata JS
non passa dal wrapper `window.fetch`, o hai messo l'endpoint fuori dal prefisso
`admin/api`.

## Verifica visiva

Screenshot con Playwright/chromium headless — vedi la memoria `verifica-visiva`.
Dopo ogni modifica ai blade, guarda davvero la pagina: sono editor canvas, un
errore JS non si vede nei log di Laravel. Controlla anche la console del browser.

La cartella di scambio con l'utente è `/var/www/eshop/FileMemorai/` (screenshot,
font, foto di test). Contiene anche i **due blade sorgente originali** di
memoraiengine: sono riferimento per il diff, non vanno modificati.
