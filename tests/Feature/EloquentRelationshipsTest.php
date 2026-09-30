<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Watch;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relationships_from_diagram(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();

        $order = Order::create(['user_id' => $user->id, 'total' => 150000]);
        $cartItem = CartItem::create(['user_id' => $user->id, 'watch_id' => $watch->id, 'quantity' => 2]);
        $wishlistItem = WishlistItem::create(['user_id' => $user->id, 'watch_id' => $watch->id]);

        $this->assertTrue($user->orders->contains($order));
        $this->assertTrue($user->cartItems->contains($cartItem));
        $this->assertTrue($user->wishlistItems->contains($wishlistItem));
    }

    public function test_watch_relationships_from_diagram(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'total' => 200000]);

        $cartItem = CartItem::create(['user_id' => $user->id, 'watch_id' => $watch->id, 'quantity' => 1]);
        $wishlistItem = WishlistItem::create(['user_id' => $user->id, 'watch_id' => $watch->id]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => 200000,
        ]);

        $this->assertTrue($watch->cartItems->contains($cartItem));
        $this->assertTrue($watch->wishlistItems->contains($wishlistItem));
        $this->assertTrue($watch->orderItems->contains($orderItem));
    }

    public function test_cart_item_relationships_from_diagram(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();
        $cartItem = CartItem::create(['user_id' => $user->id, 'watch_id' => $watch->id, 'quantity' => 1]);

        $this->assertSame($user->id, $cartItem->user->id);
        $this->assertSame($watch->id, $cartItem->watch->id);
    }

    public function test_wishlist_item_relationships_from_diagram(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();
        $wishlistItem = WishlistItem::create(['user_id' => $user->id, 'watch_id' => $watch->id]);

        $this->assertSame($user->id, $wishlistItem->user->id);
        $this->assertSame($watch->id, $wishlistItem->watch->id);
    }

    public function test_order_relationships_from_diagram(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'total' => 300000]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => 300000,
        ]);

        $this->assertSame($user->id, $order->user->id);
        $this->assertTrue($order->items->contains($orderItem));
    }

    public function test_order_item_relationships_from_diagram(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'total' => 120000]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => 120000,
        ]);

        $this->assertSame($order->id, $orderItem->order->id);
        $this->assertSame($watch->id, $orderItem->watch->id);
    }
}
