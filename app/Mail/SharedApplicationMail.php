<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class SharedApplicationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $url;
    public $name;
    public $email;
    public $password;
    public $businessId;
    public $new;

    public $companyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->name = $data['first_name'] . ' ' . $data['last_name'];
        $this->email = $data['email'];

        $this->url = $data['url'];

        $this->companyName = $data['company_name'];
    }

    public function build()
    {
        $fromAddress = Config::get('mail.from.address');
        $fromName = Config::get('mail.from.name');

        return $this->from($fromAddress, $fromName)
            ->subject('Application was shared')
            ->markdown('emails.share-invites');
    }
}
