<?php

// Isabela Ruiz, Miguel Angel Rendon

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Watch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Services\PlantService;

class CartController extends Controller
{
    public function index(Request $request, PlantService $plantService): View
    {
        $items = $request->user()->cartItems()->with('watch')->get();
        $totalQuantity = $items->sum('quantity');

        $giftPlant = null;
        if ($totalQuantity >= config('plants.min_watches_for_gift', 2)) {
            $giftPlant = $request->session()->get('gift_plant');
            if (! $giftPlant) {
                $giftPlant = $plantService->getRandomGiftPlant();
                if ($giftPlant) {
                    $request->session()->put('gift_plant', $giftPlant);
                }
            }
        } else {
            $request->session()->forget('gift_plant');
        }

        return view('cart.index', [
            'title' => __('messages.cart_title'),
            'subtitle' => __('messages.cart_subtitle'),
            'items' => $items,
            'total' => $this->total($items),
            'giftPlant' => $giftPlant,
            'totalQuantity' => $totalQuantity,
        ]);
    }

    public function add(Request $request, Watch $watch): RedirectResponse
    {
        $quantity = max(1, (int) $request->input('quantity', 1));

        $item = $request->user()->cartItems()->firstOrNew(['watch_id' => $watch->id]);
        $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

        if ($watch->stock < 1) {
            return back()->withErrors(['cart' => __('messages.cart_out_of_stock')]);
        }

        $item->quantity = min($newQuantity, $watch->stock);
        $item->save();

        $message = $newQuantity > $watch->stock
            ? __('messages.cart_added_limited', ['stock' => $watch->stock])
            : __('messages.cart_added');

        return back()->with('success', $message);
    }

    public function remove(Request $request, Watch $watch): RedirectResponse
    {
        $request->user()->cartItems()->where('watch_id', $watch->id)->delete();

        return back()->with('success', __('messages.cart_removed'));
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->cartItems()->delete();
        $request->session()->forget('gift_plant');

        return back()->with('success', __('messages.cart_cleared'));
    }

    public function checkout(Request $request, PlantService $plantService): RedirectResponse
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($user, $request, $plantService) {
            $items = $user->cartItems()->with('watch')->get();

            if ($items->isEmpty()) {
                return __('messages.cart_empty_checkout');
            }

            $watches = Watch::whereIn('id', $items->pluck('watch_id'))->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                if ($watches[$item->watch_id]->stock < $item->quantity) {
                    return __('messages.cart_not_enough_stock', ['name' => $item->watch->name]);
                }
            }

            $totalQuantity = $items->sum('quantity');
            $giftPlant = null;
            if ($totalQuantity >= config('plants.min_watches_for_gift', 2)) {
                $giftPlant = $request->session()->get('gift_plant') ?: $plantService->getRandomGiftPlant();
            }

            $orderData = [
                'user_id' => $user->id,
                'total' => $this->total($items),
            ];

            if ($giftPlant) {
                $orderData['gift_plant_id'] = $giftPlant['id'] ?? null;
                $orderData['gift_plant_name'] = $giftPlant['nombre'] ?? null;
                $orderData['gift_plant_category'] = $giftPlant['categoria'] ?? null;
                $orderData['gift_plant_image'] = $giftPlant['imagen_url'] ?? null;
                $orderData['gift_plant_detail_url'] = $giftPlant['detalle_url'] ?? null;
                $orderData['gift_plant_price'] = $giftPlant['precio'] ?? 0;
            }

            $order = Order::create($orderData);

            foreach ($items as $item) {
                $order->items()->create([
                    'watch_id' => $item->watch_id,
                    'quantity' => $item->quantity,
                    'price' => $item->watch->price,
                ]);
                $watches[$item->watch_id]->decrement('stock', $item->quantity);
            }

            if ($giftPlant && ! empty($giftPlant['id'])) {
                $plantService->notifyStockReduction((int) $giftPlant['id']);
            }

            $user->cartItems()->delete();
            $request->session()->forget('gift_plant');

            return $order;
        });

        if (is_string($result)) {
            return redirect()->route('cart.index')->withErrors(['cart' => $result]);
        }

        // Issue blockchain authenticity certificates for purchased items
        $blockchainService = app(\App\Services\BlockchainService::class);
        foreach ($result->items()->with('watch')->get() as $orderItem) {
            $blockchainService->issueAndMine($result, $orderItem);
        }

        return redirect()->route('orders.show', $result)->with('success', __('messages.order_success'));
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return view('cart.order', [
            'title' => __('messages.order_title', ['id' => $order->id]),
            'subtitle' => __('messages.order_subtitle', ['id' => $order->id]),
            'order' => $order->load('items.watch', 'items.certificate'),
        ]);
    }

    private function total($items): int
    {
        return $items->sum(fn (CartItem $item) => $item->subtotal());
    }
}
