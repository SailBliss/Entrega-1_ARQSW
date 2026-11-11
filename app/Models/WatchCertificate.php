<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'watch_id',
        'user_id',
        'serial_number',
        'certificate_hash',
        'block_index',
        'block_hash',
        'node_url',
        'status',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'block_index' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
