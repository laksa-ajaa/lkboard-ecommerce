@extends('layouts.app')

@section('title', $productModel->name . ' – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-0">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-indigo-600">Produk</a>
            @if ($productModel->category)
                <span>/</span>
                <a href="{{ route('products.category', $productModel->category->slug) }}" class="hover:text-indigo-600">
                    {{ $productModel->category->name }}
                </a>
            @endif
            <span>/</span>
            <span class="text-slate-900 font-medium">{{ $productModel->name }}</span>
        </nav>

        {{-- Product Detail --}}
        <div class="grid gap-8 lg:grid-cols-12" x-data="{
            selectedVariant: null,
            quantity: 1,
            showSuccessModal: false,
            showErrorAlert: false,
            showLoginModal: false,
            isAuthenticated: {{ auth()->check() ? 'true' : 'false' }},
            isInWishlist: {{ $isInWishlist ? 'true' : 'false' }},
            isTogglingWishlist: false,
            variants: [
                @foreach ($productModel->variants as $variant)
                    { id: {{ $variant->id }}, stock: {{ $variant->stock }}, name: '{{ $variant->name }}' }, @endforeach
            ],
            getMaxStock() {
                if (!this.selectedVariant) return {{ $productModel->stock }};
                const variant = this.variants.find(v => v.id == this.selectedVariant);
                return variant ? variant.stock : {{ $productModel->stock }};
            },
            validateQuantity() {
                if (this.quantity < 1) {
                    this.quantity = 1;
                } else if (this.quantity > this.getMaxStock()) {
                    this.quantity = this.getMaxStock();
                }
            },
            toggleWishlist() {
                // Cek apakah user sudah login
                if (!this.isAuthenticated) {
                    this.showLoginModal = true;
                    return;
                }
        
                // Prevent multiple simultaneous requests
                if (this.isTogglingWishlist) return;
        
                this.isTogglingWishlist = true;
        
                const formData = {
                    product_id: {{ $productModel->id }},
                    _token: '{{ csrf_token() }}'
                };
        
                fetch('{{ route('wishlist.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(async response => {
                        // Handle 401/403 (Unauthorized) - show login modal
                        if (response.status === 401 || response.status === 403) {
                            this.showLoginModal = true;
                            this.isTogglingWishlist = false;
                            return;
                        }
        
                        // Check if response is ok
                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({}));
                            alert(errorData.message || 'Terjadi kesalahan saat mengubah wishlist');
                            this.isTogglingWishlist = false;
                            return;
                        }
        
                        const data = await response.json();
                        if (data.success) {
                            this.isInWishlist = data.in_wishlist;
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat mengubah wishlist');
                        }
                        this.isTogglingWishlist = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengubah wishlist. Silakan coba lagi.');
                        this.isTogglingWishlist = false;
                    });
            },
            addToCart() {
                // Cek apakah user sudah login
                if (!this.isAuthenticated) {
                    this.showLoginModal = true;
                    return;
                }
        
                // Validasi varian harus dipilih (jika produk memiliki varian)
                const hasVariants = {{ $productModel->variants->count() > 0 ? 'true' : 'false' }};
                if (hasVariants && !this.selectedVariant) {
                    this.showErrorAlert = true;
                    setTimeout(() => { this.showErrorAlert = false; }, 3000);
                    return;
                }
        
                // Kirim data ke server
                const formData = {
                    product_id: {{ $productModel->id }},
                    variant_id: this.selectedVariant,
                    quantity: this.quantity,
                    _token: '{{ csrf_token() }}'
                };
        
                fetch('{{ route('cart.add') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(async response => {
                        // Handle 401/403 (Unauthorized) - show login modal
                        if (response.status === 401 || response.status === 403) {
                            this.showLoginModal = true;
                            return;
                        }
        
                        // Check if response is ok
                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({}));
                            alert(errorData.message || 'Terjadi kesalahan saat menambahkan ke keranjang');
                            return;
                        }
        
                        const data = await response.json();
                        if (data.success) {
                            this.showSuccessModal = true;
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat menambahkan ke keranjang');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Network error or other issues
                        alert('Terjadi kesalahan saat menambahkan ke keranjang. Silakan coba lagi.');
                    });
            },
            buyNow() {
                // Cek apakah user sudah login
                if (!this.isAuthenticated) {
                    this.showLoginModal = true;
                    return;
                }
        
                // Validasi varian harus dipilih (jika produk memiliki varian)
                const hasVariants = {{ $productModel->variants->count() > 0 ? 'true' : 'false' }};
                if (hasVariants && !this.selectedVariant) {
                    this.showErrorAlert = true;
                    setTimeout(() => { this.showErrorAlert = false; }, 3000);
                    return;
                }
        
                // Langsung redirect ke buy now checkout (tidak menggunakan cart)
                const params = new URLSearchParams({
                    product_id: {{ $productModel->id }},
                    quantity: this.quantity
                });
                if (this.selectedVariant) {
                    params.append('variant_id', this.selectedVariant);
                }
                window.location.href = '{{ route('checkout.buy-now') }}?' + params.toString();
            }
        }" x-init="$watch('selectedVariant', () => { validateQuantity(); })">

            {{-- Alert Varian Belum Dipilih --}}
            <div x-show="showErrorAlert" x-cloak
                class="fixed top-4 right-4 z-50 max-w-md rounded-lg bg-rose-50 border border-rose-200 p-4 shadow-lg"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-rose-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-rose-900">Pilih Varian Terlebih Dahulu</h3>
                        <p class="mt-1 text-xs text-rose-700">Silakan pilih varian produk sebelum menambahkan ke keranjang.
                        </p>
                    </div>
                    <button @click="showErrorAlert = false" class="ml-auto text-rose-400 hover:text-rose-600">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Success --}}
            <div x-show="showSuccessModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                {{-- Backdrop --}}
                <div @click="showSuccessModal = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                {{-- Modal Content --}}
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-90"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-90">

                    {{-- Icon Success --}}
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 mb-4">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 text-center mb-2">Berhasil Ditambahkan!</h3>
                    <p class="text-sm text-slate-600 text-center mb-6">Produk telah ditambahkan ke keranjang belanja Anda.
                    </p>

                    {{-- Product Info --}}
                    <div class="bg-slate-50 rounded-lg p-3 mb-6">
                        <div class="flex items-center gap-3">
                            @if ($productModel->image_url)
                                <img src="{{ $productModel->image_url }}" alt="{{ $productModel->name }}"
                                    class="w-16 h-16 rounded-lg object-cover">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-slate-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ $productModel->name }}</p>
                                <p class="text-xs text-slate-500" x-show="selectedVariant">
                                    <span x-text="variants.find(v => v.id == selectedVariant)?.name"></span>
                                </p>
                                <p class="text-xs text-slate-500">Jumlah: <span x-text="quantity"></span></p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button @click="showSuccessModal = false"
                            class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Lanjut Belanja
                        </button>
                        <a href="{{ route('cart.index') }}"
                            class="flex-1 rounded-lg bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-600 transition-colors text-center shadow-sm shadow-indigo-500/40">
                            Lihat Keranjang
                        </a>
                    </div>
                </div>
            </div>

            {{-- Modal Login Required --}}
            <div x-show="showLoginModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                {{-- Backdrop --}}
                <div @click="showLoginModal = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                {{-- Modal Content --}}
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-90"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-90">

                    {{-- Icon --}}
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 text-center mb-2">Login Diperlukan</h3>
                    <p class="text-sm text-slate-600 text-center mb-6">
                        Anda perlu masuk terlebih dahulu untuk menambahkan produk ke keranjang.
                    </p>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button @click="showLoginModal = false"
                            class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Nanti Saja
                        </button>
                        <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}"
                            class="flex-1 rounded-lg bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-600 transition-colors text-center shadow-sm shadow-indigo-500/40">
                            Masuk
                        </a>
                    </div>
                </div>
            </div>

            {{-- ========== GAMBAR (LEFT) ========== --}}
            <div class="lg:col-span-4">
                <div class="sticky top-24 self-start">
                    <div class="relative rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                        @if ($productModel->image_url)
                            <img src="{{ $productModel->image_url }}" alt="{{ $productModel->name }}"
                                class="w-full h-auto object-cover aspect-square">
                        @else
                            <div class="w-full aspect-square bg-slate-100 flex items-center justify-center text-slate-400">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif

                        @if ($productModel->badge || $productModel->discount)
                            <div class="absolute top-4 left-4 flex gap-2">
                                @if ($productModel->badge)
                                    <x-badge variant="primary">{{ $productModel->badge }}</x-badge>
                                @endif
                                @if ($productModel->discount)
                                    <x-badge variant="danger">-{{ $productModel->discount }}%</x-badge>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ========== JUDUL, VARIAN, DESKRIPSI (MIDDLE) ========== --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- Judul & Kategori --}}
                <div>
                    @if ($productModel->category)
                        <a href="{{ route('products.category', $productModel->category->slug) }}"
                            class="inline-block text-xs font-medium text-indigo-600 hover:text-indigo-700 mb-2">
                            {{ $productModel->category->name }}
                        </a>
                    @endif
                    <h1 class="text-3xl font-extrabold text-slate-900 mb-2">{{ $productModel->name }}</h1>
                    @if ($productModel->short_description)
                        <p class="text-sm text-slate-600">{{ $productModel->short_description }}</p>
                    @endif
                </div>

                {{-- Pilihan Varian --}}
                @if ($productModel->variants->count() > 0)
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-slate-900">
                            Pilih Varian <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($productModel->variants as $variant)
                                <button type="button" @click="selectedVariant = {{ $variant->id }}"
                                    class="px-4 py-2 rounded-lg border-2 cursor-pointer transition-colors"
                                    :class="selectedVariant == {{ $variant->id }} ?
                                        'border-indigo-500 bg-indigo-50 text-indigo-700' :
                                        'border-slate-200 hover:border-slate-300 text-slate-700'">
                                    <span class="text-sm font-medium">{{ $variant->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Wishlist Button --}}
                <div>
                    <button type="button" @click="toggleWishlist()" :disabled="isTogglingWishlist"
                        class="group relative inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="isInWishlist ?
                            'border-rose-500 bg-rose-50 text-rose-600 hover:bg-rose-100' :
                            'border-indigo-500 bg-white text-indigo-600 hover:bg-indigo-50'"
                        :title="isInWishlist ? 'Hapus Wishlist' : 'Tambah ke Wishlist'">
                        {{-- Empty Heart Icon --}}
                        <svg x-show="!isInWishlist" class="h-5 w-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        {{-- Filled Heart Icon --}}
                        <svg x-show="isInWishlist" x-cloak class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span x-show="!isInWishlist">Tambah ke Wishlist</span>
                        <span class="group-hover:hidden" x-show="isInWishlist" x-cloak>Wishlist</span>
                        <span class="hidden group-hover:inline" x-show="isInWishlist" x-cloak>Hapus Wishlist</span>
                    </button>
                </div>

                {{-- Deskripsi --}}
                @if ($productModel->description)
                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold text-slate-900">Deskripsi Produk</h2>
                        <div class="prose prose-sm max-w-none text-slate-600">
                            {!! nl2br(e($productModel->description)) !!}
                        </div>
                    </div>
                @endif
            </div>

            {{-- ========== CARD JUMLAH DAN HARGA (RIGHT) ========== --}}
            <div class="lg:col-span-3">
                <div class="sticky top-24 self-start">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        {{-- Harga --}}
                        <div class="mb-6">
                            <div class="flex items-baseline gap-2">
                                @if ($productModel->variants->count() > 0)
                                    @foreach ($productModel->variants as $variant)
                                        @php
                                            $displayPrice =
                                                $variant->price !== null ? $variant->price : $productModel->price;
                                        @endphp
                                        <span x-show="selectedVariant == {{ $variant->id }}"
                                            class="text-3xl font-extrabold text-indigo-600">
                                            Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                        </span>
                                    @endforeach
                                @endif
                                <span x-show="selectedVariant == null" class="text-3xl font-extrabold text-indigo-600">
                                    Rp {{ number_format($productModel->price, 0, ',', '.') }}
                                </span>
                            </div>
                            @if ($productModel->variants->count() > 0)
                                @foreach ($productModel->variants as $variant)
                                    @php
                                        $variantPrice = $variant->price ?? $productModel->price;
                                        $variantComparePrice =
                                            $variant->compare_at_price ?? $productModel->compare_at_price;
                                        $variantDiscount =
                                            $variantComparePrice && $variantComparePrice > $variantPrice
                                                ? (int) round(
                                                    (($variantComparePrice - $variantPrice) / $variantComparePrice) *
                                                        100,
                                                )
                                                : null;
                                    @endphp
                                    <div x-show="selectedVariant == {{ $variant->id }}" x-cloak>
                                        @if ($variantComparePrice && $variantComparePrice > $variantPrice)
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="text-sm text-slate-500 line-through">
                                                    Rp {{ number_format($variantComparePrice, 0, ',', '.') }}
                                                </span>
                                                @if ($variantDiscount)
                                                    <x-badge variant="danger">-{{ $variantDiscount }}%</x-badge>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                            <div x-show="!selectedVariant">
                                @if ($productModel->original_price && $productModel->original_price > $productModel->price)
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-sm text-slate-500 line-through">
                                            Rp {{ number_format($productModel->original_price, 0, ',', '.') }}
                                        </span>
                                        @if ($productModel->discount)
                                            <x-badge variant="danger">-{{ $productModel->discount }}%</x-badge>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Stok Varian --}}
                        @if ($productModel->variants->count() > 0)
                            <div class="mb-6">
                                @foreach ($productModel->variants as $variant)
                                    <div x-show="selectedVariant == {{ $variant->id }}">
                                        @if ($variant->stock > 0)
                                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                <span>Tersedia ({{ $variant->stock }} stok)</span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-sm text-rose-600">
                                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                                <span>Stok habis</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                <div x-show="selectedVariant == null">
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <span>Pilih varian untuk melihat stok</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Jumlah & Tombol --}}
                        <div class="space-y-4">
                            @if ($productModel->variants->count() > 0)
                                <div>
                                    <label class="block text-sm font-semibold text-slate-900 mb-2">Varian</label>
                                    <div class="text-sm text-slate-600">
                                        @foreach ($productModel->variants as $variant)
                                            <div x-show="selectedVariant == {{ $variant->id }}">
                                                <p class="font-medium text-slate-900">{{ $variant->name }}</p>
                                                <p class="text-xs text-slate-500">Stok: {{ $variant->stock }}</p>
                                            </div>
                                        @endforeach
                                        <p x-show="selectedVariant == null" class="text-slate-500">Pilih varian</p>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-2">Jumlah</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if(quantity > 1) quantity--" :disabled="quantity <= 1"
                                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <input type="number" x-model="quantity" min="1" :max="getMaxStock()"
                                        @input="validateQuantity()"
                                        class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <button type="button" @click="if(quantity < getMaxStock()) quantity++"
                                        :disabled="quantity >= getMaxStock()"
                                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <button type="button" @click="addToCart()"
                                    class="w-full rounded-lg bg-indigo-500 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40 cursor-pointer">
                                    Tambah ke Keranjang
                                </button>
                                <button type="button" @click="buyNow()"
                                    class="w-full rounded-lg border border-indigo-500 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-100 transition-colors cursor-pointer">
                                    Beli Langsung
                                </button>
                            </div>

                            {{-- Info Tambahan --}}
                            <div class="pt-4 border-t border-slate-200 space-y-2 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Garansi resmi 1 tahun</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Pengiriman ke seluruh Indonesia</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Support customer service 24/7</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Produk Serupa --}}
        @if (isset($similarProducts) && $similarProducts->count() > 0)
            <div class="mt-16">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-2xl font-extrabold text-slate-900">Produk Serupa</h2>
                    @if ($productModel->category)
                        <a href="{{ route('products.category', $productModel->category->slug) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-500/5 px-4 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 transition-colors">
                            Lihat semua di kategori ini
                        </a>
                    @endif
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similarProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
