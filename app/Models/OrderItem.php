<?php

// Isabela Ruiz, Miguel Angel Rendon

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'watch_id', 'quantity', 'price'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}
