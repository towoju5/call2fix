<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepartmentCreatedNotification extends Notification
{
    use Queueable;

    public $department;

    /**
     * Create a new notification instance.
     */
    public function __construct($department)
    {
        $this->department = $department;
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
                    ->line('A new department has been created.')
                    ->line('Department Name: ' . $this->department->name)
                    ->action('View Department', url('/departments/' . $this->department->id))
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
            'message' => 'A new department has been created.',
            'department_name' => $this->department->name,
            'department_id' => $this->department->id,
        ];
    }
}
