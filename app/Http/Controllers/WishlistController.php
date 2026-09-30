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
            'title' => __('messages.wishlist_title'),
            'subtitle' => __('messages.wishlist_subtitle'),
            'items' => $request->user()->wishlistItems()->with('watch')->latest()->get(),
        ]);
    }

    public function add(Request $request, Watch $watch): RedirectResponse
    {
        $request->user()->wishlistItems()->firstOrCreate(['watch_id' => $watch->id]);

        return back()->with('success', __('messages.wishlist_added'));
    }

    public function remove(Request $request, Watch $watch): RedirectResponse
    {
        $request->user()->wishlistItems()->where('watch_id', $watch->id)->delete();

        return back()->with('success', __('messages.wishlist_removed'));
    }
}
