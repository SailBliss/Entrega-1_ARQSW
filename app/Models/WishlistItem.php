<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    protected $fillable = ['user_id', 'watch_id'];

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}
