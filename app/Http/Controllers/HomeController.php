<?php

// Isabela Ruiz, Miguel Angel Rendon

namespace App\Http\Controllers;

use App\Models\Watch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home.index', [
            'title' => __('messages.home_title'),
            'subtitle' => __('messages.home_subtitle'),
            'featured' => Watch::latest('id')->take(4)->get(),
        ]);
    }
}
