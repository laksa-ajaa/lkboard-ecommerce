<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Semua route utama untuk halaman publik, auth, produk, cart, checkout,
| wishlist, akun, dan admin.
|
*/

// Home / Landing
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication
Route::get('/login', fn() => view('pages.auth.login'))->name('login');
Route::get('/register', fn() => view('pages.auth.register'))->name('register');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
Route::post('/logout', [LogoutController::class, 'store'])->name('logout');

// Search
Route::get('/api/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Products
Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/search', 'search')->name('search');
    Route::get('/category/{slug}', 'category')->name('category');
    Route::get('/{product}', 'show')->name('show');
});

// Cart (requires authentication)
Route::prefix('cart')->name('cart.')->middleware('auth')->controller(CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/add', 'add')->name('add');
    Route::put('/update/{id}', 'update')->name('update');
    Route::delete('/remove/{id}', 'remove')->name('remove');
    Route::delete('/clear', 'clear')->name('clear');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', fn() => view('pages.checkout.index'))->name('index');
    Route::get('/success', fn() => view('pages.checkout.success'))->name('success');
});

// Wishlist (requires authentication)
Route::prefix('wishlist')->name('wishlist.')->middleware('auth')->controller(WishlistController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/toggle', 'toggle')->name('toggle');
    Route::delete('/{id}', 'destroy')->name('destroy');
});

// Account
Route::prefix('account')->name('account.')->group(function () {
    Route::get('/', fn() => view('pages.account.index'))->name('index');
    Route::get('/settings', fn() => view('pages.account.settings'))->name('settings');

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', fn() => view('pages.account.transactions'))->name('index');
        Route::get('/{id}', fn($id) => view('pages.account.transaction-show', compact('id')))->name('show');
    });

    Route::get('/address', fn() => view('pages.account.address'))->name('address.index');
    Route::get('/tracking', fn() => view('pages.account.tracking'))->name('tracking');
});

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('pages.admin.dashboard'))->name('dashboard');
    Route::get('/products', fn() => view('pages.admin.products'))->name('products');
    Route::get('/orders', fn() => view('pages.admin.orders'))->name('orders');
    Route::get('/users', fn() => view('pages.admin.users'))->name('users');
});
