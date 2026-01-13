<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('departure_city');
            $table->string('arrival_city');
            $table->decimal('departure_latitude', 10, 8)->nullable();
            $table->decimal('departure_longitude', 11, 8)->nullable();
            $table->decimal('arrival_latitude', 10, 8)->nullable();
            $table->decimal('arrival_longitude', 11, 8)->nullable();
            $table->date('departure_date');
            $table->time('departure_time');
            $table->decimal('price_per_seat', 8, 2);
            $table->integer('available_seats');
            $table->integer('total_seats');
            $table->decimal('available_volume', 8, 2)->nullable()->comment('Volume disponible en m³');
            $table->decimal('max_weight', 8, 2)->nullable()->comment('Poids maximum en kg');
            $table->enum('trip_type', ['passengers', 'parcels', 'mixed'])->default('mixed');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

