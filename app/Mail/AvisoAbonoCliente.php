<?php

namespace App\Mail;

use App\Models\Suscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail al cliente: "tu servicio vence pronto" (por_vencer) o "tu servicio venció" (vencido). */
class AvisoAbonoCliente extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Suscripcion $suscripcion,
        public string $tipo,
        public ?int $dias,
        public string $urlRenovar,
    ) {
    }

    public function envelope(): Envelope
    {
        $sistema = $this->suscripcion->clienteSistema->sistema->nombre;

        $asunto = match (true) {
            $this->tipo === 'vencido' => "Tu {$sistema} venció",
            $this->dias === 0 => "Tu {$sistema} vence hoy",
            $this->dias === 1 => "Tu {$sistema} vence mañana",
            default => "Tu {$sistema} vence en {$this->dias} días",
        };

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        $cs = $this->suscripcion->clienteSistema;

        return new Content(view: 'emails.abono-cliente', with: [
            'cliente' => $cs->cliente,
            'sistema' => $cs->sistema,
            'suscripcion' => $this->suscripcion,
            'tipo' => $this->tipo,
            'dias' => $this->dias,
            'urlRenovar' => $this->urlRenovar,
        ]);
    }
}
