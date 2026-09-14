<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * Nota: $usuario se recibe como snapshot (stdClass), no como modelo Eloquent vivo,
     * para que el correo de "eliminado" siga teniendo datos aunque el registro ya no exista.
     *
     * @param array<int, array{label: string, before: string, after: string}> $cambios
     */
    public function __construct(
        public object $usuario,
        public string $accion,
        public array $cambios = []
    ) {
    }

    public function envelope(): Envelope
    {
        $asuntos = [
            'actualizado' => 'Tu cuenta ha sido actualizada',
            'eliminado' => 'Tu cuenta ha sido dada de baja',
        ];

        $asunto = $asuntos[$this->accion] ?? 'Actualización de tu cuenta';

        return new Envelope(
            subject: "{$asunto} — " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuario-notification',
        );
    }
}
