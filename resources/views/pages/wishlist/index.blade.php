@extends('layouts.app')

@section('title', 'Wishlist – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-0">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-indigo-600">Produk</a>
            <span>/</span>
            <span class="text-slate-900 font-medium">Wishlist</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Wishlist Saya</h1>
            <p class="mt-2 text-sm text-slate-600">Produk yang Anda simpan untuk dibeli nanti</p>
        </div>

        @if ($wishlistItems->total() === 0)
            {{-- Empty Wishlist --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">Wishlist Anda kosong</h2>
                <p class="mt-2 text-sm text-slate-600">Mulai menambahkan produk ke wishlist Anda</p>
                <a href="{{ route('products.index') }}"
                    class="mt-6 inline-flex items-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                    Jelajahi Produk
                </a>
            </div>
        @else
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
                                    ]);
                                @endphp
                                @if ($hasActiveFilters)
                                    <a href="{{ route('wishlist.index') }}"
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
                                <a href="{{ route('wishlist.index', request()->except('category')) }}"
                                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ !request('category') ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ !request('category') ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                                    Semua Kategori
                                </a>
                                @foreach ($categories as $category)
                                    @if ($category->products_count > 0)
                                        <a href="{{ route('wishlist.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                                            class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ request('category') === $category->slug ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="h-1.5 w-1.5 rounded-full {{ request('category') === $category->slug ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                                                {{ $category->name }}
                                            </div>
                                            <span class="text-[10px] text-slate-400">{{ $category->products_count }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Filter Card: Rentang Harga --}}
                        <div
                            class="rounded-2xl border border-slate-200 bg-white/70 p-5 shadow-sm shadow-slate-200/60 backdrop-blur">
                            <h3 class="text-sm font-semibold text-slate-900 mb-4">Rentang Harga</h3>
                            <form method="GET" action="{{ route('wishlist.index') }}" class="space-y-4">
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
                                    <a href="{{ route('wishlist.index', request()->except(['min_price', 'max_price', 'page'])) }}"
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
                                <a href="{{ route('wishlist.index', request()->except(['in_stock', 'page'])) }}"
                                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ !request('in_stock') ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ !request('in_stock') ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                                    Semua Produk
                                </a>
                                <a href="{{ route('wishlist.index', array_merge(request()->except('page'), ['in_stock' => '1'])) }}"
                                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-colors {{ request('in_stock') === '1' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ request('in_stock') === '1' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    Tersedia (Stok > 0)
                                </a>
                            </div>
                        </div>

                    </div>
                </aside>

                {{-- ========== WISHLIST GRID (RIGHT) ========== --}}
                <div class="flex-1 min-w-0">
                    {{-- Toolbar: Sort & Results Count --}}
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-slate-600">
                            Menampilkan <span class="font-semibold text-slate-900">{{ $wishlistItems->total() }}</span> produk
                        </div>
                        <form method="GET" action="{{ route('wishlist.index') }}" class="flex items-center gap-2">
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

                    {{-- Wishlist Grid --}}
                    @if ($wishlistItems->count() > 0)
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($wishlistItems as $item)
                                <div
                                    class="group flex flex-col rounded-2xl border border-slate-200 bg-white overflow-hidden hover:-translate-y-1 hover:border-indigo-200 hover:shadow-[0_18px_55px_rgba(15,23,42,0.18)] transition-all duration-300">
                                    <a href="{{ $item['product_url'] }}"
                                        class="relative block aspect-[4/3] bg-slate-900 overflow-hidden">
                                        @if (!empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                                class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center text-xs text-slate-500">
                                                <svg class="h-12 w-12 text-slate-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif

                                        @if (!empty($item['stock']) && $item['stock'] > 0)
                                            <div class="absolute right-3 bottom-3">
                                                <span
                                                    class="rounded-full bg-slate-950/70 px-2 py-1 text-[10px] text-slate-100 border border-slate-700/70 backdrop-blur">
                                                    Stok: {{ $item['stock'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="flex flex-1 flex-col px-3 pt-3 pb-3">
                                        @if (!empty($item['category']))
                                            <p class="text-[11px] text-slate-500 mb-1">{{ $item['category'] }}</p>
                                        @endif
                                        <a href="{{ $item['product_url'] }}"
                                            class="text-sm font-semibold text-slate-900 line-clamp-2 tracking-tight hover:text-indigo-600 transition-colors">
                                            {{ $item['name'] }}
                                        </a>

                                        <div class="mt-3">
                                            <p class="text-sm font-semibold text-indigo-600">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </p>
                                            @if (!empty($item['original_price']) && $item['original_price'] > $item['price'])
                                                <p class="text-[11px] text-slate-500 line-through">
                                                    Rp {{ number_format($item['original_price'], 0, ',', '.') }}
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Actions --}}
                                        <div class="mt-3 flex gap-2">
                                            <a href="{{ $item['product_url'] }}"
                                                class="flex-1 rounded-lg bg-indigo-500 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-indigo-400 transition-colors">
                                                Lihat Detail
                                            </a>
                                            <button type="button"
                                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                                                onclick="removeFromWishlist({{ $item['id'] }})">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if ($wishlistItems->hasPages())
                            <div class="mt-8">
                                {{ $wishlistItems->links() }}
                            </div>
                        @endif
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-white/70 p-12 text-center shadow-sm">
                            <p class="text-sm font-medium text-slate-600">Tidak ada produk yang ditemukan</p>
                            <p class="mt-2 text-xs text-slate-500">Coba ubah filter atau hapus beberapa kriteria pencarian</p>
                            <a href="{{ route('wishlist.index') }}"
                                class="mt-4 inline-flex items-center rounded-lg bg-indigo-500 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-400 transition-colors">
                                Lihat Semua Wishlist
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function removeFromWishlist(wishlistId) {
                if (!confirm('Yakin ingin menghapus produk ini dari wishlist?')) return;

                fetch(`{{ route('wishlist.destroy', ['id' => '__ID__']) }}`.replace('__ID__', wishlistId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat menghapus dari wishlist');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus dari wishlist');
                    });
            }
        </script>
    @endpush
@endsection
