<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'shipping_cost',
        'grand_total',
        'status',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'city',
        'postal_code',
        'notes',
        'courier_name',
        'tracking_number',
        'shipped_at',
        'completed_at',
        'payment_method',
        'payment_proof',
        'payment_note',
        'paid_at',
        'stock_reduced',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'shipping_cost' => 'float',
        'grand_total' => 'float',
        'stock_reduced' => 'boolean',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedShippingCostAttribute(): string
    {
        return 'Rp ' . number_format($this->shipping_cost, 0, ',', '.');
    }

    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if ($this->payment_proof && Storage::disk('public')->exists($this->payment_proof)) {
            return asset('storage/' . $this->payment_proof);
        }
        return null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'pembayaran_ditolak' => 'Pembayaran Ditolak',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'badge-warning',
            'menunggu_konfirmasi' => 'badge-info',
            'diproses' => 'badge-primary',
            'dikirim' => 'badge-cyan',
            'selesai' => 'badge-success',
            'dibatalkan' => 'badge-secondary',
            'pembayaran_ditolak' => 'badge-danger',
            default => 'badge-light',
        };
    }

    public static function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        return "INV-{$date}-{$random}";
    }
}
