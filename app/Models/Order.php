<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    // Order status constants
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PAID = 'paid';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'status_label',
        'delivery_type',
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'total',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'notes',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Pending Payment',
            self::STATUS_PAID => 'Paid',
            self::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get status display name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Whether a PDF receipt can be generated for this order (payment confirmed).
     */
    public function isReceiptAvailable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_COMPLETED,
        ], true);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_PAYMENT => 'badge-warning',
            self::STATUS_PAID => 'badge-success',
            self::STATUS_READY_FOR_PICKUP => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment for the order.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'IZB';
        $date = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(4)));
        $orderNumber = "{$prefix}-{$date}-{$random}";
        
        while (static::where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(bin2hex(random_bytes(4)));
            $orderNumber = "{$prefix}-{$date}-{$random}";
        }
        
        return $orderNumber;
    }

    /**
     * Scope for searching orders.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('order_number', 'ILIKE', "%{$search}%")
              ->orWhere('name', 'ILIKE', "%{$search}%")
              ->orWhere('email', 'ILIKE', "%{$search}%")
              ->orWhere('phone', 'ILIKE', "%{$search}%")
              ->orWhereHas('items', function($itemQuery) use ($search) {
                  $itemQuery->where('product_name', 'ILIKE', "%{$search}%");
              });
        });
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for active orders (not completed or cancelled).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }
}
