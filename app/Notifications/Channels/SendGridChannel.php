<?php
namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\SendGridService;

class SendGridChannel
{
    protected $sendGrid;

    public function __construct(SendGridService $sendGrid)
    {
        $this->sendGrid = $sendGrid;
    }

    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toCustomSendgrid')) {
            return;
        }

        $message = $notification->toCustomSendgrid($notifiable);

        // Enviar el email usando el servicio
        return $this->sendGrid->sendEmail(
            $notifiable->correo,
            $message['subject'],
            $message['content']
        );
    }
}
