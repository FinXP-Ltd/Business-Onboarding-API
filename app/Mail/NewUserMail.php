<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $subject = "Welcome to FinXP Business Onboarding platform";

        return $this->from($fromAddress, $fromName)
            ->subject($subject)
            ->markdown('emails.user-invitation', $this->data);
    }
}
