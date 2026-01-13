<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $oldStatus)
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
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
        $booking = $this->booking->load(['trip.driver', 'user']);
        $trip = $booking->trip;
        
        $statusLabels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmé',
            'in_transit' => 'En transit',
            'delivered' => 'Livré',
            'cancelled' => 'Annulé',
        ];
        
        $newStatusLabel = $statusLabels[$booking->status] ?? $booking->status;
        $icon = match($booking->status) {
            'confirmed' => '✅',
            'in_transit' => '🚗',
            'delivered' => '📦',
            'cancelled' => '❌',
            default => '📋',
        };
        
        return (new MailMessage)
            ->subject($icon . ' Statut de réservation mis à jour - ColisCar')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Le statut de votre réservation a été mis à jour.')
            ->line('**Nouveau statut :** ' . $newStatusLabel)
            ->line('**Détails de la réservation :**')
            ->line('📍 **Trajet :** ' . $trip->departure_city . ' → ' . $trip->arrival_city)
            ->line('📅 **Date :** ' . $trip->departure_date->format('d/m/Y') . ' à ' . date('H:i', strtotime($trip->departure_time)))
            ->when($booking->status === 'in_transit', function ($mail) {
                return $mail->line('🚗 Votre trajet est en cours. Bon voyage !');
            })
            ->when($booking->status === 'delivered', function ($mail) {
                return $mail->line('✅ Votre colis a été livré avec succès. Merci d\'avoir utilisé ColisCar !');
            })
            ->when($booking->status === 'cancelled', function ($mail) {
                return $mail->line('❌ Votre réservation a été annulée. Si vous avez des questions, n\'hésitez pas à nous contacter.');
            })
            ->action('Voir ma réservation', route('bookings.show', $booking))
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
            'booking_id' => $this->booking->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->booking->status,
        ];
    }
}
