<?php

namespace Modules\PhotoPrint\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Ricordino;

/**
 * L'email con cui la famiglia riceve la bozza.
 *
 * Il tono conta più della forma: chi la riceve ha appena perso qualcuno.
 * Niente "gentile cliente", niente urgenza, nessun automatismo che suoni
 * commerciale.
 *
 * Parte sempre dal nostro SMTP, ma quando dietro c'è un'agenzia la risposta
 * va a lei: è l'agenzia che sta seguendo quella famiglia, e una risposta che
 * finisse a noi sarebbe persa per entrambe.
 */
class BozzaDaApprovare extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ricordino $ricordino,
        public string $link,
        public ?Agenzia $agenzia = null,
    ) {}

    public function envelope(): Envelope
    {
        $nome = $this->ricordino->defunto?->nomeCompleto();
        $email = $this->agenzia?->emailContatto();

        return new Envelope(
            subject: $nome ? "Il ricordino di {$nome}" : 'Il ricordino da approvare',
            // Mittente nostro, risposta all'agenzia. Il contrario (agenzia nel
            // mittente) farebbe fallire SPF/DMARC sul suo dominio e l'email
            // finirebbe nello spam proprio quando serve che sia letta.
            replyTo: $email ? [new Address($email, $this->agenzia->ragione_sociale)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'photoprint::mail.bozza-da-approvare',
            with: [
                'defunto' => $this->ricordino->defunto,
                'link' => $this->link,
                'firma' => $this->agenzia?->ragione_sociale ?? config('app.name'),
            ],
        );
    }
}
