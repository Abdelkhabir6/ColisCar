<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'user_id',
        'booking_type',
        'seats',
        'weight',
        'volume',
        'dimensions',
        'instructions',
        'price',
        'status',
        'payment_intent_id',
        'is_paid',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'volume' => 'decimal:2',
        'price' => 'decimal:2',
        'is_paid' => 'boolean',
        'dimensions' => 'array',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPassenger(): bool
    {
        return $this->booking_type === 'passenger';
    }

    public function isParcel(): bool
    {
        return $this->booking_type === 'parcel';
    }
}

