<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        return $this->subject('Support Request: ' . $this->emailData['subject'])
                    ->view('emails.support')
                    ->with([
                        'subject' => $this->emailData['subject'],
                        'payload' => $this->emailData['message'],
                        'sender' => auth()->user() ?? $this->emailData['sender'],
                    ]);
    }
}
