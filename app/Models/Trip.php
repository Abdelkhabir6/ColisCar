<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'departure_city',
        'arrival_city',
        'departure_latitude',
        'departure_longitude',
        'arrival_latitude',
        'arrival_longitude',
        'departure_date',
        'departure_time',
        'price_per_seat',
        'available_seats',
        'total_seats',
        'available_volume',
        'max_weight',
        'trip_type',
        'description',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'departure_time' => 'datetime',
        'price_per_seat' => 'decimal:2',
        'available_volume' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'departure_latitude' => 'decimal:8',
        'departure_longitude' => 'decimal:8',
        'arrival_latitude' => 'decimal:8',
        'arrival_longitude' => 'decimal:8',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function passengerBookings()
    {
        return $this->hasMany(Booking::class)->where('booking_type', 'passenger');
    }

    public function parcelBookings()
    {
        return $this->hasMany(Booking::class)->where('booking_type', 'parcel');
    }

    public function getOccupiedSeatsAttribute(): int
    {
        return $this->passengerBookings()->whereIn('status', ['confirmed', 'in_transit'])->sum('seats');
    }

    public function getOccupiedVolumeAttribute(): float
    {
        return $this->parcelBookings()->whereIn('status', ['confirmed', 'in_transit'])->sum('volume');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'pending' || $this->status === 'confirmed';
    }

    public function canAcceptPassengers(): bool
    {
        return in_array($this->trip_type, ['passengers', 'mixed']) && $this->available_seats > 0;
    }

    public function canAcceptParcels(): bool
    {
        return in_array($this->trip_type, ['parcels', 'mixed']) && $this->available_volume > 0;
    }
}

