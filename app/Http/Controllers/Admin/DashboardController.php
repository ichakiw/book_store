<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_revenue' => Order::where('status', 'selesai')->sum('grand_total'),
            'total_orders' => Order::count(),
            'pending_confirmation' => Order::where('status', 'menunggu_konfirmasi')->count(),
            'needs_shipping' => Order::where('status', 'diproses')->count(),
            'total_books' => Book::count(),
            'low_stock_books' => Book::where('stock', '<=', 5)->count(),
            'total_categories' => Category::count(),
            'total_customers' => User::where('role', 'user')->count(),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        $lowStockBooks = Book::with('category')
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockBooks'));
    }
}
