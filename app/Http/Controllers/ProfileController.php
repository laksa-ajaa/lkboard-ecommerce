<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the user profile page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get order statistics
        $orders = $user->orders()->latest()->get();
        
        $orderStats = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'shipped' => $orders->where('status', 'shipped')->count(),
            'delivered' => $orders->where('status', 'delivered')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
        ];
        
        // Get payment status statistics
        $paymentStats = [
            'pending' => $orders->where('payment_status', 'pending')->count(),
            'paid' => $orders->where('payment_status', 'paid')->count(),
            'failed' => $orders->where('payment_status', 'failed')->count(),
            'expired' => $orders->where('payment_status', 'expired')->count(),
            'cancelled' => $orders->where('payment_status', 'cancelled')->count(),
        ];
        
        // Get recent orders (last 5)
        $recentOrders = $orders->take(5);
        
        return view('pages.account.profile', compact('user', 'orderStats', 'paymentStats', 'recentOrders'));
    }
}

