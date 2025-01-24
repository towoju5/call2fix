<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class ServiceProviderActionNotification extends Notification
{
    use Queueable;

    protected $action;
    protected $data;

    public function __construct($action, $data)
    {
        $this->action = $action;
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', FcmChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Action: ' . $this->action)
            ->line('Data: ' . json_encode($this->data));
    }

    public function toDatabase($notifiable)
    {
        return [
            'action' => $this->action,
            'data' => $this->data,
        ];
    }

    public function toFcm($notifiable)
    {
        return send_fcm($notifiable, $this->action, $this->data);
    }
}
