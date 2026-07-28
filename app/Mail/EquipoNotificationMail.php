<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EquipoNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Cantidad de intentos antes de marcar el job como fallido definitivamente.
     */
    public int $tries = 3;

    /**
     * Segundos de espera entre reintentos si el envío falla (p.ej. SMTP caído).
     */
    public int $backoff = 30;

    /**
     * Nota: $equipo se recibe como un snapshot (stdClass) de los datos del equipo,
     * no como el modelo Eloquent vivo. Esto es necesario porque el correo se envía
     * de forma asíncrona (cola): si se pasara el modelo Eloquent, Laravel intentaría
     * releerlo desde la base de datos al procesar el job, y para la acción "eliminado"
     * el registro ya no existiría (ModelNotFoundException).
     *
     * @param array<int, array{label: string, before: string, after: string}> $cambios
     */
    public function __construct(
        public object $equipo,
        public string $accion,
        public array $cambios = []
    ) {
    }

    public function envelope(): Envelope
    {
        $asuntos = [
            'creado' => 'Notificación de nuevo equipo creado',
            'actualizado' => 'Notificación de equipo editado',
            'reasignado' => 'Notificación de equipo reasignado',
            'eliminado' => 'Notificación de equipo eliminado',
        ];

        $asunto = $asuntos[$this->accion] ?? 'Actualización de equipo';

        return new Envelope(
            subject: "{$asunto}: {$this->equipo->codigo_inventario}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.equipo-notification',
        );
    }
}
