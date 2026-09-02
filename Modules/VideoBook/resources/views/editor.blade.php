<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="google" content="notranslate">
<title>Impaginatore | MemorAI</title>
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
<style>
:root{
  --ink:#1a1a2e;--gold:#c8a96e;--cream:#f5f0e8;--cream-dark:#ede6d8;
  --white:#fdfaf5;--gray:#8a7f72;--border:#ddd8d0;--red:#c44b3a;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--ink);height:100vh;overflow:hidden;display:flex;flex-direction:column}

nav{background:var(--ink);padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;flex-shrink:0}
.logo{color:#fff;font-size:1rem;font-weight:600;font-family:'Cormorant Garamond',serif;display:flex;align-items:center;gap:.6rem}
.logo small{color:rgba(255,255,255,.5);font-family:'DM Sans',sans-serif;font-size:.7rem;font-weight:400}
.nav-links{display:flex;align-items:center;gap:.5rem}
.nav-btn{padding:.4rem .9rem;border-radius:6px;font-size:.8rem;font-weight:500;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none}
.btn-gold{background:var(--gold);color:#fff}
.btn-ghost{background:rgba(255,255,255,.1);color:#fff}

/* Barra Strumenti: sotto la nav, la base su cui appoggiare altre funzioni
   via via che l'impaginatore cresce (formattazione testo oggi, altro poi). */
.strumenti-bar{background:var(--white);border-bottom:1px solid var(--border);padding:.5rem 1.5rem;display:flex;align-items:center;gap:.9rem;flex-shrink:0}
.strumenti-bar-btn{background:var(--ink);color:#fff;border:none;border-radius:6px;padding:.4rem .9rem;font-size:.8rem;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:.4rem}
.strumenti-bar-btn:hover{background:var(--gold)}
.strumenti-bar-hint{font-size:.75rem;color:var(--gray)}

.editor-layout{display:flex;flex:1;overflow:hidden}

/* SIDEBAR: le pagine del libro */
.sidebar{width:280px;background:var(--white);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;flex-shrink:0}
.sidebar-head{padding:1rem;border-bottom:1px solid var(--border)}
.sidebar-list{flex:1;overflow-y:auto;padding:.75rem}
.sidebar-vuota{color:var(--gray);font-size:.85rem;text-align:center;padding:2rem 1rem}

/* Formato di stampa del libro: pulsante nella testata della sidebar + modale selettore */
.formato-btn{width:100%;text-align:left;background:var(--cream);border:1px solid var(--border);border-radius:6px;padding:.5rem .6rem;font-size:.75rem;color:var(--gray);cursor:pointer;margin-bottom:.6rem;font-family:'DM Sans',sans-serif}
.formato-btn:hover{border-color:var(--gold);color:var(--ink)}
.formato-btn strong{color:var(--ink)}

.pagina-card{display:flex;align-items:stretch;gap:.4rem;border:1px solid var(--border);border-radius:8px;margin-bottom:.6rem;background:#fff;overflow:hidden}
.pagina-card.attiva{border-color:var(--gold);box-shadow:0 0 0 1px var(--gold)}
.pagina-seleziona{flex:1;display:flex;align-items:center;gap:.6rem;padding:.6rem;border:none;background:none;cursor:pointer;text-align:left;font-family:'DM Sans',sans-serif}
/* Miniatura vera (foto incluse, non solo il numero) nella lista pagine
   della sidebar: stesso disegno di .filmstrip-pagina/miniAnteprimaPagina(),
   qui verticale. Le pagine ancora senza foto restano sbiadite — "compilate"
   vs "da compilare" si vedono a colpo d'occhio. */
.pagina-mini{position:relative;width:34px;flex-shrink:0}
.pagina-mini .filmstrip-pagina{border:1px solid var(--border);box-shadow:none}
.pagina-mini-numero{position:absolute;bottom:-3px;right:-3px;background:var(--ink);color:#fff;font-size:.6rem;line-height:1;padding:2px 4px;border-radius:999px}
.pagina-seleziona.non-compilata .pagina-mini{opacity:.45}
.pagina-info{display:flex;flex-direction:column;gap:.1rem;min-width:0}
.pagina-info strong{font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pagina-info small{font-size:.72rem;color:var(--gray)}
.pagina-azioni{display:flex;flex-direction:column;border-left:1px solid var(--border)}
.pagina-azioni button{flex:1;border:none;background:none;cursor:pointer;font-size:.7rem;color:var(--gray);padding:0 .4rem;min-height:26px}
.pagina-azioni button:hover:not(:disabled){background:var(--cream);color:var(--ink)}
.pagina-azioni button:disabled{opacity:.3;cursor:not-allowed}

/* CANVAS: la doppia pagina attiva (spread), come un libro aperto */
.canvas-area{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--cream)}
.canvas-vuoto{color:var(--gray);text-align:center;display:flex;flex-direction:column;gap:1rem;align-items:center}
.canvas-col{display:flex;flex-direction:column;align-items:center;gap:.8rem;width:min(100%,1200px)}
.pagina-toolbar{display:flex;align-items:center;justify-content:space-between;width:100%;color:var(--gray);font-size:.8rem}
.pagina-toolbar button{background:#fff;color:var(--ink);border:1px solid var(--border);border-radius:6px;padding:.35rem .8rem;font-size:.75rem;cursor:pointer;font-family:'DM Sans',sans-serif}
.pagina-toolbar button:hover:not(:disabled){border-color:var(--gold)}
.pagina-toolbar button:disabled{opacity:.35;cursor:not-allowed}

/* Sfoglia pagina: bottoni grandi e fissi ai due lati del canvas, sempre ben
   visibili qualunque sia la pagina mostrata (non dentro la toolbar, che
   cambia contenuto ad ogni pagina). */
.canvas-stage{position:relative;flex:1;min-height:0;display:flex;align-items:center;justify-content:center;overflow:hidden}
.canvas-scroll{flex:1;height:100%;overflow:auto;display:flex;align-items:center;justify-content:center;padding:2rem 5rem}
.canvas-nav{position:absolute;top:50%;transform:translateY(-50%);width:52px;height:52px;flex-shrink:0;border-radius:50%;border:1px solid var(--border);background:#fff;color:var(--ink);font-size:1.7rem;line-height:1;cursor:pointer;box-shadow:0 6px 20px rgba(26,26,46,.2);display:flex;align-items:center;justify-content:center;z-index:10;transition:opacity .15s,border-color .15s,transform .15s}
.canvas-nav:hover:not(:disabled){border-color:var(--gold);transform:translateY(-50%) scale(1.06)}
.canvas-nav:disabled{opacity:.3;cursor:not-allowed}
.canvas-nav-sx{left:1.2rem}
.canvas-nav-dx{right:1.2rem}

/* Slider in basso: le FOTO caricate in tutto il libro (le pagine hanno la
   loro lista verticale in sidebar, vedi .pagina-mini), cliccabile per
   saltare alla pagina che le contiene. Sfondo scuro apposta: una card
   bianca su un fondo quasi bianco (--white su --white) si perdeva,
   invisibile — vedi nota. */
.filmstrip{flex-shrink:0;min-height:104px;display:flex;gap:.7rem;align-items:flex-end;padding:.9rem 1.4rem;overflow-x:auto;overflow-y:hidden;background:var(--ink);border-top:1px solid var(--border)}
.filmstrip-item{flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:.3rem;border:none;background:none;cursor:pointer;padding:0;width:56px;font-family:'DM Sans',sans-serif}
.filmstrip-pagina{position:relative;width:100%;background:#fff;border:2px solid rgba(255,255,255,.5);border-radius:3px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.35)}
.filmstrip-foto{display:block;width:100%;aspect-ratio:1/1;background-size:cover;background-position:center;border-radius:3px;border:2px solid rgba(255,255,255,.5);box-shadow:0 2px 8px rgba(0,0,0,.35)}
.filmstrip-item:hover .filmstrip-pagina,.filmstrip-item:hover .filmstrip-foto{border-color:var(--gold)}
.filmstrip-item.attiva .filmstrip-pagina,.filmstrip-item.attiva .filmstrip-foto{border-color:var(--gold);box-shadow:0 0 0 2px var(--gold)}
.filmstrip-riquadro{position:absolute;background:var(--cream-dark);border:1px solid var(--gold)}
.filmstrip-riquadro-foto{background-size:cover;background-position:center;border-color:rgba(255,255,255,.7)}
.filmstrip-vuota{display:flex;align-items:center;justify-content:center;color:var(--gray);font-size:.9rem}
.filmstrip-numero{font-size:.68rem;color:rgba(255,255,255,.6)}
.filmstrip-item.attiva .filmstrip-numero{color:#fff;font-weight:600}

/* Le due pagine affiancate, ravvicinate al centro con un'ombra di piega:
   il senso del libro aperto invece di un unico foglio isolato. */
.spread{position:relative;display:flex;gap:.4rem;justify-content:center;align-items:flex-start;width:100%}
.spread::before{content:'';position:absolute;top:0;bottom:0;left:50%;width:26px;transform:translateX(-50%);background:linear-gradient(to right,rgba(26,26,46,.16),transparent 45%,transparent 55%,rgba(26,26,46,.16));pointer-events:none;z-index:5}
.foglio-lato{position:relative;flex:1;min-width:0}
.foglio-assente{flex:1;min-width:0;border-radius:2px;background:rgba(26,26,46,.04)}
.foglio-lato.non-attiva .pagina-foglio,.foglio-lato.non-attiva .canvas-senza-layout{opacity:.55;filter:saturate(.75);transition:opacity .15s}
.foglio-lato.non-attiva:hover .pagina-foglio,.foglio-lato.non-attiva:hover .canvas-senza-layout{opacity:.85}
.foglio-seleziona{position:absolute;inset:0;border:none;background:transparent;cursor:pointer;z-index:6;padding:0}

.pagina-foglio{position:relative;width:100%;background:#fff;border-radius:2px;box-shadow:0 12px 40px rgba(0,0,0,.35);flex-shrink:0}

.slot-wrap,.slot-caption-wrap{position:absolute}
.slot-foto{width:100%;height:100%;border-radius:4px;background-size:cover;background-position:center;position:relative;overflow:hidden}
.slot-foto.vuoto{border:2px dashed var(--gold);background:var(--cream-dark);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;cursor:pointer;color:#a5863f}
.slot-foto.vuoto:hover{background:var(--cream)}
.slot-foto.vuoto.drag-over{background:#e9d9b0;border-style:solid}
.slot-plus{font-size:1.6rem;line-height:1;font-weight:300}
.slot-num{font-size:.7rem;letter-spacing:.05em}
.slot-foto.riempito{cursor:default}
.slot-foto.riempito.drag-over{outline:3px solid var(--gold);outline-offset:-3px}
.slot-elimina,.slot-sostituisci,.slot-strumenti{position:absolute;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;opacity:0;transition:opacity .15s;z-index:2}
.slot-foto.riempito:hover .slot-elimina,.slot-foto.riempito:hover .slot-sostituisci,.slot-foto.riempito:hover .slot-strumenti{opacity:1}
.slot-elimina{top:6px;right:6px;width:22px;height:22px;border-radius:50%;background:rgba(26,26,46,.75);color:#fff;font-size:.9rem;line-height:1}
.slot-strumenti{top:6px;left:6px;width:22px;height:22px;border-radius:50%;background:rgba(26,26,46,.75);color:#fff;font-size:.85rem;line-height:1;display:flex;align-items:center;justify-content:center}
.slot-sostituisci{bottom:6px;left:50%;transform:translateX(-50%);padding:.25rem .6rem;border-radius:999px;background:rgba(26,26,46,.75);color:#fff;font-size:.68rem}

/* foto dentro il riquadro: posizionabile (drag) e ridimensionabile (maniglia), proporzionata sull'aspect ratio originale */
.slot-foto-img{position:absolute;top:0;left:0;max-width:none;cursor:grab;-webkit-user-drag:none;user-select:none;touch-action:none}
.slot-foto-img.trascinando{cursor:grabbing}
.slot-resize-handle{position:absolute;right:6px;bottom:6px;width:16px;height:16px;border-radius:50%;background:var(--gold);border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.4);cursor:nwse-resize;opacity:0;transition:opacity .15s;z-index:2;touch-action:none}
.slot-foto.riempito:hover .slot-resize-handle,.slot-resize-handle.attivo{opacity:1}

.slot-caption-wrap{display:flex;align-items:flex-start;justify-content:center;padding-top:2px}
.slot-didascalia{width:100%;border:none;border-bottom:1px dashed rgba(165,134,63,.5);background:transparent;text-align:center;font-family:'Cormorant Garamond',serif;font-style:italic;font-size:.8rem;color:var(--ink);padding:2px 4px}
.slot-didascalia::placeholder{color:#b8ac9a}
.slot-didascalia:focus{outline:none;border-bottom-color:var(--gold)}

/* Box di testo liberi ("Strumenti" → Box di testo): sfondo semi-trasparente
   per far risaltare la scritta sopra una foto, posizione/misura libere
   (drag sulla maniglia, resize dall'angolo) invece che legate a uno slot. */
.testo-box{position:absolute;border-radius:4px;display:flex;flex-direction:column;overflow:hidden}
.testo-box-maniglia{position:absolute;top:2px;left:50%;transform:translateX(-50%);width:28px;height:14px;border-radius:4px;background:rgba(255,255,255,.25);color:#fff;font-size:.7rem;line-height:14px;text-align:center;cursor:grab;opacity:0;transition:opacity .15s;z-index:2}
.testo-box:hover .testo-box-maniglia{opacity:1}
.testo-box-contenuto{flex:1;width:100%;padding:.6rem .8rem;overflow:hidden;outline:none;cursor:text}
.testo-box-contenuto:empty::before{content:attr(data-placeholder);opacity:.55}
.testo-box-elimina,.testo-box-strumenti{position:absolute;top:4px;border:none;cursor:pointer;width:20px;height:20px;border-radius:50%;background:rgba(255,255,255,.25);color:#fff;font-size:.8rem;line-height:1;opacity:0;transition:opacity .15s;z-index:2;display:flex;align-items:center;justify-content:center}
.testo-box:hover .testo-box-elimina,.testo-box:hover .testo-box-strumenti{opacity:1}
.testo-box-elimina{right:4px}
.testo-box-strumenti{right:28px}
.testo-box-resize{position:absolute;right:4px;bottom:4px;width:14px;height:14px;border-radius:50%;background:var(--gold);border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.4);cursor:nwse-resize;opacity:0;transition:opacity .15s;z-index:2}
.testo-box:hover .testo-box-resize{opacity:1}

/* MODALE selettore template */
.overlay{position:fixed;inset:0;background:rgba(26,26,46,.65);display:flex;align-items:center;justify-content:center;z-index:200;padding:1.5rem}
.overlay[hidden]{display:none}
.modale-box{background:#fff;border-radius:10px;max-width:920px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden}
.modale-testata{padding:1.1rem 1.3rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modale-testata h2{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600}
.modale-chiudi{border:none;background:none;font-size:1.3rem;cursor:pointer;color:var(--gray);line-height:1}
.filtro-riga{display:flex;gap:.4rem;flex-wrap:wrap;padding:.9rem 1.3rem;border-bottom:1px solid var(--border)}
.filtro-chip{border:1px solid var(--border);background:#fff;border-radius:999px;padding:.3rem .75rem;font-size:.75rem;cursor:pointer;font-family:'DM Sans',sans-serif}
.filtro-chip.attivo{background:var(--ink);border-color:var(--ink);color:#fff}
.griglia-template{flex:1;overflow-y:auto;padding:1.1rem 1.3rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.9rem}
.template-card{border:1px solid var(--border);border-radius:8px;padding:.6rem;background:#fff;cursor:pointer;display:flex;flex-direction:column;gap:.4rem;text-align:left;font-family:'DM Sans',sans-serif}
.template-card:hover{border-color:var(--gold)}
.template-card.attivo{border-color:var(--gold);background:var(--cream)}
/* Anteprima del layout nella card: stesso disegno della pagina vera (slot in
   coordinate relative), ma in miniatura e con l'aspect-ratio del formato del
   libro — vedi templateAnteprimaHtml(). */
.template-anteprima{position:relative;width:100%;background:var(--cream);border:1px solid var(--border);border-radius:4px;overflow:hidden}
.template-anteprima span{position:absolute;background:var(--cream-dark);border:1px solid var(--gold);border-radius:2px}
.template-nome{font-size:.78rem;font-weight:600}
.template-badge{font-size:.68rem;color:var(--gray)}

/* Pannello Strumenti: flottante e trascinabile, NON un overlay a tutto
   schermo — deve restare aperto insieme al canvas, spostabile per non
   coprire la foto/il box su cui si sta lavorando. Tre schede, la base del
   pannello — vedi commento su editor.blade.php § Strumenti nel <script>. */
.pannello-strumenti{position:fixed;top:78px;right:1.5rem;z-index:220;width:360px;max-width:calc(100vw - 2rem);max-height:calc(100vh - 100px);background:#fff;border-radius:10px;box-shadow:0 16px 48px rgba(26,26,46,.4);display:flex;flex-direction:column;overflow:hidden}
.pannello-strumenti[hidden]{display:none}
.pannello-trascina{cursor:move;user-select:none;flex-shrink:0}
.pannello-trascina.trascinando{cursor:grabbing;background:var(--cream)}
.pannello-trascina h2::before{content:'⠿ ';color:var(--gray);font-size:.9rem}
.strumenti-tabs{display:flex;border-bottom:1px solid var(--border);padding:0 1.3rem;flex-shrink:0}
.strumenti-tab{background:none;border:none;border-bottom:2px solid transparent;padding:.7rem .3rem;margin-right:1.2rem;font-size:.82rem;color:var(--gray);cursor:pointer;font-family:'DM Sans',sans-serif}
.strumenti-tab.attivo{color:var(--ink);border-bottom-color:var(--gold);font-weight:600}
.strumenti-pannello{padding:1.2rem 1.3rem;overflow-y:auto;flex:1;min-height:0}
.strumenti-hint{color:var(--gray);font-size:.85rem;text-align:center;padding:1.5rem 0}
.strumenti-campo{margin-bottom:1rem}
.strumenti-campo label{display:block;font-size:.75rem;color:var(--gray);margin-bottom:.35rem}
.strumenti-campo select,.strumenti-campo input[type=text]{width:100%;padding:.45rem .6rem;border:1px solid var(--border);border-radius:6px;font-size:.85rem;font-family:'DM Sans',sans-serif}
.strumenti-riga{display:flex;gap:.6rem;align-items:center}
.strumenti-riga+.strumenti-riga{margin-top:.6rem}
.strumenti-slider{flex:1;display:flex;align-items:center;gap:.6rem}
.strumenti-slider input[type=range]{flex:1}
.strumenti-slider output{font-size:.72rem;color:var(--gray);width:2.6rem;text-align:right;flex-shrink:0}
.strumenti-toggle{display:flex;gap:.4rem}
.strumenti-toggle button{width:34px;height:34px;border:1px solid var(--border);border-radius:6px;background:#fff;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.85rem;color:var(--ink)}
.strumenti-toggle button.attivo{border-color:var(--gold);background:var(--cream);color:var(--gold-scuro,#a5863f)}
.strumenti-toggle button[data-sottolineato]{text-decoration:underline}
.strumenti-toggle button[data-corsivo]{font-style:italic}
input[type=color].strumenti-colore{width:38px;height:34px;padding:2px;border:1px solid var(--border);border-radius:6px;cursor:pointer;background:#fff}
.strumenti-preset{display:flex;flex-wrap:wrap;gap:.5rem}
.strumenti-preset button{border:1px solid var(--border);border-radius:6px;padding:.35rem .7rem;font-size:.75rem;background:#fff;cursor:pointer;font-family:'DM Sans',sans-serif}
.strumenti-preset button.attivo{border-color:var(--gold);background:var(--cream);font-weight:600}
.strumenti-separatore{border:none;border-top:1px solid var(--border);margin:1.1rem 0}
.strumenti-elimina-box{width:100%;background:none;border:1px solid var(--red);color:var(--red);border-radius:6px;padding:.5rem;font-size:.8rem;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:.4rem}

/* MODALE conferma */
.conferma-box{background:#fff;border-radius:10px;max-width:360px;width:100%;padding:1.3rem}
.conferma-box p{font-size:.9rem;margin-bottom:1rem;line-height:1.4}
.conferma-azioni{display:flex;gap:.6rem;justify-content:flex-end}
.conferma-azioni button{border:none;border-radius:6px;padding:.45rem 1rem;font-size:.82rem;cursor:pointer;font-family:'DM Sans',sans-serif}
.btn-annulla{background:var(--cream-dark);color:var(--ink)}
.btn-conferma{background:var(--red);color:#fff}

/* MODALE "quante pagine" — primo passo, prima che esista una sola pagina */
.quante-pagine-box{max-width:420px}
.quante-pagine-box h2{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;margin-bottom:.6rem}
.quante-pagine-box p{font-size:.85rem;color:var(--gray);line-height:1.5;margin-bottom:1.1rem}
.input-numero-pagine{width:100%;padding:.55rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:1rem;margin-bottom:1.2rem;font-family:'DM Sans',sans-serif}
.btn-salta{background:none;color:var(--gray);text-decoration:underline;padding:.45rem 0}
.btn-continua{background:var(--gold);color:#fff}

/* Pagina senza ancora un layout scelto */
.canvas-senza-layout{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;background:var(--cream-dark);border:2px dashed var(--gold);border-radius:6px}
.canvas-senza-layout p{color:var(--gray);font-size:.85rem;text-align:center;padding:0 1rem}

/* Genera video: stato accanto al bottone in nav */
.video-stato{display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:rgba(255,255,255,.75)}
.video-spinner{width:13px;height:13px;border-radius:50%;border:2px solid rgba(255,255,255,.3);border-top-color:var(--gold);animation:girastato .8s linear infinite;flex-shrink:0}
@keyframes girastato{to{transform:rotate(360deg)}}
.video-badge{font-size:.68rem;letter-spacing:.05em;text-transform:uppercase;padding:.15rem .5rem;border-radius:999px}
.video-badge.pronto{background:rgba(120,200,140,.18);color:#8fdba3}
.video-badge.errore{background:rgba(196,75,58,.2);color:#ff9a8a}

#toast{position:fixed;bottom:1.2rem;right:1.2rem;background:var(--red);color:#fff;padding:.7rem 1.1rem;border-radius:8px;font-size:.82rem;max-width:320px;z-index:300;box-shadow:0 6px 20px rgba(0,0,0,.3);opacity:0;pointer-events:none;transition:opacity .2s}
#toast.mostra{opacity:1}

/* Dentro l'overlay (iframe) del designer: il link di navigazione interno
   non serve, chiudere l'iframe (bottone × del pannello) ricarica già la
   pagina padre — vedi partials/designer-overlay.blade.php */
body.in-iframe a.nav-btn.btn-ghost{display:none}
</style>
</head>
<body>
<script>if (window !== window.top) document.body.classList.add('in-iframe');</script>

<nav>
  <div class="logo">MemorAI <small>Impaginatore</small></div>
  <div class="nav-links">
    <div class="video-stato" id="pdf-stato" hidden></div>
    <div class="video-stato" id="video-stato" hidden></div>
    <button type="button" class="nav-btn btn-gold" id="btn-genera-video">Genera video e PDF</button>
    @if ($libro->ordine_id)
      <a class="nav-btn btn-ghost" href="/account/ordini/{{ $libro->ordine_id }}/lavorazione">← Torna all'ordine</a>
    @else
      <a class="nav-btn btn-ghost" href="/account/ordini">← I miei ordini</a>
    @endif
  </div>
</nav>

<div class="strumenti-bar">
  <button type="button" class="strumenti-bar-btn" id="btn-strumenti">🖌️ Strumenti</button>
  <span class="strumenti-bar-hint">Formattazione testo, box di testo, regolazione e viraggio foto</span>
</div>

<div class="editor-layout">

  <aside class="sidebar">
    <div class="sidebar-head">
      <button type="button" class="formato-btn" id="btn-formato"></button>
      <button type="button" class="nav-btn btn-gold" style="width:100%;justify-content:center" id="btn-nuova-pagina">+ Nuova pagina</button>
    </div>
    <div class="sidebar-list" id="sidebar-list"></div>
  </aside>

  <main class="canvas-area">
    <div class="canvas-stage">
      <button type="button" class="canvas-nav canvas-nav-sx" id="btn-pagina-prev" title="Pagina precedente" aria-label="Pagina precedente">‹</button>
      <div class="canvas-scroll" id="canvas-area"></div>
      <button type="button" class="canvas-nav canvas-nav-dx" id="btn-pagina-next" title="Pagina successiva" aria-label="Pagina successiva">›</button>
    </div>
    <div class="filmstrip" id="filmstrip" hidden></div>
  </main>

</div>

<div class="overlay" id="modale-template" hidden>
  <div class="modale-box">
    <div class="modale-testata">
      <h2 id="modale-template-titolo">Scegli il layout</h2>
      <button type="button" class="modale-chiudi" id="chiudi-modale-template">×</button>
    </div>
    <div class="filtro-riga" id="filtro-riga"></div>
    <div class="griglia-template" id="griglia-template"></div>
  </div>
</div>

<div class="overlay" id="modale-formato" hidden>
  <div class="modale-box" style="max-width:460px">
    <div class="modale-testata">
      <h2>Dimensioni del fotolibro</h2>
      <button type="button" class="modale-chiudi" id="chiudi-modale-formato">×</button>
    </div>
    <div class="griglia-template" id="griglia-formato" style="padding:1.1rem 1.3rem"></div>
  </div>
</div>

<!-- Pannello flottante, non un overlay a tutto schermo: niente sfondo scuro
     che copre la pagina, si trascina dalla testata per spostarlo dove serve
     e vedere intanto la foto/il box che si sta regolando. -->
<div class="pannello-strumenti" id="modale-strumenti" hidden>
  <div class="modale-testata pannello-trascina" id="strumenti-trascina">
    <h2>Strumenti</h2>
    <button type="button" class="modale-chiudi" id="chiudi-modale-strumenti">×</button>
  </div>
  <div class="strumenti-tabs" id="strumenti-tabs">
    <button type="button" class="strumenti-tab" data-tab="testo">Testo</button>
    <button type="button" class="strumenti-tab" data-tab="box">Box di testo</button>
    <button type="button" class="strumenti-tab" data-tab="foto">Foto</button>
  </div>
  <div class="strumenti-pannello" id="strumenti-pannello"></div>
</div>

<div class="overlay" id="modale-quante-pagine" hidden>
  <div class="conferma-box quante-pagine-box">
    <h2>Da quante pagine si compone il tuo Video Book?</h2>
    <p>Sceglierai il layout di ciascuna pagina subito dopo, una alla volta. Non è definitivo: potrai comunque aggiungerne o toglierne in seguito.</p>
    <input type="number" class="input-numero-pagine" id="input-numero-pagine" min="1" max="50" value="10">
    <div class="conferma-azioni" style="justify-content:space-between">
      <button type="button" class="btn-salta" id="salta-quante-pagine">Aggiungerò le pagine una alla volta</button>
      <button type="button" class="btn-continua" id="conferma-quante-pagine">Continua</button>
    </div>
  </div>
</div>

<div class="overlay" id="modale-conferma" hidden>
  <div class="conferma-box">
    <p id="conferma-testo"></p>
    <div class="conferma-azioni">
      <button type="button" class="btn-annulla" id="conferma-no">Annulla</button>
      <button type="button" class="btn-conferma" id="conferma-si">Elimina</button>
    </div>
  </div>
</div>

<input type="file" id="input-file-nascosto" accept="image/*" hidden>
<div id="toast"></div>

<script>
(function () {
  'use strict';

  // Allega CSRF/XHR a ogni chiamata verso /admin/api/ — stesso wrapper degli
  // altri due editor, così ogni fetch() qui sotto non deve pensarci.
  const csrfToken = '{{ csrf_token() }}';
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

  const libro = @json($libroData);
  const templates = @json($templatesData);
  const formati = @json($formatiData);
  const strumenti = @json($strumentiData);
  let video = @json($videoData);
  let paginaAttivaId = libro.pagine.length ? libro.pagine[0].id : null;

  const sidebarListEl = document.getElementById('sidebar-list');
  const canvasEl = document.getElementById('canvas-area');
  const btnPaginaPrevEl = document.getElementById('btn-pagina-prev');
  const btnPaginaNextEl = document.getElementById('btn-pagina-next');
  const filmstripEl = document.getElementById('filmstrip');
  const modaleTemplateEl = document.getElementById('modale-template');
  const modaleTemplateTitoloEl = document.getElementById('modale-template-titolo');
  const filtroRigaEl = document.getElementById('filtro-riga');
  const grigliaTemplateEl = document.getElementById('griglia-template');
  const btnFormatoEl = document.getElementById('btn-formato');
  const modaleFormatoEl = document.getElementById('modale-formato');
  const grigliaFormatoEl = document.getElementById('griglia-formato');
  const btnStrumentiEl = document.getElementById('btn-strumenti');
  const modaleStrumentiEl = document.getElementById('modale-strumenti');
  const strumentiTabsEl = document.getElementById('strumenti-tabs');
  const strumentiPannelloEl = document.getElementById('strumenti-pannello');
  const modaleQuantePagineEl = document.getElementById('modale-quante-pagine');
  const inputNumeroPagineEl = document.getElementById('input-numero-pagine');
  const modaleConfermaEl = document.getElementById('modale-conferma');
  const confermaTestoEl = document.getElementById('conferma-testo');
  const inputFileEl = document.getElementById('input-file-nascosto');
  const toastEl = document.getElementById('toast');
  const videoStatoEl = document.getElementById('video-stato');
  const pdfStatoEl = document.getElementById('pdf-stato');
  const btnGeneraVideoEl = document.getElementById('btn-genera-video');

  let slotFileTarget = null; // slot in attesa del click sull'input nascosto

  function escHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function mostraErrore(testo) {
    toastEl.textContent = testo;
    toastEl.classList.add('mostra');
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(function () { toastEl.classList.remove('mostra'); }, 4000);
  }

  function conferma(testo, etichettaSi, coloreSi) {
    return new Promise(function (resolve) {
      confermaTestoEl.textContent = testo;
      const btnSi = document.getElementById('conferma-si');
      btnSi.textContent = etichettaSi || 'Elimina';
      btnSi.style.background = coloreSi || '';
      modaleConfermaEl.hidden = false;
      btnSi.onclick = function () { modaleConfermaEl.hidden = true; resolve(true); };
      document.getElementById('conferma-no').onclick = function () { modaleConfermaEl.hidden = true; resolve(false); };
    });
  }

  function paginaAttiva() {
    return libro.pagine.find(function (p) { return p.id === paginaAttivaId; }) || null;
  }

  // ---- Sidebar: elenco pagine -------------------------------------------

  function renderSidebar() {
    if (!libro.pagine.length) {
      sidebarListEl.innerHTML = '<p class="sidebar-vuota">Nessuna pagina ancora: comincia da "+ Nuova pagina".</p>';
      return;
    }
    const ordinate = libro.pagine.slice().sort(function (a, b) { return a.ordine - b.ordine; });
    sidebarListEl.innerHTML = ordinate.map(function (p, i) {
      const attiva = p.id === paginaAttivaId ? ' attiva' : '';
      const nomeTemplate = p.template ? escHtml(p.template.name) : 'Scegli il layout';
      const totale = p.template ? p.template.numero_foto : 0;
      const compilata = p.foto.length > 0;
      const sottotitolo = p.template ? (p.foto.length + '/' + totale + ' foto') : 'ancora da comporre';
      // La lista pagine è di per sé già verticale (la sidebar): qui diventa
      // il vero "slider" — miniatura vera con le foto (stessa di
      // miniAnteprimaPagina(), usata anche nello slider orizzontale delle
      // foto) invece del solo numero, e le pagine ancora senza foto restano
      // visibilmente sbiadite — a colpo d'occhio si vede cos'è compilato e
      // cosa manca ancora.
      return '' +
        '<div class="pagina-card' + attiva + '">' +
          '<button type="button" class="pagina-seleziona' + (compilata ? '' : ' non-compilata') + '" data-azione="seleziona" data-pagina="' + p.id + '">' +
            '<span class="pagina-mini">' + miniAnteprimaPagina(p) + '<span class="pagina-mini-numero">' + (i + 1) + '</span></span>' +
            '<span class="pagina-info"><strong>' + nomeTemplate + '</strong><small>' + sottotitolo + '</small></span>' +
          '</button>' +
          '<div class="pagina-azioni">' +
            '<button type="button" data-azione="su" data-pagina="' + p.id + '" ' + (i === 0 ? 'disabled' : '') + ' title="Sposta su">↑</button>' +
            '<button type="button" data-azione="giu" data-pagina="' + p.id + '" ' + (i === ordinate.length - 1 ? 'disabled' : '') + ' title="Sposta giù">↓</button>' +
            '<button type="button" data-azione="elimina-pagina" data-pagina="' + p.id + '" title="Elimina pagina">🗑</button>' +
          '</div>' +
        '</div>';
    }).join('');
  }

  sidebarListEl.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-azione]');
    if (!btn) return;
    const paginaId = Number(btn.dataset.pagina);
    const azione = btn.dataset.azione;

    if (azione === 'seleziona') { paginaAttivaId = paginaId; renderSidebar(); renderCanvas(); return; }
    if (azione === 'su' || azione === 'giu') { spostaPagina(paginaId, azione === 'su' ? -1 : 1); return; }
    if (azione === 'elimina-pagina') { eliminaPagina(paginaId); return; }
  });

  function spostaPagina(paginaId, delta) {
    const ordinate = libro.pagine.slice().sort(function (a, b) { return a.ordine - b.ordine; });
    const indice = ordinate.findIndex(function (p) { return p.id === paginaId; });
    const nuovoIndice = indice + delta;
    if (nuovoIndice < 0 || nuovoIndice >= ordinate.length) return;

    const tmp = ordinate[indice];
    ordinate[indice] = ordinate[nuovoIndice];
    ordinate[nuovoIndice] = tmp;
    ordinate.forEach(function (p, i) { p.ordine = i + 1; });

    renderSidebar();
    fetch('/admin/api/videobook/' + libro.id + '/pagine/riordina', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ordine: ordinate.map(function (p) { return p.id; }) }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) mostraErrore('Riordino non salvato: riprova.');
    });
  }

  function eliminaPagina(paginaId) {
    conferma('Eliminare questa pagina e le foto che contiene?').then(function (ok) {
      if (!ok) return;
      fetch('/admin/api/videobook/pagine/' + paginaId, { method: 'DELETE' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) { mostraErrore(res.error || 'Eliminazione non riuscita.'); return; }
          libro.pagine = libro.pagine.filter(function (p) { return p.id !== paginaId; });
          if (paginaAttivaId === paginaId) {
            const rimaste = libro.pagine.slice().sort(function (a, b) { return a.ordine - b.ordine; });
            paginaAttivaId = rimaste.length ? rimaste[0].id : null;
          }
          renderSidebar(); renderCanvas();
        });
    });
  }

  /** { larghezzaMm, altezzaMm } dal formato del libro ("20x20" = 20x20 cm), 20x20 di ripiego se non leggibile. */
  function formatoLibroMm() {
    const parti = String(libro.formato || '').toLowerCase().split('x').map(Number);
    const cm = (parti.length === 2 && parti[0] > 0 && parti[1] > 0) ? parti : [20, 20];
    return { w: cm[0] * 10, h: cm[1] * 10 };
  }

  /** aspect-ratio inline: il foglio a schermo ha la stessa proporzione della pagina stampata. */
  function stileAspectRatio() {
    const f = formatoLibroMm();
    return 'aspect-ratio:' + f.w + '/' + f.h;
  }

  // ---- Canvas: la doppia pagina attiva, affiancata come un libro aperto --
  //
  // Le pagine si accoppiano a due a due nell'ordine del libro (1-2, 3-4, …):
  // qualunque delle due sia quella "attiva" (selezionata in sidebar), lo
  // spread mostra sempre lei e la sua pagina affacciata. Solo il lato attivo
  // è interattivo (drag/zoom/carica foto): l'altro è un'anteprima cliccabile
  // che lo rende attivo, così le mutazioni restano sempre univoche.

  function renderCanvas() {
    const pagina = paginaAttiva();
    if (!pagina) {
      canvasEl.innerHTML = '<div class="canvas-vuoto"><p>Nessuna pagina selezionata.</p>' +
        '<button type="button" class="nav-btn btn-gold" id="btn-prima-pagina">Aggiungi la prima pagina</button></div>';
      const btn = document.getElementById('btn-prima-pagina');
      if (btn) btn.addEventListener('click', function () { apriSelettoreTemplate('nuova'); });
      btnPaginaPrevEl.disabled = true;
      btnPaginaNextEl.disabled = true;
      renderFilmstrip();
      return;
    }

    const ordinate = libro.pagine.slice().sort(function (a, b) { return a.ordine - b.ordine; });
    const indiceAttiva = ordinate.findIndex(function (p) { return p.id === paginaAttivaId; });
    const inizioCoppia = indiceAttiva - (indiceAttiva % 2);
    const sinistra = ordinate[inizioCoppia] || null;
    const destra = ordinate[inizioCoppia + 1] || null;

    const toolbarTesto = (pagina.template ? escHtml(pagina.template.name) : 'Nessun layout scelto') +
      ' — pagina ' + (indiceAttiva + 1) + ' di ' + ordinate.length;
    const toolbarBtn = pagina.template ? '<button type="button" id="btn-cambia-layout">Cambia layout</button>' : '';

    canvasEl.innerHTML = '<div class="canvas-col">' +
      '<div class="pagina-toolbar"><span>' + toolbarTesto + '</span>' + toolbarBtn + '</div>' +
      '<div class="spread">' + renderFoglio(sinistra) + renderFoglio(destra) + '</div>' +
      '</div>';

    const btnCambia = document.getElementById('btn-cambia-layout');
    if (btnCambia) btnCambia.addEventListener('click', function () { apriSelettoreTemplate('sostituisci'); });

    // Sfogliare avanti/indietro: bottoni grandi e fissi ai due lati del
    // canvas (vedi wiring in fondo al file), non dentro la toolbar — restano
    // sempre nello stesso punto anche cambiando pagina.
    btnPaginaPrevEl.disabled = indiceAttiva <= 0;
    btnPaginaNextEl.disabled = indiceAttiva >= ordinate.length - 1;

    renderFilmstrip();
    inizializzaFotoInterattive();
  }

  /** Sfoglia di `delta` pagine (±1) rispetto a quella attiva: usata dai bottoni laterali. */
  function vaiPaginaRelativa(delta) {
    if (!paginaAttiva()) return;
    const ordinate = libro.pagine.slice().sort(function (a, b) { return a.ordine - b.ordine; });
    const indice = ordinate.findIndex(function (p) { return p.id === paginaAttivaId; });
    const nuovoIndice = indice + delta;
    if (nuovoIndice < 0 || nuovoIndice >= ordinate.length) return;
    paginaAttivaId = ordinate[nuovoIndice].id;
    renderSidebar(); renderCanvas();
  }

  btnPaginaPrevEl.addEventListener('click', function () { vaiPaginaRelativa(-1); });
  btnPaginaNextEl.addEventListener('click', function () { vaiPaginaRelativa(1); });

  /** Anteprima in miniatura di una pagina per lo slider in basso: stessi `slots`, formato del libro. */
  function miniAnteprimaPagina(p) {
    if (!p.template) {
      return '<div class="filmstrip-pagina filmstrip-vuota" style="' + stileAspectRatio() + '">–</div>';
    }
    const fotoPerSlot = {};
    p.foto.forEach(function (f) { fotoPerSlot[f.slot] = f; });

    // Il riquadro mostra la foto vera (stessa che sta sulla pagina), non
    // solo il perimetro del layout: la miniatura deve valere come
    // riferimento visivo di cosa c'è in quella pagina, non solo di che
    // template usa.
    const riquadri = p.template.slots.map(function (s) {
      const foto = fotoPerSlot[s.ordine];
      const posizione = 'left:' + (s.x * 100) + '%;top:' + (s.y * 100) + '%;width:' + (s.w * 100) + '%;height:' + (s.h * 100) + '%';
      return foto
        ? '<span class="filmstrip-riquadro filmstrip-riquadro-foto" style="' + posizione + ';background-image:url(\'' + foto.url + '\')"></span>'
        : '<span class="filmstrip-riquadro" style="' + posizione + '"></span>';
    }).join('');
    return '<div class="filmstrip-pagina" style="' + stileAspectRatio() + '">' + riquadri + '</div>';
  }

  /**
   * Lo slider in basso: le FOTO caricate in tutto il libro (non più le
   * pagine — quelle sono già la lista verticale in sidebar, vedi
   * renderSidebar()/.pagina-mini). Una per foto, cliccabile per saltare
   * alla pagina che la contiene.
   */
  function renderFilmstrip() {
    const ordinate = libro.pagine.slice().sort(function (a, b) { return a.ordine - b.ordine; });
    const foto = [];
    ordinate.forEach(function (p, i) {
      p.foto.forEach(function (f) { foto.push({ foto: f, paginaId: p.id, numeroPagina: i + 1 }); });
    });

    if (!foto.length) { filmstripEl.hidden = true; filmstripEl.innerHTML = ''; return; }
    filmstripEl.hidden = false;

    filmstripEl.innerHTML = foto.map(function (voce) {
      const attiva = voce.paginaId === paginaAttivaId ? ' attiva' : '';
      return '<button type="button" class="filmstrip-item' + attiva + '" data-pagina="' + voce.paginaId + '" title="Pagina ' + voce.numeroPagina + '">' +
        '<span class="filmstrip-foto" style="background-image:url(\'' + voce.foto.url + '\')"></span>' +
        '<span class="filmstrip-numero">' + voce.numeroPagina + '</span>' +
        '</button>';
    }).join('');

    const attivaEl = filmstripEl.querySelector('.filmstrip-item.attiva');
    if (attivaEl) attivaEl.scrollIntoView({ inline: 'center', block: 'nearest' });
  }

  filmstripEl.addEventListener('click', function (e) {
    const item = e.target.closest('.filmstrip-item');
    if (!item) return;
    paginaAttivaId = Number(item.dataset.pagina);
    renderSidebar(); renderCanvas();
  });

  /** Un lato dello spread: la pagina (col suo stato), oppure il vuoto se il libro finisce a metà coppia. */
  function renderFoglio(pagina) {
    if (!pagina) return '<div class="foglio-assente" style="' + stileAspectRatio() + '"></div>';

    const attiva = pagina.id === paginaAttivaId;
    const overlay = attiva ? '' : (
      '<button type="button" class="foglio-seleziona" data-azione="seleziona-pagina" data-pagina="' + pagina.id + '" ' +
      'title="Modifica questa pagina" aria-label="Seleziona questa pagina"></button>'
    );

    let contenuto;
    if (!pagina.template) {
      contenuto = '<div class="canvas-senza-layout" style="' + stileAspectRatio() + '">' +
        '<p>Questa pagina non ha ancora un layout.</p>' +
        (attiva ? '<button type="button" class="nav-btn btn-gold" data-azione="scegli-layout" data-pagina="' + pagina.id + '">Scegli il layout</button>' : '') +
        '</div>';
    } else {
      contenuto = '<div class="pagina-foglio" style="' + stileAspectRatio() + '">' + slotBoxes(pagina) + testiBoxes(pagina) + '</div>';
    }

    return '<div class="foglio-lato' + (attiva ? '' : ' non-attiva') + '">' + contenuto + overlay + '</div>';
  }

  // ---- Strumenti: formattazione testo, box liberi, regolazione foto ------
  //
  // Un solo pannello (modale-strumenti) con tre schede, la base su cui
  // aggiungere altre funzioni in seguito. "Testo" formatta l'elemento di
  // testo attivo — una didascalia o un box libero, stessa forma di `stile`
  // per entrambi (vedi Support\StileTesto lato PHP) — "Box di testo" crea/
  // elimina i box liberi della pagina, "Foto" regola/vira/incornicia la
  // foto attiva. `elementoTestoAttivo`/`fotoStrumentiAttiva` tracciano SOLO
  // cosa il pannello sta modificando: non è una selezione visiva persistente
  // come `paginaAttivaId`, si perde quando cambi pagina o chiudi il modale.

  let elementoTestoAttivo = null; // { tipo: 'didascalia'|'box', id } | null
  let fotoStrumentiAttiva = null; // id foto | null

  const BASE_TESTO_REM = 0.8; // rem a dimensione:100 — stessa base per didascalie e box

  /** Lo stile CSS inline (font/dimensione/allineamento/peso/decorazione/colore) di un elemento di testo. */
  function stileTestoCss(stile) {
    return 'font-family:"' + stile.font + '";' +
      'font-size:' + (BASE_TESTO_REM * stile.dimensione / 100).toFixed(3) + 'rem;' +
      'text-align:' + stile.allineamento + ';' +
      'font-weight:' + (stile.grassetto ? '700' : '400') + ';' +
      'text-decoration:' + (stile.sottolineato ? 'underline' : 'none') + ';' +
      'font-style:' + (stile.corsivo ? 'italic' : 'normal') + ';' +
      'color:' + stile.colore + ';';
  }

  const VIRAGGIO_CSS = {
    seppia:  'sepia(.75)',
    bn:      'grayscale(1)',
    vintage: 'sepia(.35) saturate(1.3) contrast(.92)',
    freddo:  'hue-rotate(180deg) saturate(1.15)',
  };

  /** Il filtro CSS (regolazione + viraggio) da applicare a <img> a schermo — stessa combinazione usata da ctx.filter nel PDF. */
  function filtroFotoCss(stile) {
    const parti = ['brightness(' + stile.luminosita + '%)', 'contrast(' + stile.contrasto + '%)', 'saturate(' + stile.saturazione + '%)'];
    if (stile.viraggio && VIRAGGIO_CSS[stile.viraggio]) parti.push(VIRAGGIO_CSS[stile.viraggio]);
    return parti.join(' ');
  }

  // Due misure per lo stesso bordino: px per lo schermo (dimensione fissa,
  // decorativa), mm per la stampa (BORDI_MM sotto, usato da disegnaCover).
  const BORDI_CSS = {
    'bianco-sottile': '4px solid #ffffff',
    'oro-sottile':    '3px solid #c8a96e',
    'nero-sottile':   '3px solid #1a1a2e',
    'bianco-spesso':  '10px solid #ffffff',
  };

  function bordoFotoCss(stile) {
    return stile.bordo && BORDI_CSS[stile.bordo] ? ('border:' + BORDI_CSS[stile.bordo]) : '';
  }

  function slotBoxes(pagina) {
    const tpl = pagina.template;
    if (!tpl) return '<p style="padding:1rem">Il layout di questa pagina non esiste più. Cambia layout dalla sidebar.</p>';

    const fotoPerSlot = {};
    pagina.foto.forEach(function (f) { fotoPerSlot[f.slot] = f; });
    const testiPerSlot = {};
    (pagina.testi || []).forEach(function (t) {
      if (t.slot != null) (testiPerSlot[t.slot] = testiPerSlot[t.slot] || []).push(t);
    });

    return tpl.slots.map(function (s) {
      const foto = fotoPerSlot[s.ordine];
      const xPct = s.x * 100, yPct = s.y * 100, wPct = s.w * 100, hPct = s.h * 100;
      const capH = Math.max(0, Math.min(8, 99 - (yPct + hPct)));

      // I box agganciati a questo slot vivono DENTRO il contenitore della
      // foto (.slot-foto, overflow:hidden): non solo vincolati per calcolo
      // — non possono uscirne nemmeno con un bug, li taglierebbe il
      // contenitore. Vedi testoBoxHtml()/CSS .slot-foto.
      const testiSlot = foto ? (testiPerSlot[s.ordine] || []).map(testoBoxHtml).join('') : '';

      const fotoHtml = foto
        ? '<div class="slot-foto riempito" data-slot="' + s.ordine + '" data-foto="' + foto.id + '" data-pagina="' + pagina.id + '" style="' + bordoFotoCss(foto.stile) + '">' +
            '<img class="slot-foto-img" src="' + foto.url + '" alt="" draggable="false" style="filter:' + filtroFotoCss(foto.stile) + '">' +
            '<div class="slot-resize-handle" title="Trascina per ingrandire o rimpicciolire"></div>' +
            '<button type="button" class="slot-elimina" data-azione="elimina-foto" data-foto="' + foto.id + '" title="Rimuovi">×</button>' +
            '<button type="button" class="slot-strumenti" data-azione="strumenti-foto" data-foto="' + foto.id + '" title="Regola, viraggio, bordino">🎨</button>' +
            '<button type="button" class="slot-sostituisci" data-azione="sostituisci-foto" data-slot="' + s.ordine + '">Sostituisci</button>' +
            testiSlot +
          '</div>'
        : '<div class="slot-foto vuoto" data-slot="' + s.ordine + '" data-pagina="' + pagina.id + '">' +
            '<span class="slot-plus">+</span><span class="slot-num">Foto ' + s.ordine + '</span>' +
          '</div>';

      const didascaliaHtml = foto
        ? '<input type="text" class="slot-didascalia" maxlength="180" placeholder="Didascalia…" value="' + escHtml(foto.didascalia) + '" data-azione="didascalia" data-foto="' + foto.id + '" style="' + stileTestoCss(foto.stile) + '">'
        : '';

      return '' +
        '<div class="slot-wrap" style="left:' + xPct + '%;top:' + yPct + '%;width:' + wPct + '%;height:' + hPct + '%">' + fotoHtml + '</div>' +
        '<div class="slot-caption-wrap" style="left:' + xPct + '%;top:' + (yPct + hPct) + '%;width:' + wPct + '%;height:' + capH + '%">' + didascaliaHtml + '</div>';
    }).join('');
  }

  /** #rrggbb + opacità percentuale → rgba(), per lo sfondo semi-trasparente del box di testo. */
  function hexToRgba(hex, opacitaPct) {
    const r = parseInt(hex.slice(1, 3), 16), g = parseInt(hex.slice(3, 5), 16), b = parseInt(hex.slice(5, 7), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + (opacitaPct / 100) + ')';
  }

  /**
   * Il markup di UN box di testo — usato sia agganciato a uno slot (dentro
   * .slot-foto, coordinate relative allo slot) sia libero su tutta la
   * pagina (dentro .pagina-foglio, coordinate relative alla pagina): stesso
   * disegno, cambia solo il contenitore che lo ospita (vedi slotBoxes() e
   * testiBoxes()).
   */
  function testoBoxHtml(t) {
    const st = t.stile;
    const xPct = t.x * 100, yPct = t.y * 100, wPct = t.w * 100, hPct = t.h * 100;
    return '<div class="testo-box" data-testo="' + t.id + '" ' +
        'style="left:' + xPct + '%;top:' + yPct + '%;width:' + wPct + '%;height:' + hPct + '%;background:' + hexToRgba(st.sfondo_colore, st.sfondo_opacita) + '">' +
      '<div class="testo-box-maniglia" data-azione="sposta-testo" data-testo="' + t.id + '" title="Trascina per spostare">⠿</div>' +
      '<div class="testo-box-contenuto" contenteditable="true" data-azione="contenuto-testo" data-testo="' + t.id + '" data-placeholder="Scrivi qui…" style="' + stileTestoCss(st) + '">' + escHtml(t.testo || '') + '</div>' +
      '<button type="button" class="testo-box-elimina" data-azione="elimina-testo" data-testo="' + t.id + '" title="Elimina box">×</button>' +
      '<button type="button" class="testo-box-strumenti" data-azione="strumenti-testo" data-testo="' + t.id + '" title="Formatta">🖌️</button>' +
      '<div class="testo-box-resize" data-azione="ridimensiona-testo" data-testo="' + t.id + '" title="Ridimensiona"></div>' +
      '</div>';
  }

  /** I box di testo LIBERI della pagina (senza slot): quelli agganciati a una foto sono già dentro slotBoxes(). */
  function testiBoxes(pagina) {
    return (pagina.testi || []).filter(function (t) { return t.slot == null; }).map(testoBoxHtml).join('');
  }

  function trovaTesto(testoId) {
    const pagina = paginaAttiva();
    if (!pagina) return null;
    return (pagina.testi || []).find(function (t) { return t.id === testoId; }) || null;
  }

  /** Il contenitore che vincola questo box: il riquadro della sua foto se è agganciato a uno slot, la pagina intera se è libero. */
  function contenitoreBox(box, testo) {
    return testo.slot != null ? box.closest('.slot-foto') : box.closest('.pagina-foglio');
  }

  function iniziaTrascinamentoBox(e, maniglia) {
    const box = maniglia.closest('.testo-box');
    const testo = trovaTesto(Number(box.dataset.testo));
    if (!testo) return;
    const foglio = contenitoreBox(box, testo);
    if (!foglio) return;
    e.preventDefault();
    maniglia.setPointerCapture(e.pointerId);

    const frameW = foglio.clientWidth, frameH = foglio.clientHeight;
    const startX = e.clientX, startY = e.clientY;
    const startPosX = testo.x, startPosY = testo.y;

    function onMove(ev) {
      const dx = (ev.clientX - startX) / frameW;
      const dy = (ev.clientY - startY) / frameH;
      testo.x = Math.max(0, Math.min(1 - testo.w, startPosX + dx));
      testo.y = Math.max(0, Math.min(1 - testo.h, startPosY + dy));
      box.style.left = (testo.x * 100) + '%';
      box.style.top = (testo.y * 100) + '%';
    }
    function onUp() {
      maniglia.removeEventListener('pointermove', onMove);
      maniglia.removeEventListener('pointerup', onUp);
      salvaPosizioneTesto(testo);
    }
    maniglia.addEventListener('pointermove', onMove);
    maniglia.addEventListener('pointerup', onUp, { once: true });
  }

  function iniziaRidimensionamentoBox(e, handle) {
    const box = handle.closest('.testo-box');
    const testo = trovaTesto(Number(box.dataset.testo));
    if (!testo) return;
    const foglio = contenitoreBox(box, testo);
    if (!foglio) return;
    e.preventDefault();
    e.stopPropagation();
    handle.setPointerCapture(e.pointerId);

    const frameW = foglio.clientWidth, frameH = foglio.clientHeight;
    const startX = e.clientX, startY = e.clientY;
    const startW = testo.w, startH = testo.h;

    function onMove(ev) {
      const dx = (ev.clientX - startX) / frameW;
      const dy = (ev.clientY - startY) / frameH;
      testo.w = Math.max(0.05, Math.min(1 - testo.x, startW + dx));
      testo.h = Math.max(0.03, Math.min(1 - testo.y, startH + dy));
      box.style.width = (testo.w * 100) + '%';
      box.style.height = (testo.h * 100) + '%';
    }
    function onUp() {
      handle.removeEventListener('pointermove', onMove);
      handle.removeEventListener('pointerup', onUp);
      salvaPosizioneTesto(testo);
    }
    handle.addEventListener('pointermove', onMove);
    handle.addEventListener('pointerup', onUp, { once: true });
  }

  function salvaPosizioneTesto(testo) {
    fetch('/admin/api/videobook/testi/' + testo.id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ x: testo.x, y: testo.y, w: testo.w, h: testo.h }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) mostraErrore(res.error || 'Posizione del box non salvata.');
    });
  }

  let salvaContenutoTestoTimeout = null;

  /** Il contenuto del box (contenteditable) si salva con un piccolo ritardo mentre si scrive, non ad ogni tasto. */
  function pianificaSalvaContenutoTesto(testoId, contenuto) {
    clearTimeout(salvaContenutoTestoTimeout);
    salvaContenutoTestoTimeout = setTimeout(function () {
      fetch('/admin/api/videobook/testi/' + testoId, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ testo: contenuto }),
      }).then(function (r) { return r.json(); }).then(function (res) {
        if (!res.success) mostraErrore(res.error || 'Testo non salvato.');
      });
    }, 600);
  }

  function eliminaBoxTesto(testoId) {
    conferma('Eliminare questo box di testo?').then(function (ok) {
      if (!ok) return;
      fetch('/admin/api/videobook/testi/' + testoId, { method: 'DELETE' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) { mostraErrore(res.error || 'Eliminazione non riuscita.'); return; }
          const pagina = paginaAttiva();
          if (pagina) pagina.testi = (pagina.testi || []).filter(function (t) { return t.id !== testoId; });
          if (elementoTestoAttivo && elementoTestoAttivo.tipo === 'box' && elementoTestoAttivo.id === testoId) elementoTestoAttivo = null;
          renderCanvas();
        });
    });
  }

  // ---- Posizione e zoom della foto dentro il riquadro ---------------------
  //
  // Niente Fabric.js qui (editor vanilla, vedi CLAUDE.md/skill studio-editor):
  // l'immagine è un <img> assoluto dentro `.slot-foto` (overflow:hidden fa da
  // cornice). `scala` 1 = il minimo che copre il riquadro senza margini
  // vuoti ("cover"); sopra 1 ingrandisce, sotto 1 rimpicciolisce (la foto
  // non riempie più tutta la cornice, resta centrata sul margine che avanza
  // invece di poter scorrere). `pos_x`/`pos_y` (0..1) sono la posizione nel
  // margine di scorrimento residuo quando la foto eccede la cornice. Stessa
  // logica riusata in disegnaCover() per il PDF, così stampa e schermo
  // combaciano.

  const MIN_SCALA = 0.5;
  const MAX_SCALA = 4;

  function clamp01(v) { return Math.max(0, Math.min(1, v)); }

  /** frameW/frameH/dispW/dispH/slackX/slackY per questa foto, dato lo stato attuale (scala inclusa). */
  function geometriaFoto(frameW, frameH, natW, natH, scala) {
    const baseScale = Math.max(frameW / natW, frameH / natH);
    const dispW = natW * baseScale * scala;
    const dispH = natH * baseScale * scala;
    return {
      baseScale: baseScale, dispW: dispW, dispH: dispH,
      // Positivo: la foto eccede la cornice, c'è margine da scorrere (drag).
      // Negativo o zero: la foto è più piccola/pari alla cornice (rimpicciolita), resta centrata.
      slackX: dispW - frameW, slackY: dispH - frameH,
    };
  }

  /** Offset (px) di un asse dato il suo slack: scorrimento nel margine se positivo, centratura se no. */
  function offsetAsse(slack, pos) {
    return slack > 0 ? -(pos * slack) : -slack / 2;
  }

  function applicaTrasformazioneFoto(slotEl, img, foto) {
    const frameW = slotEl.clientWidth, frameH = slotEl.clientHeight;
    const natW = img.naturalWidth, natH = img.naturalHeight;
    if (!frameW || !frameH || !natW || !natH) return;
    const scala = foto.scala || 1;
    const posX = foto.pos_x == null ? 0.5 : foto.pos_x;
    const posY = foto.pos_y == null ? 0.5 : foto.pos_y;
    const geo = geometriaFoto(frameW, frameH, natW, natH, scala);
    img.style.width = geo.dispW + 'px';
    img.style.height = geo.dispH + 'px';
    img.style.left = offsetAsse(geo.slackX, posX) + 'px';
    img.style.top = offsetAsse(geo.slackY, posY) + 'px';
    img._geo = { frameW: frameW, frameH: frameH, natW: natW, natH: natH };
  }

  function trovaPagina(paginaId) {
    return libro.pagine.find(function (p) { return p.id === paginaId; }) || null;
  }

  function trovaFotoPerId(fotoId) {
    const pagina = paginaAttiva();
    if (!pagina) return null;
    return pagina.foto.find(function (f) { return f.id === fotoId; }) || null;
  }

  // Attraversa TUTTI i riquadri visibili nel canvas, non solo quelli della
  // pagina attiva: lo spread mostra sempre anche la pagina affacciata (sola
  // anteprima, vedi renderFoglio), che ha bisogno comunque della stessa
  // trasformazione per non apparire senza ritaglio/posizione.
  function inizializzaFotoInterattive() {
    canvasEl.querySelectorAll('.slot-foto.riempito').forEach(function (slotEl) {
      const pagina = trovaPagina(Number(slotEl.dataset.pagina));
      const foto = pagina ? pagina.foto.find(function (f) { return f.id === Number(slotEl.dataset.foto); }) : null;
      const img = slotEl.querySelector('.slot-foto-img');
      if (!img || !foto) return;
      const applica = function () { applicaTrasformazioneFoto(slotEl, img, foto); };
      if (img.complete && img.naturalWidth) applica(); else img.addEventListener('load', applica, { once: true });
    });
  }

  // Ricalcola tutte le foto visibili se la finestra cambia dimensione (la
  // cornice è in percentuale, quindi anche la sua misura in px cambia).
  window.addEventListener('resize', function () { inizializzaFotoInterattive(); });

  canvasEl.addEventListener('pointerdown', function (e) {
    const handle = e.target.closest('.slot-resize-handle');
    if (handle) { iniziaRidimensionamentoFoto(e, handle); return; }
    const img = e.target.closest('.slot-foto-img');
    if (img) { iniziaTrascinamentoFoto(e, img); return; }
    const maniglia = e.target.closest('.testo-box-maniglia');
    if (maniglia) { iniziaTrascinamentoBox(e, maniglia); return; }
    const resizeBox = e.target.closest('.testo-box-resize');
    if (resizeBox) { iniziaRidimensionamentoBox(e, resizeBox); }
  });

  function iniziaTrascinamentoFoto(e, img) {
    const slotEl = img.closest('.slot-foto');
    const foto = trovaFotoPerId(Number(slotEl.dataset.foto));
    if (!foto || !img._geo) return;
    e.preventDefault();
    img.setPointerCapture(e.pointerId);
    img.classList.add('trascinando');

    const scala = foto.scala || 1;
    const geo = geometriaFoto(img._geo.frameW, img._geo.frameH, img._geo.natW, img._geo.natH, scala);
    const startX = e.clientX, startY = e.clientY;
    const startPosX = foto.pos_x == null ? 0.5 : foto.pos_x;
    const startPosY = foto.pos_y == null ? 0.5 : foto.pos_y;

    function onMove(ev) {
      const dx = ev.clientX - startX, dy = ev.clientY - startY;
      foto.pos_x = geo.slackX > 0 ? clamp01(startPosX - dx / geo.slackX) : startPosX;
      foto.pos_y = geo.slackY > 0 ? clamp01(startPosY - dy / geo.slackY) : startPosY;
      img.style.left = offsetAsse(geo.slackX, foto.pos_x) + 'px';
      img.style.top = offsetAsse(geo.slackY, foto.pos_y) + 'px';
    }
    function onUp() {
      img.removeEventListener('pointermove', onMove);
      img.removeEventListener('pointerup', onUp);
      img.classList.remove('trascinando');
      salvaPosizioneFoto(foto);
    }
    img.addEventListener('pointermove', onMove);
    img.addEventListener('pointerup', onUp, { once: true });
  }

  function iniziaRidimensionamentoFoto(e, handle) {
    const slotEl = handle.closest('.slot-foto');
    const img = slotEl.querySelector('.slot-foto-img');
    const foto = trovaFotoPerId(Number(slotEl.dataset.foto));
    if (!foto || !img._geo) return;
    e.preventDefault();
    e.stopPropagation();
    handle.setPointerCapture(e.pointerId);
    handle.classList.add('attivo');

    const startX = e.clientX, startY = e.clientY;
    const startScala = foto.scala || 1;
    const diagonale = Math.hypot(img._geo.frameW, img._geo.frameH);

    function onMove(ev) {
      const dx = ev.clientX - startX, dy = ev.clientY - startY;
      // Trascinare verso il basso/destra ingrandisce, verso l'alto/sinistra rimpicciolisce.
      const delta = (dx + dy) / diagonale;
      foto.scala = Math.max(MIN_SCALA, Math.min(MAX_SCALA, startScala + delta * 2));
      applicaTrasformazioneFoto(slotEl, img, foto); // ricalcola e riporta la posizione nel nuovo margine
    }
    function onUp() {
      handle.removeEventListener('pointermove', onMove);
      handle.removeEventListener('pointerup', onUp);
      handle.classList.remove('attivo');
      salvaPosizioneFoto(foto);
    }
    handle.addEventListener('pointermove', onMove);
    handle.addEventListener('pointerup', onUp, { once: true });
  }

  function salvaPosizioneFoto(foto) {
    fetch('/admin/api/videobook/foto/' + foto.id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ scala: foto.scala, pos_x: foto.pos_x, pos_y: foto.pos_y }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) mostraErrore(res.error || 'Posizione non salvata.');
    });
  }

  canvasEl.addEventListener('dragover', function (e) {
    const slot = e.target.closest('.slot-foto');
    if (!slot) return;
    e.preventDefault();
    slot.classList.add('drag-over');
  });
  canvasEl.addEventListener('dragleave', function (e) {
    const slot = e.target.closest('.slot-foto');
    if (slot) slot.classList.remove('drag-over');
  });
  canvasEl.addEventListener('drop', function (e) {
    const slot = e.target.closest('.slot-foto');
    if (!slot) return;
    e.preventDefault();
    slot.classList.remove('drag-over');
    const file = e.dataTransfer.files && e.dataTransfer.files[0];
    if (file) caricaFotoSlot(Number(slot.dataset.slot), file);
  });

  canvasEl.addEventListener('click', function (e) {
    const azioneBtn = e.target.closest('[data-azione]');
    if (azioneBtn) {
      const tipo = azioneBtn.dataset.azione;
      if (tipo === 'elimina-foto') { eliminaFoto(Number(azioneBtn.dataset.foto)); return; }
      if (tipo === 'sostituisci-foto') { slotFileTarget = Number(azioneBtn.dataset.slot); inputFileEl.click(); return; }
      if (tipo === 'seleziona-pagina') { paginaAttivaId = Number(azioneBtn.dataset.pagina); renderSidebar(); renderCanvas(); return; }
      if (tipo === 'scegli-layout') { paginaAttivaId = Number(azioneBtn.dataset.pagina); apriSelettoreTemplate('assegna'); return; }
      if (tipo === 'strumenti-foto') { fotoStrumentiAttiva = Number(azioneBtn.dataset.foto); apriStrumenti('foto'); return; }
      if (tipo === 'strumenti-testo') { elementoTestoAttivo = { tipo: 'box', id: Number(azioneBtn.dataset.testo) }; apriStrumenti('testo'); return; }
      if (tipo === 'elimina-testo') { eliminaBoxTesto(Number(azioneBtn.dataset.testo)); return; }
      return;
    }
    const slotVuoto = e.target.closest('.slot-foto.vuoto');
    if (slotVuoto) { slotFileTarget = Number(slotVuoto.dataset.slot); inputFileEl.click(); }
  });

  canvasEl.addEventListener('change', function (e) {
    if (!e.target.matches('.slot-didascalia')) return;
    aggiornaDidascalia(Number(e.target.dataset.foto), e.target.value);
  });

  // Il testo del box (contenteditable) si salva da solo mentre si scrive — vedi pianificaSalvaContenutoTesto().
  canvasEl.addEventListener('input', function (e) {
    if (!e.target.matches('.testo-box-contenuto')) return;
    const testoId = Number(e.target.dataset.testo);
    const testo = trovaTesto(testoId);
    const contenuto = e.target.textContent;
    if (testo) testo.testo = contenuto;
    pianificaSalvaContenutoTesto(testoId, contenuto);
  });

  // Cliccare in una didascalia o in un box marca QUELLO come l'elemento che
  // "Strumenti" → Testo formatta — non è una selezione persistente, vive
  // solo finché non si clicca altrove o si cambia pagina.
  canvasEl.addEventListener('focusin', function (e) {
    if (e.target.matches('.slot-didascalia')) {
      elementoTestoAttivo = { tipo: 'didascalia', id: Number(e.target.dataset.foto) };
      if (!modaleStrumentiEl.hidden) renderPannelloStrumenti();
      return;
    }
    if (e.target.matches('.testo-box-contenuto')) {
      elementoTestoAttivo = { tipo: 'box', id: Number(e.target.dataset.testo) };
      if (!modaleStrumentiEl.hidden) renderPannelloStrumenti();
    }
  });

  inputFileEl.addEventListener('change', function () {
    const file = inputFileEl.files && inputFileEl.files[0];
    inputFileEl.value = '';
    if (file && slotFileTarget !== null) caricaFotoSlot(slotFileTarget, file);
  });

  function caricaFotoSlot(slot, file) {
    const pagina = paginaAttiva();
    if (!pagina) return;
    const fd = new FormData();
    fd.append('slot', slot);
    fd.append('photo', file);
    fetch('/admin/api/videobook/pagine/' + pagina.id + '/foto', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) { mostraErrore(res.error || 'Caricamento non riuscito.'); return; }
        const indice = pagina.foto.findIndex(function (f) { return f.slot === slot; });
        if (indice >= 0) pagina.foto[indice] = res.foto; else pagina.foto.push(res.foto);
        renderSidebar(); renderCanvas();
      })
      .catch(function () { mostraErrore('Caricamento non riuscito: riprova.'); });
  }

  function aggiornaDidascalia(fotoId, testo) {
    const foto = trovaFotoPerId(fotoId);
    if (foto) foto.didascalia = testo; // altrimenti un giro di renderCanvas() (es. cambio pagina e ritorno) la riporterebbe al valore vecchio
    fetch('/admin/api/videobook/foto/' + fotoId, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ didascalia: testo }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) mostraErrore(res.error || 'Didascalia non salvata.');
    });
  }

  function eliminaFoto(fotoId) {
    conferma('Togliere questa foto dal riquadro?').then(function (ok) {
      if (!ok) return;
      fetch('/admin/api/videobook/foto/' + fotoId, { method: 'DELETE' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) { mostraErrore(res.error || 'Eliminazione non riuscita.'); return; }
          const pagina = paginaAttiva();
          if (pagina) pagina.foto = pagina.foto.filter(function (f) { return f.id !== fotoId; });
          renderSidebar(); renderCanvas();
        });
    });
  }

  // ---- Selettore template (nuova pagina / cambia layout) -----------------

  let modoSelettore = 'nuova'; // 'nuova' | 'sostituisci' | 'assegna' (pagina creata dal passo "quante pagine", senza layout)

  document.getElementById('btn-nuova-pagina').addEventListener('click', function () { apriSelettoreTemplate('nuova'); });

  document.getElementById('chiudi-modale-template').addEventListener('click', chiudiSelettoreTemplate);
  modaleTemplateEl.addEventListener('click', function (e) { if (e.target === modaleTemplateEl) chiudiSelettoreTemplate(); });

  function apriSelettoreTemplate(modo) {
    modoSelettore = modo;
    modaleTemplateTitoloEl.textContent = modo === 'sostituisci' ? 'Cambia layout della pagina'
      : modo === 'assegna' ? 'Scegli il layout per questa pagina'
      : 'Scegli il layout della nuova pagina';
    renderFiltroRiga();
    renderGrigliaTemplate(null);
    modaleTemplateEl.hidden = false;
  }

  function chiudiSelettoreTemplate() { modaleTemplateEl.hidden = true; }

  function renderFiltroRiga() {
    const numeri = Array.from(new Set(templates.map(function (t) { return t.numero_foto; }))).sort(function (a, b) { return a - b; });
    filtroRigaEl.innerHTML = '<button type="button" class="filtro-chip attivo" data-numero="">Tutti</button>' +
      numeri.map(function (n) { return '<button type="button" class="filtro-chip" data-numero="' + n + '">' + n + ' foto</button>'; }).join('');
  }

  filtroRigaEl.addEventListener('click', function (e) {
    const chip = e.target.closest('.filtro-chip');
    if (!chip) return;
    filtroRigaEl.querySelectorAll('.filtro-chip').forEach(function (c) { c.classList.remove('attivo'); });
    chip.classList.add('attivo');
    renderGrigliaTemplate(chip.dataset.numero ? Number(chip.dataset.numero) : null);
  });

  /**
   * L'anteprima del layout nel selettore: disegnata dagli stessi `slots` (in
   * coordinate relative) usati per la pagina vera, con l'aspect-ratio del
   * formato scelto per QUESTO libro — non l'anteprima statica 4:3 salvata
   * una volta sola sul template (t.thumbnail, generata da
   * GeneratoreAnteprimaTemplate), che apparirebbe distorta o fuori
   * proporzione su un formato quadrato o molto panoramico.
   */
  function templateAnteprimaHtml(t) {
    const riquadri = (t.slots || []).slice().sort(function (a, b) { return a.ordine - b.ordine; }).map(function (s) {
      return '<span style="position:absolute;left:' + (s.x * 100) + '%;top:' + (s.y * 100) + '%;width:' + (s.w * 100) + '%;height:' + (s.h * 100) + '%"></span>';
    }).join('');
    return '<div class="template-anteprima" style="' + stileAspectRatio() + '">' + riquadri + '</div>';
  }

  function renderGrigliaTemplate(filtroNumero) {
    const lista = filtroNumero ? templates.filter(function (t) { return t.numero_foto === filtroNumero; }) : templates;
    grigliaTemplateEl.innerHTML = lista.map(function (t) {
      return '<button type="button" class="template-card" data-template="' + t.id + '">' +
        templateAnteprimaHtml(t) +
        '<span class="template-nome">' + escHtml(t.name) + '</span>' +
        '<span class="template-badge">' + t.numero_foto + ' foto</span>' +
        '</button>';
    }).join('');
  }

  grigliaTemplateEl.addEventListener('click', function (e) {
    const card = e.target.closest('.template-card');
    if (!card) return;
    const templateId = Number(card.dataset.template);
    if (modoSelettore === 'sostituisci' || modoSelettore === 'assegna') cambiaTemplatePaginaAttiva(templateId);
    else aggiungiPagina(templateId);
    chiudiSelettoreTemplate();
  });

  function aggiungiPagina(templateId) {
    fetch('/admin/api/videobook/' + libro.id + '/pagine', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ template_id: templateId }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) { mostraErrore(res.error || 'Impossibile aggiungere la pagina.'); return; }
      libro.pagine.push(res.pagina);
      paginaAttivaId = res.pagina.id;
      renderSidebar(); renderCanvas();
    });
  }

  function cambiaTemplatePaginaAttiva(templateId) {
    const pagina = paginaAttiva();
    if (!pagina) return;
    fetch('/admin/api/videobook/pagine/' + pagina.id + '/template', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ template_id: templateId }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) { mostraErrore(res.error || 'Impossibile cambiare layout.'); return; }
      const indice = libro.pagine.findIndex(function (p) { return p.id === pagina.id; });
      libro.pagine[indice] = res.pagina;
      renderSidebar(); renderCanvas();
    });
  }

  // ---- Formato di stampa del libro ----------------------------------------
  //
  // Proprietà del libro (non del template di pagina, vedi commento sulla
  // migration): cambia l'aspect-ratio del foglio a schermo — stessa forma
  // usata da formatoLibroMm() per il PDF, così le due cose non divergono mai.
  // L'archivio (`formati`) arriva dal server — Support\FormatiLibro —, unica
  // fonte condivisa con la validazione lato PHP.

  function trovaFormato(codice) {
    return formati.find(function (f) { return f.codice === codice; }) || null;
  }

  function renderFormatoCorrente() {
    const attuale = trovaFormato(libro.formato);
    const misura = attuale ? (attuale.larghezza_cm + ' × ' + attuale.altezza_cm + ' cm') : libro.formato;
    btnFormatoEl.innerHTML = '📐 <strong>' + misura + '</strong> — cambia dimensioni';
  }

  btnFormatoEl.addEventListener('click', function () {
    grigliaFormatoEl.innerHTML = formati.map(function (f) {
      const attivo = f.codice === libro.formato ? ' attivo' : '';
      return '<button type="button" class="template-card' + attivo + '" data-formato="' + f.codice + '">' +
        '<span class="template-nome">' + escHtml(f.nome) + '</span>' +
        '<span class="template-badge">' + f.larghezza_cm + ' × ' + f.altezza_cm + ' cm</span>' +
        '</button>';
    }).join('');
    modaleFormatoEl.hidden = false;
  });

  document.getElementById('chiudi-modale-formato').addEventListener('click', function () { modaleFormatoEl.hidden = true; });
  modaleFormatoEl.addEventListener('click', function (e) { if (e.target === modaleFormatoEl) modaleFormatoEl.hidden = true; });

  grigliaFormatoEl.addEventListener('click', function (e) {
    const card = e.target.closest('[data-formato]');
    if (!card) return;
    const nuovoFormato = card.dataset.formato;
    modaleFormatoEl.hidden = true;
    if (nuovoFormato === libro.formato) return;

    const applica = function () {
      fetch('/admin/api/videobook/' + libro.id + '/formato', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ formato: nuovoFormato }),
      }).then(function (r) { return r.json(); }).then(function (res) {
        if (!res.success) { mostraErrore(res.error || 'Formato non salvato.'); return; }
        libro.formato = res.formato;
        renderFormatoCorrente();
        renderCanvas();
      });
    };

    const cePagineConFoto = libro.pagine.some(function (p) { return p.foto.length > 0; });
    if (cePagineConFoto) {
      conferma('Cambiare il formato può modificare l\'inquadratura delle foto già posizionate. Continuare?', 'Cambia formato', 'var(--gold)')
        .then(function (ok) { if (ok) applica(); });
    } else {
      applica();
    }
  });

  renderFormatoCorrente();

  // ---- Pannello Strumenti: formattazione testo, box liberi, foto ---------
  //
  // Tre schede sullo stesso modale: "Testo" formatta l'elemento di testo
  // attivo (`elementoTestoAttivo`, impostato cliccando in una didascalia o
  // in un box), "Box di testo" crea/elenca i box liberi della pagina,
  // "Foto" regola/vira/incornicia la foto attiva (`fotoStrumentiAttiva`,
  // impostata dal bottone 🎨 sul riquadro). Ogni campo si salva da solo
  // (PUT .../stile) non appena cambia — niente bottone "salva" per il
  // pannello.

  let strumentiTabAttiva = 'testo';

  function apriStrumenti(scheda) {
    cambiaSchedaStrumenti(scheda || strumentiTabAttiva);
    modaleStrumentiEl.hidden = false;
  }

  function cambiaSchedaStrumenti(scheda) {
    strumentiTabAttiva = scheda;
    strumentiTabsEl.querySelectorAll('.strumenti-tab').forEach(function (btn) {
      btn.classList.toggle('attivo', btn.dataset.tab === scheda);
    });
    renderPannelloStrumenti();
  }

  function renderPannelloStrumenti() {
    if (strumentiTabAttiva === 'box') { renderPannelloBox(); return; }
    if (strumentiTabAttiva === 'foto') { renderPannelloFoto(); return; }
    renderPannelloTesto();
  }

  btnStrumentiEl.addEventListener('click', function () { apriStrumenti(); });
  document.getElementById('chiudi-modale-strumenti').addEventListener('click', function () { modaleStrumentiEl.hidden = true; });
  strumentiTabsEl.addEventListener('click', function (e) {
    const tab = e.target.closest('.strumenti-tab');
    if (tab) cambiaSchedaStrumenti(tab.dataset.tab);
  });

  // Pannello flottante, non un modale bloccante: si trascina dalla testata
  // per spostarlo dove serve e vedere intanto la foto/il box che si sta
  // regolando (richiesta esplicita — prima copriva sempre il centro dello
  // schermo, sopra la pagina su cui si stava lavorando).
  (function () {
    const trascina = document.getElementById('strumenti-trascina');
    let startX = 0, startY = 0, startLeft = 0, startTop = 0, attivo = false;

    trascina.addEventListener('pointerdown', function (e) {
      if (e.target.closest('.modale-chiudi')) return;
      const rect = modaleStrumentiEl.getBoundingClientRect();
      // Passa da "ancorato in alto a destra" (CSS) a coordinate assolute:
      // da qui in poi la posizione la decide solo il trascinamento.
      modaleStrumentiEl.style.left = rect.left + 'px';
      modaleStrumentiEl.style.top = rect.top + 'px';
      modaleStrumentiEl.style.right = 'auto';
      startX = e.clientX; startY = e.clientY;
      startLeft = rect.left; startTop = rect.top;
      attivo = true;
      trascina.classList.add('trascinando');
      trascina.setPointerCapture(e.pointerId);
    });
    trascina.addEventListener('pointermove', function (e) {
      if (!attivo) return;
      const larghezza = modaleStrumentiEl.offsetWidth, altezza = modaleStrumentiEl.offsetHeight;
      const nuovoLeft = Math.max(4, Math.min(window.innerWidth - larghezza - 4, startLeft + (e.clientX - startX)));
      const nuovoTop = Math.max(4, Math.min(window.innerHeight - 40, startTop + (e.clientY - startY)));
      modaleStrumentiEl.style.left = nuovoLeft + 'px';
      modaleStrumentiEl.style.top = nuovoTop + 'px';
      // Vincolare anche l'altezza massima alla nuova posizione: altrimenti
      // trascinato in basso il pannello potrebbe uscire dalla finestra.
      modaleStrumentiEl.style.maxHeight = (window.innerHeight - nuovoTop - 16) + 'px';
    });
    function fine() { attivo = false; trascina.classList.remove('trascinando'); }
    trascina.addEventListener('pointerup', fine);
    trascina.addEventListener('pointercancel', fine);
  })();

  /** L'oggetto (foto o box) a cui si riferisce `elementoTestoAttivo`, o null. */
  function elementoTestoOggetto() {
    if (!elementoTestoAttivo) return null;
    return elementoTestoAttivo.tipo === 'didascalia' ? trovaFotoPerId(elementoTestoAttivo.id) : trovaTesto(elementoTestoAttivo.id);
  }

  function elementoTestoDomEls(elemento) {
    if (!elemento) return [];
    const sel = elemento.tipo === 'didascalia'
      ? '.slot-didascalia[data-foto="' + elemento.id + '"]'
      : '.testo-box-contenuto[data-testo="' + elemento.id + '"]';
    return Array.from(canvasEl.querySelectorAll(sel));
  }

  // Un solo debounce per (url, campo): così due campi cambiati in rapida
  // successione (es. uno slider e poi un bottone) si salvano entrambi,
  // invece che l'uno annullare il timer dell'altro.
  const stileSalvaTimeouts = {};

  function persistiStile(url, campo, valore) {
    const chiave = url + ':' + campo;
    clearTimeout(stileSalvaTimeouts[chiave]);
    stileSalvaTimeouts[chiave] = setTimeout(function () {
      const body = {};
      body[campo] = valore;
      fetch(url, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
        .then(function (r) { return r.json(); }).then(function (res) {
          if (!res.success) mostraErrore(res.error || 'Modifica non salvata.');
        });
    }, 350);
  }

  /** Cambia un campo dello stile dell'elemento di testo attivo: DOM subito, salvataggio con un piccolo ritardo. */
  function impostaStileTesto(campo, valore) {
    const elemento = elementoTestoAttivo;
    const oggetto = elementoTestoOggetto();
    if (!elemento || !oggetto) return;
    oggetto.stile[campo] = valore;
    elementoTestoDomEls(elemento).forEach(function (el) { el.setAttribute('style', stileTestoCss(oggetto.stile)); });
    if (elemento.tipo === 'box' && (campo === 'sfondo_colore' || campo === 'sfondo_opacita')) {
      const box = canvasEl.querySelector('.testo-box[data-testo="' + elemento.id + '"]');
      if (box) box.style.background = hexToRgba(oggetto.stile.sfondo_colore, oggetto.stile.sfondo_opacita);
    }
    const url = elemento.tipo === 'didascalia'
      ? '/admin/api/videobook/foto/' + elemento.id + '/stile'
      : '/admin/api/videobook/testi/' + elemento.id + '/stile';
    persistiStile(url, campo, valore);
  }

  /** Cambia un campo dello stile della foto attiva (Strumenti → Foto): stessa logica di impostaStileTesto(). */
  function impostaStileFoto(campo, valore) {
    const foto = trovaFotoPerId(fotoStrumentiAttiva);
    if (!foto) return;
    foto.stile[campo] = valore;
    canvasEl.querySelectorAll('.slot-foto[data-foto="' + foto.id + '"]').forEach(function (slotEl) {
      slotEl.setAttribute('style', bordoFotoCss(foto.stile));
      const img = slotEl.querySelector('.slot-foto-img');
      if (!img) return;
      img.style.filter = filtroFotoCss(foto.stile);
      // Il bordino cambia la cornice utile (box-sizing:border-box): il
      // ritaglio/posizione vanno ricalcolati sulla nuova dimensione, non
      // solo alla prossima resize della finestra.
      if (campo === 'bordo') applicaTrasformazioneFoto(slotEl, img, foto);
    });
    persistiStile('/admin/api/videobook/foto/' + foto.id + '/stile', campo, valore);
  }

  function renderPannelloTesto() {
    const elemento = elementoTestoAttivo;
    const oggetto = elementoTestoOggetto();
    if (!elemento || !oggetto) {
      strumentiPannelloEl.innerHTML = '<p class="strumenti-hint">Seleziona una didascalia o un box di testo nella pagina per formattarlo.</p>';
      return;
    }
    const st = oggetto.stile;
    const fontOptions = strumenti.font.map(function (f) {
      return '<option value="' + escHtml(f) + '"' + (f === st.font ? ' selected' : '') + '>' + escHtml(f) + '</option>';
    }).join('');

    strumentiPannelloEl.innerHTML =
      '<div class="strumenti-campo"><label>Font</label><select data-campo-font>' + fontOptions + '</select></div>' +
      '<div class="strumenti-campo"><label>Dimensione</label><div class="strumenti-slider">' +
        '<input type="range" min="50" max="220" step="5" value="' + st.dimensione + '" data-campo-slider="dimensione">' +
        '<output>' + st.dimensione + '%</output>' +
      '</div></div>' +
      '<div class="strumenti-campo"><label>Allineamento e stile</label><div class="strumenti-riga">' +
        '<div class="strumenti-toggle">' +
          '<button type="button" data-allineamento="left" class="' + (st.allineamento === 'left' ? 'attivo' : '') + '" title="Sinistra">⟸</button>' +
          '<button type="button" data-allineamento="center" class="' + (st.allineamento === 'center' ? 'attivo' : '') + '" title="Centro">≡</button>' +
          '<button type="button" data-allineamento="right" class="' + (st.allineamento === 'right' ? 'attivo' : '') + '" title="Destra">⟹</button>' +
        '</div>' +
        '<div class="strumenti-toggle">' +
          '<button type="button" data-toggle-stile="grassetto" class="' + (st.grassetto ? 'attivo' : '') + '" style="font-weight:700" title="Grassetto">B</button>' +
          '<button type="button" data-toggle-stile="sottolineato" data-sottolineato class="' + (st.sottolineato ? 'attivo' : '') + '" title="Sottolineato">U</button>' +
          '<button type="button" data-toggle-stile="corsivo" data-corsivo class="' + (st.corsivo ? 'attivo' : '') + '" title="Corsivo">I</button>' +
        '</div>' +
        '<input type="color" class="strumenti-colore" value="' + st.colore + '" data-campo-colore="colore" title="Colore testo">' +
      '</div></div>' +
      (elemento.tipo === 'box'
        ? '<hr class="strumenti-separatore">' +
          '<div class="strumenti-campo"><label>Sfondo del box (trasparenza)</label><div class="strumenti-riga">' +
            '<input type="color" class="strumenti-colore" value="' + st.sfondo_colore + '" data-campo-colore="sfondo_colore" title="Colore sfondo">' +
            '<div class="strumenti-slider"><input type="range" min="0" max="100" step="5" value="' + st.sfondo_opacita + '" data-campo-slider="sfondo_opacita"><output>' + st.sfondo_opacita + '%</output></div>' +
          '</div></div>' +
          '<button type="button" class="strumenti-elimina-box" data-elimina-box="' + elemento.id + '">🗑 Elimina questo box</button>'
        : '');
  }

  function renderPannelloBox() {
    const pagina = paginaAttiva();
    if (!pagina) { strumentiPannelloEl.innerHTML = '<p class="strumenti-hint">Seleziona prima una pagina.</p>'; return; }
    const lista = pagina.testi || [];
    const fotoRiferimento = fotoRiferimentoBox();
    const etichettaAggiungi = fotoRiferimento
      ? '+ Aggiungi box sulla foto ' + fotoRiferimento.slot
      : '+ Aggiungi box di testo';

    strumentiPannelloEl.innerHTML =
      '<button type="button" class="nav-btn btn-gold" style="width:100%;justify-content:center;margin-bottom:.4rem" id="btn-aggiungi-box">' + etichettaAggiungi + '</button>' +
      '<p class="strumenti-hint" style="padding:0 0 1rem;text-align:left">' +
        (fotoRiferimento
          ? 'Resterà sempre dentro i confini di quella foto, anche ridimensionandolo.'
          : 'Questa pagina non ha ancora nessuna foto: il box nasce libero su tutta la pagina.') +
      '</p>' +
      (lista.length
        ? '<div class="strumenti-campo"><label>Box in questa pagina</label>' +
          lista.map(function (t, i) {
            const etichetta = (t.testo ? escHtml(t.testo.slice(0, 22)) : 'Box ' + (i + 1) + ' (vuoto)') +
              (t.slot != null ? ' · foto ' + t.slot : '');
            return '<div class="strumenti-riga" style="margin-bottom:.5rem">' +
              '<span style="flex:1;font-size:.8rem;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + etichetta + '</span>' +
              '<button type="button" class="filtro-chip" data-seleziona-box="' + t.id + '">Formatta</button>' +
            '</div>';
          }).join('') +
          '</div>'
        : '<p class="strumenti-hint">Nessun box di testo in questa pagina.</p>');
  }

  /**
   * A quale foto si aggancia il prossimo box creato: quella scelta col
   * bottone 🎨 se c'è, altrimenti la prima foto già caricata nella pagina —
   * "deve stare sempre nell'area della foto" vale anche senza una scelta
   * esplicita, non solo quando l'utente ha selezionato una foto apposta.
   * Un box libero su tutta la pagina resta possibile solo se la pagina non
   * ha ancora nessuna foto (non c'è nulla a cui agganciarlo).
   */
  function fotoRiferimentoBox() {
    const pagina = paginaAttiva();
    return trovaFotoPerId(fotoStrumentiAttiva) || (pagina && pagina.foto.length ? pagina.foto[0] : null);
  }

  function aggiungiBoxTesto() {
    const pagina = paginaAttiva();
    if (!pagina) return;
    const fotoRiferimento = fotoRiferimentoBox();
    const body = fotoRiferimento ? { slot: fotoRiferimento.slot } : {};

    fetch('/admin/api/videobook/pagine/' + pagina.id + '/testi', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(function (r) { return r.json(); }).then(function (res) {
        if (!res.success) { mostraErrore(res.error || 'Impossibile aggiungere il box.'); return; }
        pagina.testi = pagina.testi || [];
        pagina.testi.push(res.testo);
        elementoTestoAttivo = { tipo: 'box', id: res.testo.id };
        renderCanvas();
        cambiaSchedaStrumenti('testo');
      });
  }

  const VIRAGGIO_ETICHETTE = { seppia: 'Seppia', bn: 'Bianco e nero', vintage: 'Vintage', freddo: 'Freddo' };
  const BORDO_ETICHETTE = { 'bianco-sottile': 'Bianco sottile', 'oro-sottile': 'Oro sottile', 'nero-sottile': 'Nero sottile', 'bianco-spesso': 'Bianco spesso' };

  function renderPannelloFoto() {
    const foto = trovaFotoPerId(fotoStrumentiAttiva);
    if (!foto) {
      strumentiPannelloEl.innerHTML = '<p class="strumenti-hint">Seleziona una foto nella pagina (bottone 🎨 sul riquadro) per regolarla.</p>';
      return;
    }
    const st = foto.stile;

    function bottonePreset(chiave, valore, etichetta) {
      const attivo = (st[chiave] || null) === valore ? ' attivo' : '';
      return '<button type="button" class="' + attivo + '" data-' + chiave + '="' + (valore || '') + '">' + etichetta + '</button>';
    }

    strumentiPannelloEl.innerHTML =
      '<div class="strumenti-campo"><label>Luminosità</label><div class="strumenti-slider"><input type="range" min="50" max="150" step="5" value="' + st.luminosita + '" data-campo-slider="luminosita"><output>' + st.luminosita + '%</output></div></div>' +
      '<div class="strumenti-campo"><label>Contrasto</label><div class="strumenti-slider"><input type="range" min="50" max="150" step="5" value="' + st.contrasto + '" data-campo-slider="contrasto"><output>' + st.contrasto + '%</output></div></div>' +
      '<div class="strumenti-campo"><label>Saturazione</label><div class="strumenti-slider"><input type="range" min="0" max="200" step="5" value="' + st.saturazione + '" data-campo-slider="saturazione"><output>' + st.saturazione + '%</output></div></div>' +
      '<div class="strumenti-campo"><label>Viraggio</label><div class="strumenti-preset">' +
        bottonePreset('viraggio', null, 'Nessuno') +
        strumenti.viraggi.map(function (v) { return bottonePreset('viraggio', v, VIRAGGIO_ETICHETTE[v] || v); }).join('') +
      '</div></div>' +
      '<div class="strumenti-campo"><label>Bordino</label><div class="strumenti-preset">' +
        bottonePreset('bordo', null, 'Nessuno') +
        strumenti.bordi.map(function (b) { return bottonePreset('bordo', b, BORDO_ETICHETTE[b] || b); }).join('') +
      '</div></div>';
  }

  strumentiPannelloEl.addEventListener('click', function (e) {
    const align = e.target.closest('[data-allineamento]');
    if (align) { impostaStileTesto('allineamento', align.dataset.allineamento); renderPannelloTesto(); return; }

    const toggle = e.target.closest('[data-toggle-stile]');
    if (toggle) {
      const oggetto = elementoTestoOggetto();
      if (!oggetto) return;
      const campo = toggle.dataset.toggleStile;
      impostaStileTesto(campo, !oggetto.stile[campo]);
      renderPannelloTesto();
      return;
    }

    const bordo = e.target.closest('[data-bordo]');
    if (bordo) { impostaStileFoto('bordo', bordo.dataset.bordo || null); renderPannelloFoto(); return; }

    const viraggio = e.target.closest('[data-viraggio]');
    if (viraggio) { impostaStileFoto('viraggio', viraggio.dataset.viraggio || null); renderPannelloFoto(); return; }

    if (e.target.closest('#btn-aggiungi-box')) { aggiungiBoxTesto(); return; }

    const selBox = e.target.closest('[data-seleziona-box]');
    if (selBox) { elementoTestoAttivo = { tipo: 'box', id: Number(selBox.dataset.selezionaBox) }; cambiaSchedaStrumenti('testo'); return; }

    const delBox = e.target.closest('[data-elimina-box]');
    if (delBox) { eliminaBoxTesto(Number(delBox.dataset.eliminaBox)); modaleStrumentiEl.hidden = true; }
  });

  strumentiPannelloEl.addEventListener('input', function (e) {
    const el = e.target;
    if (el.matches('[data-campo-slider]')) {
      const campo = el.dataset.campoSlider;
      const valore = Number(el.value);
      const output = el.parentElement.querySelector('output');
      if (output) output.textContent = valore + '%';
      if (strumentiTabAttiva === 'foto') impostaStileFoto(campo, valore); else impostaStileTesto(campo, valore);
      return;
    }
    if (el.matches('[data-campo-colore]')) impostaStileTesto(el.dataset.campoColore, el.value);
  });

  strumentiPannelloEl.addEventListener('change', function (e) {
    if (e.target.matches('[data-campo-font]')) impostaStileTesto('font', e.target.value);
  });

  // ---- Genera video e PDF (solo le pagine popolate) -----------------------
  //
  // Un solo bottone, due output indipendenti: il video (server, coda ffmpeg,
  // con polling) e il PDF pronto stampa per il Fotoalbum VideoBook (browser,
  // canvas + jsPDF, come Ricordino Designer — nessun server coinvolto nel
  // disegno, solo nel salvataggio finale). Girano in parallelo, ciascuno con
  // il proprio stato: uno può essere pronto mentre l'altro è ancora in corso.

  let pollingVideoId = null;
  let pdfUrl = libro.pdf_url || null;
  let pdfStato = null; // null | 'generazione' | 'pronto' | 'errore'
  let pdfErrore = null;

  function aggiornaBottoneGenera() {
    const inCorso = (video && video.in_corso) || pdfStato === 'generazione';
    btnGeneraVideoEl.disabled = inCorso;
    btnGeneraVideoEl.textContent = (video || pdfUrl) ? 'Rigenera video e PDF' : 'Genera video e PDF';
  }

  function renderVideoStato() {
    aggiornaBottoneGenera();
    if (!video) { videoStatoEl.hidden = true; return; }

    if (video.in_corso) {
      videoStatoEl.hidden = false;
      videoStatoEl.innerHTML = '<span class="video-spinner"></span><span>Video in elaborazione…</span>';
      return;
    }

    if (video.stato === 'pronto') {
      videoStatoEl.hidden = false;
      videoStatoEl.innerHTML = '<span class="video-badge pronto">Video pronto</span>' +
        '<a href="' + video.url + '" target="_blank" rel="noopener" style="color:#fff">Guarda</a>' +
        (video.download_url
          ? '<a href="' + video.download_url + '" style="color:#fff">Scarica</a>'
          : '<span style="color:#fffb;font-size:12px">Scaricabile dopo il pagamento</span>');
      return;
    }

    if (video.stato === 'errore') {
      videoStatoEl.hidden = false;
      videoStatoEl.innerHTML = '<span class="video-badge errore">Video: errore</span><span>' + escHtml(video.messaggio_errore || 'Riprova.') + '</span>';
      return;
    }

    videoStatoEl.hidden = true;
  }

  function renderPdfStato() {
    aggiornaBottoneGenera();
    if (pdfStato === 'generazione') {
      pdfStatoEl.hidden = false;
      pdfStatoEl.innerHTML = '<span class="video-spinner"></span><span>PDF in preparazione…</span>';
      return;
    }
    if (pdfStato === 'errore') {
      pdfStatoEl.hidden = false;
      pdfStatoEl.innerHTML = '<span class="video-badge errore">PDF: errore</span><span>' + escHtml(pdfErrore || 'Riprova.') + '</span>';
      return;
    }
    if (pdfUrl) {
      pdfStatoEl.hidden = false;
      // Il link resta lo stesso prima e dopo il pagamento (il file va
      // comunque conservato per lo staff, vedi PdfController): solo
      // l'etichetta cambia, "Scarica" implica che è definitivo.
      pdfStatoEl.innerHTML = '<span class="video-badge pronto">PDF pronto</span>' +
        (libro.scaricabile
          ? '<a href="' + pdfUrl + '" target="_blank" rel="noopener" style="color:#fff">Scarica il PDF</a>'
          : '<a href="' + pdfUrl + '" target="_blank" rel="noopener" style="color:#fff">Anteprima PDF</a>' +
            '<span style="color:#fffb;font-size:12px">Scaricabile dopo il pagamento</span>');
      return;
    }
    pdfStatoEl.hidden = true;
  }

  function avviaPollingVideo() {
    if (pollingVideoId) return;
    pollingVideoId = setInterval(function () {
      fetch('/admin/api/videobook/' + libro.id + '/video')
        .then(function (r) { return r.json(); })
        .then(function (res) {
          video = res.video;
          renderVideoStato();
          if (!video || !video.in_corso) { clearInterval(pollingVideoId); pollingVideoId = null; }
        })
        .catch(function () { /* riprova al prossimo giro */ });
    }, 4000);
  }

  // ---- PDF: canvas per pagina (cover crop, come a schermo) + jsPDF --------

  let jsPdfCaricato = null; // promessa condivisa, la libreria si scarica una sola volta

  function caricaJsPdf() {
    if (window.jspdf) return Promise.resolve();
    if (jsPdfCaricato) return jsPdfCaricato;
    jsPdfCaricato = new Promise(function (resolve, reject) {
      const script = document.createElement('script');
      script.src = '/vendor/libs/jspdf.umd.min.js';
      script.onload = resolve;
      script.onerror = function () { reject(new Error('Libreria PDF non disponibile.')); };
      document.head.appendChild(script);
    });
    return jsPdfCaricato;
  }

  function caricaImmagine(url) {
    return new Promise(function (resolve, reject) {
      const img = new Image();
      img.onload = function () { resolve(img); };
      img.onerror = function () { reject(new Error('Foto non caricabile: ' + url)); };
      img.src = url;
    });
  }

  const DPI_STAMPA = 200;

  // Stessa idea del bordino a schermo (BORDI_CSS) ma in mm, non px: una
  // misura fisica ha senso stampata, uno spessore in pixel schermo no —
  // vedi disegnaCover().
  const BORDI_MM = {
    'bianco-sottile': { mm: 1.5, colore: '#ffffff' },
    'oro-sottile':    { mm: 1.2, colore: '#c8a96e' },
    'nero-sottile':   { mm: 1.2, colore: '#1a1a2e' },
    'bianco-spesso':  { mm: 4,   colore: '#ffffff' },
  };

  /**
   * Stesso ritaglio impostato a schermo (drag + maniglia, vedi
   * applicaTrasformazioneFoto — geometriaFoto/offsetAsse condivise): non più
   * sempre il centro, ma la porzione scelta dall'utente — scala 1 = "cover"
   * minimo, <1 rimpicciolita e centrata, pos_x/pos_y (0..1) la posizione nel
   * margine di scorrimento residuo quando la foto eccede la cornice. Più
   * regolazione/viraggio (ctx.filter, stessa combinazione di filtroFotoCss)
   * e bordino (stroke), entrambi dal pannello Strumenti.
   */
  function disegnaCover(ctx, img, dx, dy, dw, dh, foto) {
    const stile = (foto && foto.stile) || {};
    const scala = (foto && foto.scala) || 1;
    const posX = foto && foto.pos_x != null ? foto.pos_x : 0.5;
    const posY = foto && foto.pos_y != null ? foto.pos_y : 0.5;

    const geo = geometriaFoto(dw, dh, img.naturalWidth, img.naturalHeight, scala);
    const offX = offsetAsse(geo.slackX, posX);
    const offY = offsetAsse(geo.slackY, posY);

    ctx.save();
    ctx.beginPath();
    ctx.rect(dx, dy, dw, dh);
    ctx.clip();
    ctx.filter = filtroFotoCss(stile);
    ctx.drawImage(img, dx + offX, dy + offY, geo.dispW, geo.dispH);
    ctx.restore();

    const bordo = stile.bordo && BORDI_MM[stile.bordo];
    if (bordo) {
      const spessore = bordo.mm / 25.4 * DPI_STAMPA;
      ctx.save();
      ctx.strokeStyle = bordo.colore;
      ctx.lineWidth = spessore;
      ctx.strokeRect(dx + spessore / 2, dy + spessore / 2, dw - spessore, dh - spessore);
      ctx.restore();
    }
  }

  /** { size (px), css } del font per un blocco di testo in stampa, alla stessa proporzione di pagina usata a schermo. */
  function fontCanvasStile(stile, wPxRiferimento) {
    const size = Math.max(6, Math.round(wPxRiferimento * 0.017 * (stile.dimensione / 100)));
    return { size: size, css: (stile.corsivo ? 'italic ' : '') + (stile.grassetto ? '700 ' : '400 ') + size + 'px "' + stile.font + '"' };
  }

  /** Spezza `testo` su più righe che stanno entro `maxW` (canvas 2D non lo fa da solo). */
  function spezzaRighe(ctx, testo, maxW) {
    const parole = testo.split(/\s+/);
    const righe = [];
    let corrente = '';
    parole.forEach(function (parola) {
      const prova = corrente ? corrente + ' ' + parola : parola;
      if (corrente && ctx.measureText(prova).width > maxW) {
        righe.push(corrente);
        corrente = parola;
      } else {
        corrente = prova;
      }
    });
    if (corrente) righe.push(corrente);
    return righe;
  }

  /**
   * Disegna un testo (didascalia o box) dentro un rettangolo con lo stesso
   * stile scelto nel pannello Strumenti — font/dimensione/allineamento/
   * grassetto/corsivo/sottolineato/colore, stessa combinazione di
   * stileTestoCss() ma per canvas 2D (niente text-decoration: il
   * sottolineato è una riga disegnata a mano).
   */
  function disegnaTestoStile(ctx, testo, x, y, w, h, stile, wPxRiferimento, aCapo) {
    if (!testo) return;
    const font = fontCanvasStile(stile, wPxRiferimento);
    ctx.font = font.css;
    ctx.fillStyle = stile.colore;
    ctx.textBaseline = 'top';
    ctx.textAlign = stile.allineamento === 'left' ? 'left' : stile.allineamento === 'right' ? 'right' : 'center';

    const rigaAltezza = font.size * 1.25;
    const righe = aCapo ? spezzaRighe(ctx, testo, w) : [testo];
    const ax = ctx.textAlign === 'left' ? x : ctx.textAlign === 'right' ? x + w : x + w / 2;
    let cy = y + Math.max(0, (h - righe.length * rigaAltezza) / 2);

    righe.forEach(function (riga) {
      ctx.fillText(riga, ax, cy, w);
      if (stile.sottolineato) {
        const larghezza = Math.min(w, ctx.measureText(riga).width);
        const lx = ctx.textAlign === 'left' ? ax : ctx.textAlign === 'right' ? ax - larghezza : ax - larghezza / 2;
        ctx.fillRect(lx, cy + font.size * 1.05, larghezza, Math.max(1, Math.round(font.size * 0.06)));
      }
      cy += rigaAltezza;
    });
  }

  /** Il canvas di UNA pagina, alla risoluzione di stampa: foto (cover) + didascalia, stessa geometria dei riquadri a schermo. */
  async function disegnaPaginaCanvas(pagina, larghezzaMm, altezzaMm) {
    const wPx = Math.round(larghezzaMm / 25.4 * DPI_STAMPA);
    const hPx = Math.round(altezzaMm / 25.4 * DPI_STAMPA);

    const canvas = document.createElement('canvas');
    canvas.width = wPx;
    canvas.height = hPx;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, wPx, hPx);

    const fotoPerSlot = {};
    pagina.foto.forEach(function (f) { fotoPerSlot[f.slot] = f; });

    for (const s of pagina.template.slots) {
      const foto = fotoPerSlot[s.ordine];
      if (!foto) continue;

      const dx = s.x * wPx, dy = s.y * hPx, dw = s.w * wPx, dh = s.h * hPx;
      const img = await caricaImmagine(foto.url);
      disegnaCover(ctx, img, dx, dy, dw, dh, foto);

      if (foto.didascalia) {
        const capH = Math.max(0, Math.min(0.08, 0.99 - (s.y + s.h))) * hPx;
        if (capH > wPx * 0.01) {
          disegnaTestoStile(ctx, foto.didascalia, dx, dy + dh + capH * 0.15, dw, capH, foto.stile || {}, wPx, false);
        }
      }
    }

    // Box di testo (pannello Strumenti → Box di testo): sfondo semi-
    // trasparente + testo, disegnati sopra le foto — stesso ordine dello
    // schermo (dentro .slot-foto/.pagina-foglio in slotBoxes()/testiBoxes()).
    // Un box agganciato a uno slot ha x/y/w/h relative allo SLOT, non alla
    // pagina (vedi TestoPagina): si convertono in assolute prima di
    // disegnare, e si clippa al rettangolo dello slot come lo schermo
    // (.slot-foto{overflow:hidden}) — stessa garanzia anche in stampa.
    (pagina.testi || []).forEach(function (t) {
      let relX = t.x, relY = t.y, relW = t.w, relH = t.h;
      let clip = null;

      if (t.slot != null && pagina.template) {
        const s = pagina.template.slots.find(function (sl) { return sl.ordine === t.slot; });
        if (s) {
          relX = s.x + t.x * s.w;
          relY = s.y + t.y * s.h;
          relW = t.w * s.w;
          relH = t.h * s.h;
          clip = { x: s.x * wPx, y: s.y * hPx, w: s.w * wPx, h: s.h * hPx };
        }
      }

      const bx = relX * wPx, by = relY * hPx, bw = relW * wPx, bh = relH * hPx;

      ctx.save();
      if (clip) { ctx.beginPath(); ctx.rect(clip.x, clip.y, clip.w, clip.h); ctx.clip(); }

      ctx.fillStyle = hexToRgba(t.stile.sfondo_colore, t.stile.sfondo_opacita);
      ctx.fillRect(bx, by, bw, bh);
      if (t.testo) {
        const padding = wPx * 0.012;
        disegnaTestoStile(ctx, t.testo, bx + padding, by + padding, bw - padding * 2, bh - padding * 2, t.stile, wPx, true);
      }
      ctx.restore();
    });

    return canvas.toDataURL('image/jpeg', 0.92);
  }

  /** Costruisce il PDF di tutte le pagine popolate e lo carica sul server. */
  async function generaESalvaPdf() {
    const popolate = libro.pagine.filter(function (p) { return p.foto.length > 0 && p.template; })
      .slice().sort(function (a, b) { return a.ordine - b.ordine; });
    if (!popolate.length) return;

    pdfStato = 'generazione';
    pdfErrore = null;
    renderPdfStato();

    try {
      await document.fonts.ready; // la didascalia usa il font serif già caricato dalla pagina
      const { w, h } = formatoLibroMm();

      const immagini = [];
      for (const pagina of popolate) {
        immagini.push(await disegnaPaginaCanvas(pagina, w, h));
      }

      await caricaJsPdf();
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({ orientation: w >= h ? 'landscape' : 'portrait', unit: 'mm', format: [w, h] });

      immagini.forEach(function (dataUrl, i) {
        if (i > 0) doc.addPage([w, h], w >= h ? 'landscape' : 'portrait');
        doc.addImage(dataUrl, 'JPEG', 0, 0, w, h);
      });

      const blob = doc.output('blob');
      const fd = new FormData();
      fd.append('pdf', blob, 'videobook-' + libro.id + '.pdf');

      const risposta = await fetch('/admin/api/videobook/' + libro.id + '/pdf', { method: 'POST', body: fd });
      const res = await risposta.json();
      if (!res.success) throw new Error(res.error || 'Salvataggio PDF non riuscito.');

      pdfUrl = res.url;
      pdfStato = 'pronto';
    } catch (e) {
      pdfStato = 'errore';
      pdfErrore = e.message;
    }
    renderPdfStato();
  }

  btnGeneraVideoEl.addEventListener('click', function () {
    const paginePopolate = libro.pagine.filter(function (p) { return p.foto.length > 0; }).length;
    if (!paginePopolate) { mostraErrore('Carica almeno una foto in una pagina prima di generare.'); return; }

    conferma(
      'Generare il video e il PDF stampa con le ' + paginePopolate + ' pagine popolate? Le pagine senza foto verranno escluse.',
      'Genera', 'var(--gold)'
    ).then(function (ok) {
      if (!ok) return;

      fetch('/admin/api/videobook/' + libro.id + '/video', { method: 'POST' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) { mostraErrore(res.error || 'Impossibile avviare il render del video.'); return; }
          video = res.video;
          renderVideoStato();
          avviaPollingVideo();
        })
        .catch(function () { mostraErrore('Impossibile avviare il render del video: riprova.'); });

      // Indipendente dal video: se fallisce solo lui, il video prosegue lo stesso.
      generaESalvaPdf();
    });
  });

  renderVideoStato();
  renderPdfStato();
  if (video && video.in_corso) avviaPollingVideo();

  // ---- Passo iniziale: "da quante pagine si compone?" --------------------

  document.getElementById('salta-quante-pagine').addEventListener('click', function () { modaleQuantePagineEl.hidden = true; });

  document.getElementById('conferma-quante-pagine').addEventListener('click', function () {
    const numero = Math.max(1, Math.min(50, Number(inputNumeroPagineEl.value) || 1));
    modaleQuantePagineEl.hidden = true;
    inizializzaPagine(numero);
  });

  function inizializzaPagine(numero) {
    fetch('/admin/api/videobook/' + libro.id + '/pagine/inizializza', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ numero_pagine: numero }),
    }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res.success) { mostraErrore(res.error || 'Impossibile creare le pagine.'); return; }
      libro.pagine = res.pagine;
      paginaAttivaId = libro.pagine.length ? libro.pagine[0].id : null;
      renderSidebar(); renderCanvas();
    });
  }

  // Si propone ogni volta che il libro è senza pagine (prima apertura, o
  // dopo averle eliminate tutte): chi non vuole rispondere può chiudere il
  // modale e usare "+ Nuova pagina" una alla volta come prima.
  if (!libro.pagine.length) modaleQuantePagineEl.hidden = false;

  renderSidebar();
  renderCanvas();
})();
</script>
</body>
</html>
