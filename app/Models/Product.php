<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'regular_price',
        'sale_price',
        'stock_status',
        'featured',
        'is_visible',
        'quantity',
        'image',
        'images',
        'category_id',
        'brand_id',
    ];

    protected $casts = [
        'featured'   => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * Scope a query to only the products that should be shown to customers.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get total quantity sold.
     */
    public function getTotalSoldAttribute(): int
    {
        return $this->orderItems()
            ->whereHas('order', fn($q) => $q->where('status', 'completed'))
            ->sum('quantity');
    }

    /**
     * Get total revenue from this product.
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->orderItems()
            ->whereHas('order', fn($q) => $q->where('status', 'completed'))
            ->sum('subtotal');
    }

    /**
     * Get the effective price (sale price if available, otherwise regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->regular_price;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('display')
            ->fit(Fit::Max, 1080, 1080)
            ->nonQueued()
            ->performOnCollections('product', 'gallery');

        $this->addMediaConversion('thumb')
            ->crop(104, 104, CropPosition::Top)
            ->nonQueued()
            ->performOnCollections('product', 'gallery');
    }
}
