<?php

namespace App\Http\Controllers;

use App\Models\Watch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home.index', [
            'title' => 'Inicio - Tienda Relojes',
            'subtitle' => 'Relojes para cada momento',
            'featured' => Watch::latest('id')->take(4)->get(),
        ]);
    }
}
