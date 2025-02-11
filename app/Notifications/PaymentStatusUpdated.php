<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PaymentStatusUpdated extends Notification
{
    protected $status;
    protected $negotiation;
    protected $msg;

    public function __construct($status, $negotiation)
    {
        $this->status = $status;
        $this->negotiation = $negotiation;
        $this->msg = $this->status == 'Payment Confirmed' ? 'Your payment has been confirmed.' : 'Your payment is awaiting confirmation.';
    }

    // Define the database notification
    public function toDatabase($notifiable)
    {
        return [
            'negotiation_id' => $this->negotiation->id,
            'status' => $this->status,
            'message' => $this->msg,
            'title' => 'Payment Status Updated'
        ];
    }

    // Define the email notification (optional)
    public function toMail($notifiable)
    {
        // make fcm request to send pop notification to mobile app
        // fcm("Update on Service Request", $this->msg, $notifiable->device_id);
        return (new MailMessage)
            ->subject('Payment Status Update')
            ->line('Your payment status has been updated.')
            ->line('Status: ' . $this->status)
            ->action('View Request', url('/service-requests/' . $this->negotiation->request_id))
            ->line('Thank you for using our service!');
    }

    // You can add other notification channels here like SMS, Slack, etc.
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }
}
