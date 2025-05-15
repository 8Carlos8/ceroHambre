<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;

class SendGridService
{
    protected $sendGrid;

    public function __construct()
    {
        $this->sendGrid = new SendGrid(env('SENDGRID_API_KEY'));
    }

    public function sendEmail($to, $subject, $content)
    {
        $email = new Mail();
        $email->setFrom("support@cerohambre.org", "Cero Hambre");
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/plain", $content);
        $email->addContent("text/html", "<strong>$content</strong>");

        try {
            $response = $this->sendGrid->send($email);
            return [
                'status' => $response->statusCode(),
                'body' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
