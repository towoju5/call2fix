<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewRequestNotification extends Notification
{
    use Queueable;

    protected $serviceRequest;

    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Send FCM notification to artisan's device
        fcm(
            "New Service Request", 
            "You've received a new service request. Check your dashboard for details.", 
            $notifiable->device_id
        );

        return (new MailMessage)
            ->subject('New Service Request Received')
            ->greeting('Hello Artisan!')
            ->line('A new service request has been submitted that matches your skills:')
            ->line("Customer: {$this->serviceRequest->user->name}")
            ->line("Service Category: {$this->serviceRequest->service_category->name}")
            ->line("Service Details: {$this->serviceRequest->problem_description}")
            ->line("Scheduled Date: {$this->serviceRequest->inspection_date->format('M d, Y')}")
            ->line("Location: {$this->serviceRequest->property->address}")
            ->action('View Details', url('/artisan/dashboard'))
            ->line('Please review the details and submit your quote if interested.')
            ->line('Thank you for using our service platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Service Request',
            'message' => 'You have received a new service request. Please check your dashboard.',
            'service_request_id' => $this->serviceRequest->id,
            'customer_name' => $this->serviceRequest->user->name,
            'service_category' => $this->serviceRequest->service_category->name,
            'scheduled_date' => $this->serviceRequest->inspection_date->format('M d, Y'),
            'location' => $this->serviceRequest->property->address
        ];
    }
}