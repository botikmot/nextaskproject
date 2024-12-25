<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatMessageNotification extends Notification
{
    use Queueable;
    protected $message;
    protected $sender;
    /**
     * Create a new notification instance.
     */
    public function __construct($message, $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        //return ['mail'];
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable)
    {
        $data = [
            'type' => 'chat',
            'message' => $this->message,
            'sender' => $this->sender,
            'data' => json_encode([  // Ensure this is JSON-encoded
                'message' => $this->message,
                'sender' => $this->sender
            ]),
        ];

        logger('Notification Data:', $data);
        return $data;
    }
}
