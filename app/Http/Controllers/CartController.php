<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Watch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $request->user()->cartItems()->with('watch')->get();

        return view('cart.index', [
            'title' => 'Carrito - Tienda Relojes',
            'subtitle' => 'Tu carrito',
            'items' => $items,
            'total' => $this->total($items),
        ]);
    }

    public function add(Request $request, Watch $watch): RedirectResponse
    {
        $quantity = max(1, (int) $request->input('quantity', 1));

        $item = $request->user()->cartItems()->firstOrNew(['watch_id' => $watch->id]);
        $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

        if ($watch->stock < 1) {
            return back()->withErrors(['cart' => 'Este reloj está agotado.']);
        }

        $item->quantity = min($newQuantity, $watch->stock);
        $item->save();

        $message = $newQuantity > $watch->stock
            ? 'Se añadió al carrito (limitado al stock disponible: '.$watch->stock.').'
            : 'Reloj añadido al carrito.';

        return back()->with('success', $message);
    }

    public function remove(Request $request, Watch $watch): RedirectResponse
    {
        $request->user()->cartItems()->where('watch_id', $watch->id)->delete();

        return back()->with('success', 'Reloj quitado del carrito.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->cartItems()->delete();

        return back()->with('success', 'Carrito vaciado.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($user) {
            $items = $user->cartItems()->with('watch')->get();

            if ($items->isEmpty()) {
                return 'El carrito está vacío.';
            }

            $watches = Watch::whereIn('id', $items->pluck('watch_id'))->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                if ($watches[$item->watch_id]->stock < $item->quantity) {
                    return 'No hay stock suficiente de "'.$item->watch->name.'".';
                }
            }

            $order = Order::create(['user_id' => $user->id, 'total' => $this->total($items)]);

            foreach ($items as $item) {
                $order->items()->create([
                    'watch_id' => $item->watch_id,
                    'quantity' => $item->quantity,
                    'price' => $item->watch->price,
                ]);
                $watches[$item->watch_id]->decrement('stock', $item->quantity);
            }

            $user->cartItems()->delete();

            return $order;
        });

        if (is_string($result)) {
            return redirect()->route('cart.index')->withErrors(['cart' => $result]);
        }

        return redirect()->route('orders.show', $result)->with('success', '¡Compra realizada con éxito!');
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return view('cart.order', [
            'title' => 'Pedido #'.$order->id.' - Tienda Relojes',
            'subtitle' => 'Pedido #'.$order->id,
            'order' => $order->load('items.watch'),
        ]);
    }

    private function total($items): int
    {
        return $items->sum(fn (CartItem $item) => $item->subtotal());
    }
}
