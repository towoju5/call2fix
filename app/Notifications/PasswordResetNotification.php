<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;

class PasswordResetNotification extends Notification
{
    use Queueable;

    protected $resetCode;

    public function __construct($resetCode)
    {
        $this->resetCode = $resetCode;
    }

    public function via($notifiable)
    {
        return $notifiable->preferredChannel();
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Password Reset Code')
                    ->line('Your password reset code is: ' . $this->resetCode)
                    ->line('If you didn\'t request a password reset, please ignore this message.');
    }

    // public function toNexmo($notifiable)
    // {
    //     return (new NexmoMessage)
    //                 ->content('Your password reset code is: ' . $this->resetCode);
    // }
}
