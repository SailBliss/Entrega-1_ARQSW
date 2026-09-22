<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'watch_id', 'quantity'];

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }

    public function subtotal(): int
    {
        return $this->quantity * $this->watch->price;
    }
}
