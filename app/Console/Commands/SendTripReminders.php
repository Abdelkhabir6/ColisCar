<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\TripReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendTripReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des rappels par email 24h avant les trajets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Envoi des rappels de trajets...');

        // Récupérer les trajets qui ont lieu demain
        $tomorrow = Carbon::tomorrow();
        
        $bookings = Booking::whereHas('trip', function ($query) use ($tomorrow) {
            $query->whereDate('departure_date', $tomorrow->format('Y-m-d'))
                  ->whereIn('status', ['pending', 'confirmed']);
        })
        ->whereIn('status', ['pending', 'confirmed'])
        ->with(['trip', 'user'])
        ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            try {
                $booking->user->notify(new TripReminder($booking->trip));
                $count++;
            } catch (\Exception $e) {
                $this->error('Erreur lors de l\'envoi du rappel pour la réservation #' . $booking->id . ': ' . $e->getMessage());
            }
        }

        $this->info("{$count} rappel(s) envoyé(s) avec succès.");
        
        return Command::SUCCESS;
    }
}
