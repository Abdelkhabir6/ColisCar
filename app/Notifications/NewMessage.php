<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->message->load(['sender', 'booking.trip']);
        $booking = $message->booking;
        $trip = $booking->trip;
        
        return (new MailMessage)
            ->subject('💬 Nouveau message - ColisCar')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Vous avez reçu un nouveau message concernant votre réservation.')
            ->line('**De :** ' . $message->sender->name)
            ->line('**Trajet :** ' . $trip->departure_city . ' → ' . $trip->arrival_city)
            ->line('**Message :**')
            ->line('"' . $message->content . '"')
            ->action('Répondre au message', route('bookings.show', $booking))
            ->line('Merci d\'utiliser ColisCar !')
            ->salutation('Cordialement, L\'équipe ColisCar');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'booking_id' => $this->message->booking_id,
        ];
    }
}
