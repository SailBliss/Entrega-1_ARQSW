<?php

// Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Watch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'brand', 'description', 'price', 'stock', 'image'];

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeSearch($query, ?string $term)
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $like = '%'.trim($term).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('brand', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
