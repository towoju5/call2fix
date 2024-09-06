<?php

namespace App\Notifications\Property;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class UpdatedNotification extends Notification
{
    use Queueable;

    protected $property;

    /**
     * Create a new notification instance.
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Property Updated')
                    ->line('A property has been updated on your account.')
                    ->line('Property Details:')
                    ->line('Name: ' . $this->property->name)
                    ->line('Address: ' . $this->property->address)
                    ->line('Type: ' . $this->property->type)
                    ->action('View Property', url('/properties/' . $this->property->id))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Property Updated',
            'message' => 'A property has been updated on your account.',
            'property_id' => $this->property->id,
            'property_name' => $this->property->name,
            'property_address' => $this->property->address,
            'property_type' => $this->property->type,
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Property Updated')
                ->setBody('A property has been updated on your account.')
            )
            ->setData([
                'property_id' => $this->property->id,
                'property_name' => $this->property->name,
            ]);
    }
}
