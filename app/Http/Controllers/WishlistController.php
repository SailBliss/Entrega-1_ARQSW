<?php

namespace App\Http\Controllers;

use App\Models\Watch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('wishlist.index', [
            'title' => 'Lista de deseados - Tienda Relojes',
            'subtitle' => 'Lista de deseados',
            'items' => $request->user()->wishlistItems()->with('watch')->latest()->get(),
        ]);
    }

    public function add(Request $request, Watch $watch): RedirectResponse
    {
        $request->user()->wishlistItems()->firstOrCreate(['watch_id' => $watch->id]);

        return back()->with('success', 'Añadido a tu lista de deseados.');
    }

    public function remove(Request $request, Watch $watch): RedirectResponse
    {
        $request->user()->wishlistItems()->where('watch_id', $watch->id)->delete();

        return back()->with('success', 'Quitado de tu lista de deseados.');
    }
}
