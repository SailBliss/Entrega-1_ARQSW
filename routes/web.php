<?php

use App\Http\Controllers\AdminWatchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/watches', [WatchController::class, 'index'])->name('watch.index');
Route::get('/watches/{watch}', [WatchController::class, 'show'])->name('watch.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{watch}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{watch}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/orders/{order}', [CartController::class, 'showOrder'])->name('orders.show');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add/{watch}', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{watch}', [WishlistController::class, 'remove'])->name('wishlist.remove');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/watches')->name('dashboard');
    Route::resource('watches', AdminWatchController::class)->except('show');
});
