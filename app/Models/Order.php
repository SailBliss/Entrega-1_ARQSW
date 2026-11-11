<?php

// Isabela Ruiz

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'gift_plant_id',
        'gift_plant_name',
        'gift_plant_category',
        'gift_plant_image',
        'gift_plant_detail_url',
        'gift_plant_price',
    ];

    public function hasGiftPlant(): bool
    {
        return ! empty($this->gift_plant_name);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(WatchCertificate::class);
    }
}
