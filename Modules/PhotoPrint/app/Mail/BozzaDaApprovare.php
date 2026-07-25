<?php

namespace Modules\PhotoPrint\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Memorial\Models\Ricordino;

/**
 * L'email con cui la famiglia riceve la bozza.
 *
 * Il tono conta più della forma: chi la riceve ha appena perso qualcuno.
 * Niente "gentile cliente", niente urgenza, nessun automatismo che suoni
 * commerciale.
 */
class BozzaDaApprovare extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ricordino $ricordino,
        public string $link,
    ) {}

    public function envelope(): Envelope
    {
        $nome = $this->ricordino->defunto?->nomeCompleto();

        return new Envelope(
            subject: $nome ? "Il ricordino di {$nome}" : 'Il ricordino da approvare',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'photoprint::mail.bozza-da-approvare',
            with: [
                'defunto' => $this->ricordino->defunto,
                'link' => $this->link,
            ],
        );
    }
}
