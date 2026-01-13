<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Indique si la notification doit être mise en queue
     * Changez en false pour envoyer immédiatement (mode développement)
     */
    public $shouldQueue = true;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        
        $type = $booking->isPassenger() ? 'passager' : 'colis';
        
        return (new MailMessage)
            ->subject('✅ Réservation confirmée - ColisCar')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre réservation a été confirmée avec succès.')
            ->line('**Détails de votre réservation :**')
            ->line('📍 **Trajet :** ' . $trip->departure_city . ' → ' . $trip->arrival_city)
            ->line('📅 **Date :** ' . $trip->departure_date->format('d/m/Y') . ' à ' . date('H:i', strtotime($trip->departure_time)))
            ->line('👤 **Conducteur :** ' . $trip->driver->name)
            ->line('💰 **Prix :** ' . number_format($booking->price, 2) . ' €')
            ->when($booking->isPassenger(), function ($mail) use ($booking) {
                return $mail->line('🪑 **Nombre de places :** ' . $booking->seats);
            })
            ->when($booking->isParcel(), function ($mail) use ($booking) {
                return $mail
                    ->line('📦 **Poids :** ' . $booking->weight . ' kg')
                    ->line('📏 **Volume :** ' . $booking->volume . ' m³');
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
            'trip_id' => $this->booking->trip_id,
        ];
    }
}
