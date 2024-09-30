<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class LogiNotification extends Notification
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
        return ['mail', 'database', 'fcm'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Successful Login Notification')
                    ->line('A successful login was detected on your account.')
                    ->line('Time: ' . now()->toDateTimeString())
                    ->line('IP Address: ' . request()->ip())
                    ->line('If this was not you, please contact support immediately.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Successful Login',
            'message' => 'A successful login was detected on your account at ' . now()->toDateTimeString() . ' from IP: ' . request()->ip(),
        ];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData(['action' => "Successful Login", 'data' => 'A successful login was detected on your account at ' . now()->toDateTimeString() . ' from IP: ' . request()->ip()])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->title('Successful Login')
                ->body('A successful login was detected on your account at ' . now()->toDateTimeString() . ' from IP: ' . request()->ip()));
    }
}
