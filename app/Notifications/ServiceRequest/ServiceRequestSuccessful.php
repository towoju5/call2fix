<?php

namespace App\Notifications\ServiceRequest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class ServiceRequestSuccessful extends Notification
{
    use Queueable;

    protected $order;

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
        fcm("New Order on Call2Fix", "Your Service Request has been placed successfully.", auth()->user()->device_id);
        return (new MailMessage)
                    ->subject('Service Request Placed Successfully')
                    ->line('Your Service Request has been placed successfully.')
                    ->line('Thank you.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Service Request Placed Successfully',
            'message' => 'You have place a new Service Request',
        ];
    }
}
