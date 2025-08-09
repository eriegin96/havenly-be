<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area',
        'price',
        'quantity',
        'adult',
        'children',
        'description',
    ];

    /**
     * Get the rooms for this room type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the images for this room type.
     */
    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }

    /**
     * Get the booking orders for this room type.
     */
    public function bookingOrders(): HasMany
    {
        return $this->hasMany(BookingOrder::class);
    }

    /**
     * Get the facilities for this room type.
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'room_facilities');
    }

    /**
     * Get the features for this room type.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'room_features');
    }
}
