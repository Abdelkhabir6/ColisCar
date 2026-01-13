<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Booking $booking): PaymentIntent
    {
        $paymentIntent = PaymentIntent::create([
            'amount' => (int)($booking->price * 100), // Convertir en centimes
            'currency' => 'eur',
            'metadata' => [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ],
        ]);

        $booking->update([
            'payment_intent_id' => $paymentIntent->id,
        ]);

        return $paymentIntent;
    }

    public function confirmPayment(Booking $booking): bool
    {
        if (!$booking->payment_intent_id) {
            return false;
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($booking->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $booking->update(['is_paid' => true]);

                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'amount' => $booking->price,
                    'currency' => 'eur',
                    'status' => 'succeeded',
                ]);

                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Erreur de paiement Stripe: ' . $e->getMessage());
        }

        return false;
    }

    public function refundPayment(Payment $payment): bool
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
            ]);

            if ($refund->status === 'succeeded') {
                $payment->update(['status' => 'refunded']);
                $payment->booking->update(['is_paid' => false]);
                return true;
            }
        } catch (\Exception $e) {
            \Log::error('Erreur de remboursement Stripe: ' . $e->getMessage());
        }

        return false;
    }
}

