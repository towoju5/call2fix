<?php

namespace App\Notifications\Property;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class DeletedNotification extends Notification
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
                    ->subject('Property Deleted from Your Account')
                    ->line('A property has been deleted from your account.')
                    ->line('Property Details:')
                    ->line('Name: ' . $this->property->name)
                    ->line('Address: ' . $this->property->address)
                    ->line('If you believe this was done in error, please contact our support team.')
                    ->action('View Your Properties', url('/properties'))
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
            'title' => 'Property Deleted',
            'message' => 'The property "' . $this->property->name . '" has been deleted from your account.',
            'property_id' => $this->property->id,
            'property_name' => $this->property->name,
            'property_address' => $this->property->address,
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Property Deleted')
                ->setBody('The property "' . $this->property->name . '" has been deleted from your account.')
            )
            ->setData([
                'property_id' => $this->property->id,
                'property_name' => $this->property->name,
                'property_address' => $this->property->address,
            ]);
    }
}
