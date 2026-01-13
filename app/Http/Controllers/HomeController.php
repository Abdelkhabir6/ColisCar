<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredTrips = Trip::where('status', 'pending')
            ->where('departure_date', '>=', now())
            ->with('driver')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('home', compact('featuredTrips'));
    }
}

