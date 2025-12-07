<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display the transactions page with filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $status = $request->get('status', 'all');
        $paymentStatus = $request->get('payment_status', 'all');
        $search = $request->get('search', '');
        
        // Build query
        $query = $user->orders()->latest();
        
        // Filter by order status
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Filter by payment status
        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }
        
        // Search by order number
        if ($search) {
            $query->where('order_number', 'like', '%' . $search . '%');
        }
        
        $orders = $query->paginate(10)->withQueryString();
        
        // Get statistics
        $allOrders = $user->orders()->get();
        $stats = [
            'total' => $allOrders->count(),
            'pending' => $allOrders->where('status', 'pending')->count(),
            'processing' => $allOrders->where('status', 'processing')->count(),
            'shipped' => $allOrders->where('status', 'shipped')->count(),
            'delivered' => $allOrders->where('status', 'delivered')->count(),
            'cancelled' => $allOrders->where('status', 'cancelled')->count(),
        ];
        
        $paymentStats = [
            'pending' => $allOrders->where('payment_status', 'pending')->count(),
            'paid' => $allOrders->where('payment_status', 'paid')->count(),
            'failed' => $allOrders->where('payment_status', 'failed')->count(),
            'expired' => $allOrders->where('payment_status', 'expired')->count(),
            'cancelled' => $allOrders->where('payment_status', 'cancelled')->count(),
        ];
        
        return view('pages.account.transactions', compact('orders', 'stats', 'paymentStats', 'status', 'paymentStatus', 'search'));
    }
    
    /**
     * Display a single transaction detail.
     */
    public function show($id)
    {
        $user = Auth::user();
        $order = $user->orders()->with('items.product', 'items.variant')->findOrFail($id);
        
        return view('pages.account.transaction-show', compact('order'));
    }
}

