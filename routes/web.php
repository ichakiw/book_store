<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookCatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderUserController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - BookStore Application
|--------------------------------------------------------------------------
*/

// Home / Landing Page
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/buku', [BookCatalogController::class, 'index'])->name('books.index');
Route::get('/buku/{slug}', [BookCatalogController::class, 'show'])->name('books.show');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Authenticated User & Shared Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Customer Dashboard & Profile
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::put('/dashboard/profile', [UserDashboardController::class, 'updateProfile'])->name('user.profile.update');

    // Cart Management
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/tambah/{bookId}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/keranjang/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/hapus/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/keranjang/kosongkan', [CartController::class, 'clear'])->name('cart.clear');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    // Customer Orders
    Route::get('/pesanan', [OrderUserController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{orderNumber}', [OrderUserController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{orderNumber}/pembayaran', [OrderUserController::class, 'payment'])->name('orders.payment');
    Route::post('/pesanan/{orderNumber}/pembayaran', [OrderUserController::class, 'uploadPaymentProof'])->name('orders.payment.upload');
    Route::post('/pesanan/{orderNumber}/batalkan', [OrderUserController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{orderNumber}/diterima', [OrderUserController::class, 'confirmReceipt'])->name('orders.confirm_receipt');

    // Customer Chat with Admin
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
});

// Order Invoice (Shared printable receipt accessible by customer and admin)
Route::get('/pesanan/{orderNumber}/invoice', [OrderUserController::class, 'invoice'])->name('orders.invoice');

// Admin Dedicated Routes
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Book Management
    Route::get('/buku', [AdminBookController::class, 'index'])->name('books.index');
    Route::get('/buku/tambah', [AdminBookController::class, 'create'])->name('books.create');
    Route::post('/buku', [AdminBookController::class, 'store'])->name('books.store');
    Route::get('/buku/{id}/edit', [AdminBookController::class, 'edit'])->name('books.edit');
    Route::put('/buku/{id}', [AdminBookController::class, 'update'])->name('books.update');
    Route::delete('/buku/{id}', [AdminBookController::class, 'destroy'])->name('books.destroy');
    Route::patch('/buku/{id}/toggle-status', [AdminBookController::class, 'toggleActive'])->name('books.toggle_status');

    // Category Management
    Route::get('/kategori', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    // Stock Management
    Route::get('/stok', [AdminStockController::class, 'index'])->name('stocks.index');
    Route::post('/stok/{id}', [AdminStockController::class, 'update'])->name('stocks.update');

    // Order & Payment & Shipping Management
    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{id}/konfirmasi-pembayaran', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirm_payment');
    Route::post('/pesanan/{id}/tolak-pembayaran', [AdminOrderController::class, 'rejectPayment'])->name('orders.reject_payment');
    Route::post('/pesanan/{id}/update-pengiriman', [AdminOrderController::class, 'updateShipping'])->name('orders.update_shipping');
    Route::post('/pesanan/{id}/selesai', [AdminOrderController::class, 'completeOrder'])->name('orders.complete');
    Route::post('/pesanan/{id}/batal', [AdminOrderController::class, 'cancelOrder'])->name('orders.cancel');

    // Customer Management
    Route::get('/pelanggan', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/pelanggan/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::patch('/pelanggan/{id}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('customers.toggle_status');
    Route::delete('/pelanggan/{id}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');

    // Sales Report
    Route::get('/laporan', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/cetak', [AdminReportController::class, 'print'])->name('reports.print');

    // Customer Live Chat
    Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{userId}/messages', [AdminChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/{userId}/messages', [AdminChatController::class, 'sendMessage'])->name('chat.send');
});
