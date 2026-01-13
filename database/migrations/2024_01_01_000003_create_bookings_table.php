<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('booking_type', ['passenger', 'parcel']);
            $table->integer('seats')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('volume', 8, 2)->nullable();
            $table->text('dimensions')->nullable()->comment('JSON: length, width, height');
            $table->text('instructions')->nullable();
            $table->decimal('price', 8, 2);
            $table->enum('status', ['pending', 'confirmed', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            $table->string('payment_intent_id')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

