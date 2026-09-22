<?php

namespace App\Http\Controllers;

use App\Models\Watch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');

        return view('watch.index', [
            'title' => 'Relojes - Tienda Relojes',
            'subtitle' => $q ? 'Resultados para "'.$q.'"' : 'Catálogo de relojes',
            'q' => $q,
            'watches' => Watch::search($q)->orderBy('brand')->orderBy('name')->get(),
            'wishlistIds' => $request->user()?->wishlistItems()->pluck('watch_id')->all() ?? [],
        ]);
    }

    public function show(Request $request, Watch $watch): View
    {
        return view('watch.show', [
            'title' => $watch->name.' - Tienda Relojes',
            'subtitle' => $watch->name,
            'watch' => $watch,
            'inWishlist' => $request->user()?->wishlistItems()->where('watch_id', $watch->id)->exists() ?? false,
        ]);
    }
}
