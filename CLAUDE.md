# CLAUDE.md

## Cos'è questo progetto
E-shop MemorAI: portale e-commerce per articoli memoriali e devozionali
(trigesimali, rosari/corone, photoceramiche, ricordini).
Due canali sulla stessa piattaforma: B2C privati e B2B onoranze funebri.
Fa parte dell'ecosistema kerachrom.it. Dominio lingua: italiano.

## Stack
Laravel 13 / PHP 8.3 / MySQL 8 / nwidart/laravel-modules
Server: VPS unico, /var/www/eshop. Deploy GitHub-first.
Repo: github.com/scaraalex-lang/e-shop

## Moduli previsti
Catalog (fatto), Commerce, Configurator, PhotoPrint, Memorial, AiAssistant
Ordine per dipendenze: Catalog -> Commerce -> altri

## Modulo Catalog (già implementato)
Tabelle: categories, products, product_images, attribute_definitions
Approccio ibrido: colonne fisse indicizzate (material, color, price) per i
filtri vetrina + colonna JSON `attributes` per specifiche variabili.
attribute_definitions definisce quali campi esistono per categoria e quali
sono filtrabili, senza nuove migration.
Flag su products che agganciano gli altri moduli:
is_configurable, is_photo_printable, has_qr_memorial

## Regole di business
- Prezzi in centesimi (interi), mai float
- Kit trigesimo: prezzo base include 50 ricordini, ogni pezzo extra si paga
  (campi is_kit, included_units, extra_unit_price; metodo priceForQuantity)
- B2B: prezzo pubblico uguale per tutti, sconti a scaglioni di quantità
  applicati sul singolo prodotto, solo per account approvati
- Registrazione B2B: richiesta con P.IVA -> approvazione manuale -> sconti
- Minimo d'ordine B2B espresso in numero pezzi
- Fatturazione: fase 1 export dati, integrazione SdI in fase 2
- Spedizione B2B: all'agenzia, che poi consegna alla famiglia

## Flusso foto (trasversale a tutti i prodotti con foto)
Foto Manager (elabora) -> Designer ricordini -> file pronto stampa
Moduli esistenti su memoraiengine.com da adattare (stesso stack Laravel).
B2B: bozza condivisibile con link (token) alla famiglia per approvazione,
con storico revisioni; link valido finché l'ordine è aperto.
B2C: il privato vede le bozze nella propria area account.

## Identità visiva
Editoriale da rivista, non griglia e-commerce standard.
Palette oro-panna: #faf6ec panna, #c2a35a oro, #a5863f oro scuro
Font: Cormorant Garamond (serif) + Jost (sans)
Decori: greche dorate sottili, fiori vintage in dissolvenza
Tono: bellezza e artigianato, mai lutto.

## Convenzioni
- Modelli in Modules/<Nome>/app/Models/
- Migration in Modules/<Nome>/database/migrations/
- Namespace Modules\<Nome>\...
- Dopo modifiche ai moduli: composer dump-autoload
