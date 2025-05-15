<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\SendGridService;

class VerifyEmailNotification extends Notification
{
    protected $codigo;

    public function __construct($codigo)
    {
        $this->codigo = $codigo;
    }

    public function via($notifiable)
    {
        return ['custom_sendgrid'];
    }
    
    public function toCustomSendgrid($notifiable)
    {
        return [
            'subject' => 'Código de verificación',
            'content' => "Hola {$notifiable->nombre}, tu código de verificación es: {$this->codigo}. Gracias por registrarte en Cero Hambre."
        ];
    }
}