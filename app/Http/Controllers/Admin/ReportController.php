<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->input('status', 'selesai');

        $query = Order::with(['user', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->latest()->get();

        $totalRevenue = $orders->sum('grand_total');
        $totalOrders = $orders->count();
        $totalBooksSold = OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Top selling books in this period
        $topBooks = OrderItem::select('book_title', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->whereIn('order_id', $orders->pluck('id'))
            ->groupBy('book_title')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('admin.reports.index', compact(
            'orders',
            'startDate',
            'endDate',
            'status',
            'totalRevenue',
            'totalOrders',
            'totalBooksSold',
            'averageOrderValue',
            'topBooks'
        ));
    }

    public function print(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->input('status', 'selesai');

        $query = Order::with(['user', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->latest()->get();

        $totalRevenue = $orders->sum('grand_total');
        $totalOrders = $orders->count();
        $totalBooksSold = OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return view('admin.reports.print', compact(
            'orders',
            'startDate',
            'endDate',
            'status',
            'totalRevenue',
            'totalOrders',
            'totalBooksSold',
            'averageOrderValue'
        ));
    }
}
