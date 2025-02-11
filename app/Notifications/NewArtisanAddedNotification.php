<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewArtisanAddedNotification extends Notification
{
    use Queueable;
    
    public $artisan, $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($artisan, $password)
    {
        $this->artisan = $artisan;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Your Account Details')
            ->view('vendor.email', [
                'greeting' => 'Hello ' . $this->artisan->name,
                'introLines' => [
                    'Welcome to ' . config('app.name') . '! Your artisan account has been successfully created.',
                    'Here are your login credentials:',
                    'Email: ' . $this->artisan->email,
                    'Password: ' . $this->password,
                ],
                'actionText' => 'Login to Your Account',
                'actionUrl' => url('/login'),
                'outroLines' => [
                    'For security reasons, we recommend changing your password after your first login.',
                    'If you have any questions, please don\'t hesitate to contact our support team.'
                ]
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Artisan Account Created',
            'message' => 'Your artisan account has been successfully created'
        ];
    }
}
