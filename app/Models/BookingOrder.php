<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_type_id',
        'room_id',
        'status',
        'check_in_date',
        'check_out_date',
        'phone',
        'adult',
        'children',
        'total_price',
        'is_paid',
        'is_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'datetime',
            'check_out_date' => 'datetime',
            'is_paid' => 'boolean',
            'is_reviewed' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the booking order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the room type that owns the booking order.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the room that owns the booking order.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the review for this booking order.
     */
    public function review(): HasOne
    {
        return $this->hasOne(BookingReview::class);
    }
}
