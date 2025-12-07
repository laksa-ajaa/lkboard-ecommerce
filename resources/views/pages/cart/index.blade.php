@extends('layouts.app')

@section('title', 'Keranjang Belanja – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-0"
        x-data="{
            cartItems: @js($cartItems),
            subtotal: {{ $subtotal }},
            total: {{ $subtotal }},
            itemCount: {{ count($cartItems) }},
            loading: false,
            selectedItems: [],
            selectAll: false,
            toggleSelectAll() {
                this.selectAll = !this.selectAll;
                if (this.selectAll) {
                    this.selectedItems = this.cartItems.map(item => item.id);
                } else {
                    this.selectedItems = [];
                }
            },
            toggleItem(itemId) {
                const index = this.selectedItems.indexOf(itemId);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                } else {
                    this.selectedItems.push(itemId);
                }
                this.selectAll = this.selectedItems.length === this.cartItems.length;
            },
            getSelectedSubtotal() {
                return this.cartItems
                    .filter(item => this.selectedItems.includes(item.id))
                    .reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },
            getSelectedCount() {
                return this.selectedItems.length;
            },
            async updateQuantity(itemId, newQuantity) {
                const item = this.cartItems.find(i => i.id === itemId);
                if (!item || newQuantity < 1 || newQuantity > item.stock) return;
                
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('cart.update', ['id' => '__ID__']) }}`.replace('__ID__', itemId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ quantity: newQuantity })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        item.quantity = newQuantity;
                        this.subtotal = data.subtotal;
                        this.total = data.subtotal;
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui jumlah');
                } finally {
                    this.loading = false;
                }
            },
            async removeItem(itemId) {
                if (!confirm('Yakin ingin menghapus item ini dari keranjang?')) return;
                
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('cart.remove', ['id' => '__ID__']) }}`.replace('__ID__', itemId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.cartItems = this.cartItems.filter(i => i.id !== itemId);
                        // Remove from selectedItems if it was selected
                        this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                        this.selectAll = this.selectedItems.length === this.cartItems.length && this.cartItems.length > 0;
                        this.subtotal = data.subtotal;
                        this.total = data.subtotal;
                        this.itemCount = data.itemCount;
                        
                        if (this.itemCount === 0) {
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus item');
                } finally {
                    this.loading = false;
                }
            },
            async clearCart() {
                if (!confirm('Yakin ingin mengosongkan keranjang?')) return;
                
                this.loading = true;
                try {
                    const response = await fetch('{{ route('cart.clear') }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengosongkan keranjang');
                } finally {
                    this.loading = false;
                }
            },
            formatPrice(price) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(price);
            },
            async removeSelectedItems() {
                if (this.selectedItems.length === 0) return;
                if (!confirm(`Yakin ingin menghapus ${this.selectedItems.length} item yang dipilih?`)) return;
                
                this.loading = true;
                try {
                    // Hapus semua item terpilih secara paralel
                    const promises = this.selectedItems.map(itemId => 
                        fetch(`{{ route('cart.remove', ['id' => '__ID__']) }}`.replace('__ID__', itemId), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                    );
                    
                    const responses = await Promise.all(promises);
                    const results = await Promise.all(responses.map(r => r.json()));
                    
                    // Check if all deletions were successful
                    const allSuccess = results.every(r => r.success);
                    
                    if (allSuccess) {
                        // Remove deleted items from cartItems
                        this.cartItems = this.cartItems.filter(item => !this.selectedItems.includes(item.id));
                        
                        // Clear selected items
                        this.selectedItems = [];
                        this.selectAll = false;
                        
                        // Recalculate subtotal
                        this.subtotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                        this.total = this.subtotal;
                        this.itemCount = this.cartItems.length;
                        
                        if (this.itemCount === 0) {
                            window.location.reload();
                        }
                    } else {
                        alert('Beberapa item gagal dihapus. Silakan coba lagi.');
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus item');
                    window.location.reload();
                } finally {
                    this.loading = false;
                }
            },
            goToCheckout() {
                if (this.selectedItems.length === 0) {
                    alert('Silakan pilih item terlebih dahulu');
                    return;
                }
                
                // Redirect to checkout with selected items as query parameter
                const selectedItemsParam = encodeURIComponent(JSON.stringify(this.selectedItems));
                window.location.href = '{{ route('checkout.index') }}?items=' + selectedItemsParam;
            }
        }">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-indigo-600">Produk</a>
            <span>/</span>
            <span class="text-slate-900 font-medium">Keranjang</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Keranjang Belanja</h1>
            <p class="mt-2 text-sm text-slate-600">Kelola item yang ingin Anda beli</p>
        </div>

        {{-- Empty Cart (Dynamic) --}}
        <div x-show="cartItems.length === 0" x-cloak style="display: {{ empty($cartItems) || count($cartItems) === 0 ? 'block' : 'none' }};">
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">Keranjang Anda kosong</h2>
                <p class="mt-2 text-sm text-slate-600">Mulai berbelanja dan tambahkan produk ke keranjang Anda</p>
                <a href="{{ route('products.index') }}"
                    class="mt-6 inline-flex items-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                    Mulai Berbelanja
                </a>
            </div>
        </div>

        @if (!empty($cartItems) && count($cartItems) > 0)
            <div x-show="cartItems.length > 0">
                <div class="grid gap-8 lg:grid-cols-12">
                {{-- Cart Items --}}
                <div class="lg:col-span-8">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        {{-- Cart Header --}}
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" 
                                            :checked="selectAll && cartItems.length > 0"
                                            @change="toggleSelectAll()"
                                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm font-semibold text-slate-900">Pilih Semua</span>
                                    </label>
                                    <span class="text-sm text-slate-600">
                                        (<span x-text="getSelectedCount()"></span> dipilih)
                                    </span>
                                </div>
                                <button type="button" 
                                    @click="removeSelectedItems()" 
                                    :disabled="loading || selectedItems.length === 0"
                                    class="text-xs font-medium text-rose-600 hover:text-rose-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        {{-- Cart Items List --}}
                        <div class="divide-y divide-slate-200">
                            <template x-for="(item, index) in cartItems" :key="index">
                                <div class="p-6">
                                    <div class="flex gap-4">
                                        {{-- Checkbox --}}
                                        <div class="flex-shrink-0 flex items-start pt-1">
                                            <input type="checkbox" 
                                                :checked="selectedItems.includes(item.id)"
                                                @change="toggleItem(item.id)"
                                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                        
                                        {{-- Product Image --}}
                                        <div class="flex-shrink-0">
                                            <a :href="item.product_url || '#'"
                                                class="block h-24 w-24 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                                <template x-if="item.image">
                                                    <img :src="item.image" :alt="item.name"
                                                        class="h-full w-full object-cover">
                                                </template>
                                                <template x-if="!item.image">
                                                    <div class="flex h-full w-full items-center justify-center text-slate-400">
                                                        <svg class="h-8 w-8" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </template>
                                            </a>
                                        </div>

                                        {{-- Product Info --}}
                                        <div class="flex flex-1 flex-col">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h3 class="text-sm font-semibold text-slate-900">
                                                        <a :href="item.product_url || '#'"
                                                            class="hover:text-indigo-600 transition-colors" x-text="item.name">
                                                        </a>
                                                    </h3>
                                                    <template x-if="item.variant">
                                                        <p class="mt-1 text-xs text-slate-600">Varian: <span x-text="item.variant"></span></p>
                                                    </template>
                                                    <template x-if="item.stock && item.stock > 0">
                                                        <p class="mt-1 text-xs text-slate-600">
                                                            Stok: <span x-text="item.stock"></span> tersedia
                                                        </p>
                                                    </template>
                                                </div>
                                                <button type="button" @click="removeItem(item.id)" :disabled="loading"
                                                    class="ml-4 flex-shrink-0 text-slate-400 hover:text-rose-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between">
                                                {{-- Quantity Selector --}}
                                                <div class="flex items-center gap-2">
                                                    <label class="text-xs font-medium text-slate-600">Jumlah:</label>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button"
                                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                                            :disabled="item.quantity <= 1 || loading"
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <input type="number" x-model="item.quantity" min="1"
                                                            :max="item.stock || 999"
                                                            @change="updateQuantity(item.id, item.quantity)"
                                                            @blur="updateQuantity(item.id, item.quantity)"
                                                            class="h-8 w-16 rounded-lg border border-slate-200 bg-white px-2 text-center text-xs text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                                        <button type="button"
                                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                                            :disabled="item.quantity >= (item.stock || 999) || loading"
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Price --}}
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-slate-900">
                                                        Rp <span x-text="formatPrice(item.quantity * item.price)"></span>
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        Rp <span x-text="formatPrice(item.price)"></span> per item
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Continue Shopping --}}
                    <div class="mt-6">
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Lanjutkan Belanja
                        </a>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-4">
                    <div class="sticky top-24">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="mb-4 text-lg font-semibold text-slate-900">Ringkasan Pesanan</h2>

                            <div class="space-y-3 border-b border-slate-200 pb-4">
                                <div class="flex items-center justify-between text-sm text-slate-600">
                                    <span>Subtotal (<span x-text="getSelectedCount()"></span> item)</span>
                                    <span class="font-medium text-slate-900">
                                        Rp <span x-text="formatPrice(getSelectedSubtotal())"></span>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-b border-slate-200 pb-4">
                                <span class="text-base font-semibold text-slate-900">Total</span>
                                <span class="text-xl font-extrabold text-indigo-600">
                                    Rp <span x-text="formatPrice(getSelectedSubtotal())"></span>
                                </span>
                            </div>

                            <div class="mt-6">
                                <a href="{{ route('checkout.index') }}" 
                                    @click.prevent="goToCheckout()"
                                    :class="{
                                        'block w-full rounded-lg px-4 py-3 text-center text-sm font-semibold transition-colors shadow-sm shadow-indigo-500/40': true,
                                        'bg-indigo-500 text-white hover:bg-indigo-400': selectedItems.length > 0,
                                        'bg-slate-300 text-slate-500 cursor-not-allowed': selectedItems.length === 0
                                    }">
                                    <span x-show="selectedItems.length > 0">Lanjutkan ke Checkout (<span x-text="getSelectedCount()"></span> item)</span>
                                    <span x-show="selectedItems.length === 0">Pilih item terlebih dahulu</span>
                                </a>
                            </div>

                            {{-- Trust Badges --}}
                            <div class="mt-6 space-y-2 border-t border-slate-200 pt-4 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Pembayaran aman</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Garansi resmi 1 tahun</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
