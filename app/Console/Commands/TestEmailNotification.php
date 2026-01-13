<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'envoi d\'email de notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if (!$email) {
            $email = $this->ask('Quel email voulez-vous utiliser pour le test ?');
        }

        $this->info("Test d'envoi d'email à : {$email}");

        try {
            // Test 1 : Email simple
            $this->info("Test 1 : Envoi d'un email simple...");
            Mail::raw('Ceci est un test d\'email depuis ColisCar', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - ColisCar');
            });
            $this->info("✓ Email simple envoyé !");

            // Test 2 : Notification
            $this->info("Test 2 : Envoi d'une notification...");
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->warn("Utilisateur non trouvé. Création d'un utilisateur de test...");
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'role' => 'user',
                ]);
            }

            // Trouver une réservation pour le test
            $booking = Booking::with(['trip.driver'])->first();
            
            if ($booking) {
                $user->notify(new BookingConfirmed($booking));
                $this->info("✓ Notification envoyée !");
            } else {
                $this->warn("Aucune réservation trouvée pour le test.");
            }

            $this->info("\n✅ Tests terminés ! Vérifiez votre boîte email.");
            $this->warn("\n⚠️  Si vous n'avez pas reçu d'email :");
            $this->warn("   1. Vérifiez votre configuration dans .env");
            $this->warn("   2. Vérifiez les logs : storage/logs/laravel.log");
            $this->warn("   3. Si QUEUE_CONNECTION=database, exécutez : php artisan queue:work");

        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            $this->error("Trace : " . $e->getTraceAsString());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
