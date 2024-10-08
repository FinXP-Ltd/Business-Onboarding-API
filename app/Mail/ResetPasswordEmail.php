<?php
namespace App\Mail;

use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $url;

    /**
     * Create a new message instance.
     *
     * @param string $token
     * @param string $email
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromAddress = Config::get('mail.from.address');
        $fromName = Config::get('mail.from.name');
        $this->url =config('app.asset_url').'/reset-password/'.$this->token.'/?email='.$this->email;

        return $this->from($fromAddress, $fromName)
            ->subject('Reset Your Password')
            ->markdown('emails.reset-password');
    }
}
