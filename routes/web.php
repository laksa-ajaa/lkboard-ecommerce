<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Semua route utama untuk halaman publik, auth, produk, cart, checkout,
| wishlist, akun, dan admin dasar. Untuk sekarang masih menggunakan
| closure yang langsung merender view.
*/

// Landing / homepage
Route::get('/', function () {
    $popularCategories = Category::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->take(6)
        ->get();

    $featuredProducts = Product::with(['category'])
        ->where('status', 'active')
        ->latest()
        ->take(8)
        ->get()
        ->map(function ($product) {
            $product->original_price = $product->compare_at_price;
            $product->discount = $product->compare_at_price && $product->compare_at_price > $product->price
                ? (int) round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100)
                : null;
            $product->image_url = $product->thumbnail;
            return $product;
        });

    return view('pages.landing', compact('popularCategories', 'featuredProducts'));
})->name('home');

// Auth (login & register berbasis view + controller)
Route::view('/login', 'pages.auth.login')->name('login');
Route::view('/register', 'pages.auth.register')->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home')->with('status', 'Kamu sudah keluar dari akun LKBoard.');
})->name('logout');

// Produk
Route::prefix('products')->name('products.')->group(function () {
    Route::view('/', 'pages.products.index')->name('index');               // katalog / daftar produk
    Route::view('/search', 'pages.products.search')->name('search');       // hasil pencarian
    Route::view('/category/{slug}', 'pages.products.category')->name('category'); // filter by category
    Route::view('/{product}', 'pages.products.show')->name('show');        // detail produk
});

// Cart
Route::view('/cart', 'pages.cart.index')->name('cart.index');

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::view('/', 'pages.checkout.index')->name('index');
    Route::view('/success', 'pages.checkout.success')->name('success');
});

// Wishlist
Route::view('/wishlist', 'pages.wishlist.index')->name('wishlist.index');

// Akun user
Route::prefix('account')->name('account.')->group(function () {
    Route::view('/', 'pages.account.index')->name('index'); // dashboard akun

    Route::view('/settings', 'pages.account.settings')->name('settings'); // pengaturan user

    Route::view('/transactions', 'pages.account.transactions')->name('transactions.index'); // riwayat pembelian
    Route::view('/transactions/{id}', 'pages.account.transaction-show')->name('transactions.show'); // detail transaksi

    Route::view('/address', 'pages.account.address')->name('address.index'); // daftar alamat user

    Route::view('/tracking', 'pages.account.tracking')->name('tracking'); // lacak pesanan
});

// Admin (opsional untuk masa depan)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'pages.admin.dashboard')->name('dashboard');
    Route::view('/products', 'pages.admin.products')->name('products');
    Route::view('/orders', 'pages.admin.orders')->name('orders');
    Route::view('/users', 'pages.admin.users')->name('users');
});

