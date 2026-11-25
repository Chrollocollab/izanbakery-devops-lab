<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends Model
{
    use HasFactory;

    protected $table = 'customer_feedbacks';

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'type',
        'rating',
        'admin_response',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the user that submitted the feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the type badge class.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'suggestion' => 'badge-primary',
            'complaint' => 'badge-danger',
            'question' => 'badge-info',
            'praise' => 'badge-success',
            default => 'badge-secondary',
        };
    }
}
