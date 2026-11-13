<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(15000);
            $table->decimal('grand_total', 12, 2);
            $table->enum('status', [
                'menunggu_pembayaran',
                'menunggu_konfirmasi',
                'diproses',
                'dikirim',
                'selesai',
                'dibatalkan',
                'pembayaran_ditolak'
            ])->default('menunggu_pembayaran');
            
            // Recipient & Address Info
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('shipping_address');
            $table->string('city');
            $table->string('postal_code');
            $table->text('notes')->nullable();

            // Shipping Info
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Payment Info
            $table->string('payment_method')->default('Transfer Bank');
            $table->string('payment_proof')->nullable();
            $table->text('payment_note')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Stock deduction flag
            $table->boolean('stock_reduced')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
