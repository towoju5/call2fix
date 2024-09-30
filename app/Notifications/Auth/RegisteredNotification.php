<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class RegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
                    ->subject('Welcome to ' . config('app.name'))
                    ->line('Thank you for registering with ' . config('app.name') . '!')
                    ->line('Time: ' . now()->toDateTimeString())
                    ->line('IP Address: ' . request()->ip())
                    ->line('We are excited to have you on board.')
                    ->action('Get Started', url('/dashboard'))
                    ->line('If you have any questions, feel free to contact our support team.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome to ' . config('app.name'),
            'message' => 'Thank you for registering. We are excited to have you on board!',
        ];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData(['action' => 'Welcome to ' . config('app.name'), 'data' => 'Thank you for registering. We are excited to have you on board!'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->title('Welcome to ' . config('app.name'))
                ->body('Thank you for registering. We are excited to have you on board!'));
    }
}
