<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isDriver()) {
            return $this->driverDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    private function adminDashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_trips' => Trip::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('is_paid', true)->sum('price'),
            'pending_trips' => Trip::where('status', 'pending')->count(),
            'active_trips' => Trip::whereIn('status', ['confirmed', 'in_progress'])->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
        ];

        $recentTrips = Trip::with('driver')->latest()->take(10)->get();
        $recentBookings = Booking::with(['trip.driver', 'user'])->latest()->take(10)->get();

        return view('dashboard.admin', compact('stats', 'recentTrips', 'recentBookings'));
    }

    private function driverDashboard()
    {
        $user = Auth::user();

        $stats = [
            'total_trips' => Trip::where('driver_id', $user->id)->count(),
            'active_trips' => Trip::where('driver_id', $user->id)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count(),
            'total_bookings' => Booking::whereHas('trip', function ($q) use ($user) {
                $q->where('driver_id', $user->id);
            })->count(),
            'total_revenue' => Booking::whereHas('trip', function ($q) use ($user) {
                $q->where('driver_id', $user->id);
            })->where('is_paid', true)->sum('price'),
        ];

        $upcomingTrips = Trip::where('driver_id', $user->id)
            ->where('departure_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date')
            ->take(5)
            ->get();

        $recentBookings = Booking::whereHas('trip', function ($q) use ($user) {
            $q->where('driver_id', $user->id);
        })->with(['user', 'trip'])->latest()->take(10)->get();

        return view('dashboard.driver', compact('stats', 'upcomingTrips', 'recentBookings'));
    }

    private function userDashboard()
    {
        $user = Auth::user();

        $stats = [
            'total_bookings' => Booking::where('user_id', $user->id)->count(),
            'upcoming_bookings' => Booking::where('user_id', $user->id)
                ->whereHas('trip', function ($q) {
                    $q->where('departure_date', '>=', now());
                })
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'completed_bookings' => Booking::where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'completed'])
                ->count(),
        ];

        $upcomingBookings = Booking::where('user_id', $user->id)
            ->whereHas('trip', function ($q) {
                $q->where('departure_date', '>=', now());
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('trip.driver')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentBookings = Booking::where('user_id', $user->id)
            ->with('trip.driver')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.user', compact('stats', 'upcomingBookings', 'recentBookings'));
    }
}

