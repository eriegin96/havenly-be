<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReview extends Model
{
    use HasFactory;

    protected $primaryKey = 'booking_order_id';
    public $incrementing = false;

    protected $fillable = [
        'booking_order_id',
        'rating',
        'review',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    /**
     * Get the booking order that owns the review.
     */
    public function bookingOrder(): BelongsTo
    {
        return $this->belongsTo(BookingOrder::class);
    }
}
