<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'method',
        'amount',
        'status',
        'paid_at',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Whether the payment has been confirmed/completed by an admin.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Public URL to the uploaded proof of payment, if any.
     */
    public function getReceiptUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('receipt') ?: ($this->receipt_path
            ? asset('uploads/receipts/' . $this->receipt_path)
            : null);
    }

    /**
     * Whether a proof of payment has been uploaded, in either the media
     * library or (for pre-migration rows) the legacy column.
     */
    public function hasReceipt(): bool
    {
        return $this->getFirstMedia('receipt') !== null || ! empty($this->receipt_path);
    }

    /**
     * Whether the uploaded proof of payment is a PDF, checked via the media
     * library's mime type first, falling back to the legacy filename extension.
     */
    public function receiptIsPdf(): bool
    {
        if ($media = $this->getFirstMedia('receipt')) {
            return $media->mime_type === 'application/pdf';
        }

        return $this->receipt_path
            ? \Illuminate\Support\Str::endsWith(strtolower($this->receipt_path), '.pdf')
            : false;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipt')->singleFile();
    }

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for refunded payments.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the method label.
     */
    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'cash' => 'Cash on Delivery',
            'qr_transfer' => 'QR Transfer',
            default => ucfirst($this->method),
        };
    }
}
