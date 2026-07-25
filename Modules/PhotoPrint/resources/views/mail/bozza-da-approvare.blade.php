@component('mail::message')
# Il ricordino è pronto da guardare

@if ($defunto)
Abbiamo preparato il ricordino di **{{ $defunto->nomeCompleto() }}**.
@else
Abbiamo preparato il ricordino.
@endif

Prima di stamparlo vorremmo che lo vedeste voi. Da questa pagina potete
guardarlo con calma e dirci se va bene così, oppure indicarci cosa
preferireste cambiare: una data, una parola, la fotografia.

@component('mail::button', ['url' => $link])
Guarda il ricordino
@endcomponent

Non c'è nessuna fretta, e non serve registrarsi: il collegamento resta
valido finché la lavorazione è aperta. Se preferite scriverci, potete
rispondere direttamente a questo messaggio.

Con rispetto,
{{ $firma }}

@slot('subcopy')
Se il pulsante non funziona, copiate questo indirizzo nel browser:
{{ $link }}
@endslot
@endcomponent
