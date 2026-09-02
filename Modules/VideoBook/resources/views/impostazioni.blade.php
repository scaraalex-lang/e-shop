<!DOCTYPE html>
<html lang="it" translate="no">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Impostazioni Video Book | MemorAI</title>
<link href="/vendor/fonts/editor-fonts.css" rel="stylesheet">
<style>
:root{--ink:#1a1a2e;--gold:#c2a35a;--gold-scuro:#a5863f;--panna:#faf6ec;--panna-scura:#efe6d0;--border:#ddd8d0;--rosso:#c44b3a;--verde:#3f8f5c}
*{box-sizing:border-box}
body{margin:0;font-family:'Jost',sans-serif;background:var(--panna);color:var(--ink);padding:2.5rem 1.5rem;display:flex;justify-content:center}
.pagina{width:100%;max-width:640px}
h1{font-family:'Cormorant Garamond',serif;font-size:1.9rem;font-weight:600;margin:0 0 .3rem}
.sottotitolo{color:var(--gold-scuro);font-size:.9rem;margin:0 0 1.8rem}
.scheda{background:#fff;border:1px solid var(--border);border-radius:10px;padding:1.6rem;margin-bottom:1.2rem}
.scheda h2{font-family:'Cormorant Garamond',serif;font-size:1.25rem;margin:0 0 .3rem}
.scheda p.hint{color:#8a7f72;font-size:.85rem;margin:0 0 1.2rem;line-height:1.5}
.stato-attuale{display:flex;align-items:center;gap:.6rem;background:var(--panna);border:1px solid var(--panna-scura);border-radius:8px;padding:.8rem 1rem;margin-bottom:1.2rem;font-size:.85rem}
.stato-attuale .badge{width:8px;height:8px;border-radius:50%;background:var(--verde);flex-shrink:0}
.stato-attuale.assente .badge{background:var(--rosso)}
.stato-attuale strong{font-weight:600}
form{display:flex;flex-direction:column;gap:.9rem}
input[type=file]{font-family:inherit;font-size:.85rem}
.errore{color:var(--rosso);font-size:.8rem}
.successo{color:var(--verde);font-size:.85rem;margin-bottom:1rem}
button{align-self:flex-start;background:var(--gold);color:#fff;border:none;border-radius:6px;padding:.6rem 1.3rem;font-size:.85rem;font-weight:500;cursor:pointer;font-family:'Jost',sans-serif}
button:hover{background:var(--gold-scuro)}
</style>
</head>
<body>
<div class="pagina">
  <h1>Impostazioni Video Book</h1>
  <p class="sottotitolo">Solo staff</p>

  @if (session('successo'))
    <p class="successo">{{ session('successo') }}</p>
  @endif

  <div class="scheda">
    <h2>Profilo colore di stampa</h2>
    <p class="hint">
      Il profilo ICC del laboratorio di stampa fotografica: allineerà le foto esportate al colore che il laboratorio stamperà davvero.
      Per ora viene solo caricato e conservato — la conversione automatica in fase di export è un passo successivo, non ancora attivo.
    </p>

    @if ($profilo)
      <div class="stato-attuale">
        <span class="badge"></span>
        <span>Profilo attivo: <strong>{{ $profilo->nome_originale }}</strong> — caricato il {{ $profilo->created_at->format('d/m/Y') }}{{ $profilo->caricatoDa ? ' da ' . $profilo->caricatoDa->name : '' }}</span>
      </div>
    @else
      <div class="stato-attuale assente">
        <span class="badge"></span>
        <span>Nessun profilo caricato.</span>
      </div>
    @endif

    <form method="POST" action="{{ route('videobook.impostazioni.profilo-colore') }}" enctype="multipart/form-data">
      @csrf
      <input type="file" name="profilo" accept=".icc,.icm" required>
      @error('profilo')
        <span class="errore">{{ $message }}</span>
      @enderror
      <button type="submit">{{ $profilo ? 'Sostituisci profilo' : 'Carica profilo' }}</button>
    </form>
  </div>
</div>
</body>
</html>
