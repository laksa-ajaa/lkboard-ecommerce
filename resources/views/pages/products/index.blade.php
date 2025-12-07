@extends('layouts.app')

@section('title', 'Katalog Produk – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-0">
        {{-- Header --}}
        <div class="mb-8">
            @if ($searchQuery ?? null)
                <div class="mb-4 flex items-center gap-2">
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center text-xs text-slate-500 hover:text-slate-700">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke semua produk
                    </a>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900">
                    Hasil Pencarian: "{{ $searchQuery }}"
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Menampilkan produk yang sesuai dengan kata kunci pencarian
                </p>
            @elseif (isset($category))
                <div class="mb-4 flex items-center gap-2">
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center text-xs text-slate-500 hover:text-slate-700">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke semua produk
                    </a>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900">
                    Kategori: {{ $category->name }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $category->description ?? 'Menampilkan produk dalam kategori ini' }}
                </p>
            @else
                <h1 class="text-3xl font-extrabold text-slate-900">Katalog Produk</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Temukan keyboard mechanical dan aksesoris pilihan untuk setup impianmu
                </p>
            @endif
        </div>

        {{-- Main Content: Filter Sidebar + Product Grid --}}
        <div class="flex flex-col gap-6 lg:flex-row">
            {{-- ========== FILTER SIDEBAR (LEFT) ========== --}}
            <aside class="w-full lg:w-80 flex-shrink-0">
                <div class="sticky top-24 space-y-4">
                    {{-- Filter Card: Kategori --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-white/70 p-5 shadow-sm shadow-slate-200/60 backdrop-blur">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">Kategori</h3>
                            @php
                                $hasActiveFilters = request()->hasAny([
                                    'category',
                                    'min_price',
                                    'max_price',
                                    'in_stock',
                                    'sort',
                                    'q',
                                ]);
                            @endphp
                            @if ($hasActiveFilters)
                                <a href="{{ route('products.index') }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-600 hover:bg-slate-50 hover:border-indigo-300 transition-colors"
                                    title="Reset semua filter">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset
                                </a>
                            @else
                                <button type="button" disabled
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-medium text-slate-400 cursor-not-allowed"
                                    title="Tidak ada filter aktif">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset
                                </button>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('products.index', request()->except('category')) }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ !request('category') ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span
                                    class="h-1.5 w-1.5 rounded-full {{ !request('category') ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                                Semua Kategori
                            </a>
                            @foreach ($categories as $category)
                                <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                                    class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ request('category') === $category->slug ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-1.5 w-1.5 rounded-full {{ request('category') === $category->slug ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                                        {{ $category->name }}
                                    </div>
                                    @if ($category->products_count > 0)
                                        <span class="text-[10px] text-slate-400">{{ $category->products_count }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filter Card: Rentang Harga --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-white/70 p-5 shadow-sm shadow-slate-200/60 backdrop-blur">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">Rentang Harga</h3>
                        <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
                            @foreach (request()->except(['min_price', 'max_price', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="min_price" class="block text-[11px] text-slate-500 mb-1">Min (Rp)</label>
                                    <input type="number" id="min_price" name="min_price"
                                        value="{{ request('min_price') }}" min="0" placeholder="Min"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </div>
                                <div>
                                    <label for="max_price" class="block text-[11px] text-slate-500 mb-1">Max (Rp)</label>
                                    <input type="number" id="max_price" name="max_price"
                                        value="{{ request('max_price') }}" min="0" placeholder="Max"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full rounded-lg bg-indigo-500 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-400 transition-colors">
                                Terapkan
                            </button>
                            @if (request('min_price') || request('max_price'))
                                <a href="{{ route('products.index', request()->except(['min_price', 'max_price', 'page'])) }}"
                                    class="block w-full text-center rounded-lg border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    {{-- Filter Card: Stok --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-white/70 p-5 shadow-sm shadow-slate-200/60 backdrop-blur">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">Ketersediaan</h3>
                        <div class="space-y-2">
                            <a href="{{ route('products.index', request()->except(['in_stock', 'page'])) }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ !request('in_stock') ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span
                                    class="h-1.5 w-1.5 rounded-full {{ !request('in_stock') ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                                Semua Produk
                            </a>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['in_stock' => '1'])) }}"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ request('in_stock') === '1' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span
                                    class="h-1.5 w-1.5 rounded-full {{ request('in_stock') === '1' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                Tersedia (Stok > 0)
                            </a>
                        </div>
                    </div>

                </div>
            </aside>

            {{-- ========== PRODUCT GRID (RIGHT) ========== --}}
            <div class="flex-1 min-w-0">
                {{-- Toolbar: Sort & Results Count --}}
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-slate-600">
                        Menampilkan <span class="font-semibold text-slate-900">{{ $products->total() }}</span> produk
                    </div>
                    <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="sort" class="text-xs text-slate-600">Urutkan:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="" {{ !request('sort') ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Harga:
                                Rendah
                                ke Tinggi</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Harga:
                                Tinggi ke Rendah</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama: A-Z
                            </option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nama: Z-A
                            </option>
                        </select>
                    </form>
                </div>

                {{-- Product Grid --}}
                @if ($products->count() > 0)
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($products->hasPages())
                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-12 text-center shadow-sm">
                        <p class="text-sm font-medium text-slate-600">Tidak ada produk yang ditemukan</p>
                        <p class="mt-2 text-xs text-slate-500">Coba ubah filter atau hapus beberapa kriteria pencarian</p>
                        <a href="{{ route('products.index') }}"
                            class="mt-4 inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-400 transition-colors">
                            Lihat Semua Produk
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
