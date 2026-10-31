<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Resumen diario para el dueño del Panel. */
class ResumenAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $resumen)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MiGestión Panel · resumen de cobros del día');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.resumen-admin', with: ['resumen' => $this->resumen]);
    }
}
