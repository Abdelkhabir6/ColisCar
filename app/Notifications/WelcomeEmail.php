<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleMessage = match($notifiable->role) {
            'driver' => 'En tant que conducteur, vous pouvez maintenant publier des trajets et gagner de l\'argent en transportant des passagers ou des colis.',
            'sender' => 'En tant qu\'expéditeur, vous pouvez maintenant envoyer vos colis facilement et à moindre coût.',
            default => 'Vous pouvez maintenant rechercher des trajets, réserver des places ou envoyer des colis.',
        };
        
        return (new MailMessage)
            ->subject('🎉 Bienvenue sur ColisCar !')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Bienvenue sur ColisCar, la plateforme qui combine covoiturage et transport de colis !')
            ->line($roleMessage)
            ->line('**Découvrez nos fonctionnalités :**')
            ->line('✅ Recherchez des trajets rapidement')
            ->line('✅ Réservez des places ou envoyez des colis')
            ->line('✅ Communiquez avec les conducteurs')
            ->line('✅ Gérez vos réservations facilement')
            ->action('Commencer à explorer', route('trips.index'))
            ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.')
            ->line('Merci de nous faire confiance !')
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
            'user_id' => $notifiable->id,
        ];
    }
}
