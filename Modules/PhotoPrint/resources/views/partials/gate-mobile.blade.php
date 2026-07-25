{{-- Cancello per gli schermi stretti.

     I due editor completi sono strumenti da scrivania: tre colonne fisse, tela
     al centro, pannelli ai lati. Sotto i 900px non si rimpiccioliscono, si
     rompono — la tela finisce fuori schermo. Invece di lasciare che qualcuno ci
     sbatta contro, lo mandiamo al Designer Smart, che quel lavoro lo fa in un
     modo pensato per il telefono. --}}

<style>
#gate-mobile{display:none}
@media (max-width: 899px){
  /* Il gate SOSTITUISCE l'editor, non ci si sovrappone: un "fixed; inset:0"
     qui si dimensionerebbe sulla pagina traboccante (743px di larghezza a
     390px di schermo), non sulla finestra. */
  body > *:not(#gate-mobile){display:none !important}
  html,body{height:auto !important;overflow:auto !important;background:#fdfcfa !important}

  #gate-mobile{
    display:flex;min-height:100dvh;
    flex-direction:column;align-items:center;justify-content:center;gap:1.1rem;
    padding:2rem 1.6rem;text-align:center;
    background:#fdfcfa;color:#3a2e22;
    font-family:'DM Sans',system-ui,sans-serif;font-weight:300;
  }
  #gate-mobile .segno{font-family:'Cormorant Garamond',Georgia,serif;font-size:2.6rem;color:#c2a35a;line-height:1}
  #gate-mobile h2{font-family:'Cormorant Garamond',Georgia,serif;font-weight:500;font-size:1.9rem;line-height:1.15;color:#3a2e22}
  #gate-mobile p{font-size:.95rem;line-height:1.6;color:#6b6152;max-width:26rem}
  #gate-mobile a{
    display:inline-flex;align-items:center;justify-content:center;
    padding:.95rem 1.6rem;text-decoration:none;
    font-size:.75rem;letter-spacing:.2em;text-transform:uppercase;
  }
  #gate-mobile .principale{background:#c2a35a;color:#fdfcfa}
  #gate-mobile .secondario{border:2px solid #3a2e22;color:#3a2e22}
}
</style>

<div id="gate-mobile">
    <span class="segno">✛</span>
    <h2>Questo editor si apre da<br>computer o tablet</h2>
    <p>
        Ha pannelli e strumenti pensati per uno schermo largo. Dal telefono c'è il
        <strong>Designer Smart</strong>: foto, testi e conferma in tre passaggi, con
        l'impaginazione già pronta.
    </p>
    <a class="principale" href="{{ $linkSmart ?? url('/studio/ricordino/smart') }}">Apri il Designer Smart</a>
    <a class="secondario" href="{{ url('/') }}">Torna al sito</a>
</div>

<script>
// Ruotando il telefono si può superare la soglia: l'editor tornerebbe visibile
// con i canvas misurati mentre era nascosto. Meglio ricaricare.
matchMedia('(min-width: 900px)').addEventListener('change', () => location.reload());
</script>
