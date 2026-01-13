<?php

namespace App\Notifications;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TripReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $trip;

    /**
     * Create a new notification instance.
     */
    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
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
        $trip = $this->trip->load('driver');
        
        return (new MailMessage)
            ->subject('⏰ Rappel : Votre trajet est demain - ColisCar')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Ceci est un rappel : votre trajet est prévu demain.')
            ->line('**Détails du trajet :**')
            ->line('📍 **Trajet :** ' . $trip->departure_city . ' → ' . $trip->arrival_city)
            ->line('📅 **Date :** ' . $trip->departure_date->format('d/m/Y') . ' à ' . date('H:i', strtotime($trip->departure_time)))
            ->line('👤 **Conducteur :** ' . $trip->driver->name)
            ->line('💰 **Prix par place :** ' . number_format($trip->price_per_seat, 2) . ' €')
            ->line('**N\'oubliez pas de :**')
            ->line('✅ Vérifier votre point de rendez-vous')
            ->line('✅ Contacter le conducteur si nécessaire')
            ->line('✅ Arriver à l\'heure')
            ->action('Voir les détails du trajet', route('trips.show', $trip))
            ->line('Bon voyage avec ColisCar !')
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
            'trip_id' => $this->trip->id,
        ];
    }
}
