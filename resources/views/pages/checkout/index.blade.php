@extends('layouts.app')

@section('title', 'Checkout – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-0" x-data="{
        shippingMethods: {},
        paymentMethod: 'transfer',
        shippingCost: 15000,
        subtotal: {{ $subtotal }},
        addressData: {
            name: '{{ old('name', $savedAddress->name ?? $user->name) }}',
            email: '{{ old('email', $savedAddress->email ?? $user->email) }}',
            phone: '{{ old('phone', $savedAddress->phone ?? $user->phone) }}',
            address: '{{ old('address', $savedAddress->address ?? '') }}',
            city: '{{ old('city', $savedAddress->city ?? '') }}',
            province: '{{ old('province', $savedAddress->province ?? '') }}',
            postal_code: '{{ old('postal_code', $savedAddress->postal_code ?? '') }}'
        },
        showAddressModal: false,
        hasAddress: {{ ($savedAddress ?? null) ? 'true' : 'false' }},
        cartItems: @js($cartItems),
        loading: false,
        shippingCosts: {
            standard: 15000,
            express: 25000,
            jnt: 16000,
            sicepat: 18000
        },
        formatPrice(price) {
            return new Intl.NumberFormat('id-ID').format(price);
        },
        updateShippingCost() {
            let total = 0;
            for (let key in this.shippingMethods) {
                total += this.shippingCosts[this.shippingMethods[key]] || 15000;
            }
            this.shippingCost = total || 15000;
        },
        get total() {
            return this.subtotal + this.shippingCost;
        },
        openAddressModal() {
            this.showAddressModal = true;
        },
        closeAddressModal() {
            this.showAddressModal = false;
        },
        async saveAddress() {
            if (this.addressData.name && this.addressData.phone && this.addressData.address && this.addressData.city && this.addressData.province && this.addressData.postal_code) {
                try {
                    const response = await fetch('{{ route('checkout.save-address') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.addressData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.hasAddress = true;
                        this.closeAddressModal();
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat menyimpan alamat');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan alamat');
                }
            } else {
                alert('Mohon lengkapi semua field yang wajib diisi');
            }
        },
        async updateQuantity(itemId, newQuantity) {
            const item = this.cartItems.find(i => i.id === itemId);
            if (!item) return;
            
            newQuantity = parseInt(newQuantity);
            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
            }
            
            // Check stock limit
            if (newQuantity > item.stock) {
                alert('Stok tidak mencukupi. Stok tersedia: ' + item.stock);
                newQuantity = item.stock;
            }
            
            this.loading = true;
            try {
                const response = await fetch(`{{ route('checkout.update-quantity', ['id' => '__ID__']) }}`.replace('__ID__', itemId) + '?items=' + encodeURIComponent(JSON.stringify(this.cartItems.map(i => i.id))), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ quantity: newQuantity })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    item.quantity = data.quantity;
                    this.subtotal = data.subtotal;
                    // Trigger Alpine.js reactivity by updating cartItems reference
                    this.cartItems = [...this.cartItems];
                    // Dispatch event to update nested scopes
                    this.$dispatch('quantity-updated', { itemId, quantity: data.quantity });
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                    // Reload page to get latest data
                    if (data.message && data.message.includes('Stok')) {
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memperbarui jumlah');
            } finally {
                this.loading = false;
            }
        },
        init() {
            @foreach ($cartItems as $index => $item)
                this.shippingMethods[{{ $item['id'] }}] = 'standard';
            @endforeach
            this.updateShippingCost();
        }
    }" x-init="init()">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
            <span>/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-indigo-600">Keranjang</a>
            <span>/</span>
            <span class="text-slate-900 font-medium">Checkout</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Checkout</h1>
            <p class="mt-2 text-sm text-slate-600">Lengkapi informasi pengiriman dan pembayaran</p>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            
            {{-- Hidden inputs for address data from modal --}}
            <input type="hidden" name="name" :value="addressData.name || '{{ old('name', $savedAddress->name ?? $user->name) }}'" value="{{ old('name', $savedAddress->name ?? $user->name) }}" required>
            <input type="hidden" name="email" :value="addressData.email || '{{ old('email', $savedAddress->email ?? $user->email) }}'" value="{{ old('email', $savedAddress->email ?? $user->email) }}" required>
            <input type="hidden" name="phone" :value="addressData.phone || '{{ old('phone', $savedAddress->phone ?? $user->phone) }}'" value="{{ old('phone', $savedAddress->phone ?? $user->phone) }}" required>
            <input type="hidden" name="address" :value="addressData.address || '{{ old('address', $savedAddress->address ?? '') }}'" value="{{ old('address', $savedAddress->address ?? '') }}" required>
            <input type="hidden" name="city" :value="addressData.city || '{{ old('city', $savedAddress->city ?? '') }}'" value="{{ old('city', $savedAddress->city ?? '') }}" required>
            <input type="hidden" name="province" :value="addressData.province || '{{ old('province', $savedAddress->province ?? '') }}'" value="{{ old('province', $savedAddress->province ?? '') }}" required>
            <input type="hidden" name="postal_code" :value="addressData.postal_code || '{{ old('postal_code', $savedAddress->postal_code ?? '') }}'" value="{{ old('postal_code', $savedAddress->postal_code ?? '') }}" required>

            <div class="grid gap-8 lg:grid-cols-3">
                {{-- Left Column: Alamat & Ringkasan Pesanan --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Informasi Alamat Card (Minimalis) --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">Alamat Pengiriman</h2>
                            <button type="button" @click="openAddressModal()"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                <span x-show="!hasAddress">Tambah</span>
                                <span x-show="hasAddress" x-cloak>Edit</span>
                            </button>
                        </div>
                        
                        <div x-show="hasAddress" x-cloak class="space-y-2 text-sm text-slate-600">
                            <p class="font-medium text-slate-900" x-text="addressData.name"></p>
                            <p x-text="addressData.phone"></p>
                            <p x-text="addressData.address"></p>
                            <p x-text="addressData.city + ', ' + addressData.province + ' ' + addressData.postal_code"></p>
                        </div>
                        
                        <div x-show="!hasAddress" class="text-sm text-slate-500">
                            <p>Belum ada alamat. Klik "Tambah" untuk menambahkan alamat pengiriman.</p>
                        </div>
                    </div>

                    @foreach ($cartItems as $index => $item)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                            x-data="{ 
                                itemId: {{ $item['id'] }},
                                quantity: {{ $item['quantity'] }},
                                price: {{ $item['price'] }},
                                stock: {{ $item['stock'] }},
                                get totalPrice() {
                                    return this.price * this.quantity;
                                }
                            }"
                            @quantity-updated.window="if ($event.detail.itemId === itemId) { quantity = $event.detail.quantity; }">
                            {{-- Product Info --}}
                            <div class="mb-4 flex gap-4">
                                <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                    @if (!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-400">
                                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-slate-900">{{ $item['name'] }}</h3>
                                    @if (!empty($item['variant']))
                                        <p class="mt-1 text-sm text-slate-600">Varian: {{ $item['variant'] }}</p>
                                    @endif
                                    
                                    {{-- Quantity Selector --}}
                                    <div class="mt-3 flex items-center gap-2">
                                        <label class="text-sm font-medium text-slate-600">Jumlah:</label>
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                @click="updateQuantity(itemId, quantity - 1)"
                                                :disabled="quantity <= 1 || loading"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            <input type="number" 
                                                x-model.number="quantity"
                                                min="1"
                                                :max="stock"
                                                @change="updateQuantity(itemId, quantity)"
                                                @blur="updateQuantity(itemId, quantity)"
                                                class="h-8 w-16 rounded-lg border border-slate-200 bg-white px-2 text-center text-xs text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            <button type="button"
                                                @click="updateQuantity(itemId, quantity + 1)"
                                                :disabled="quantity >= stock || loading"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p class="mt-2 text-lg font-bold text-indigo-600">
                                        Rp <span x-text="formatPrice(totalPrice)"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- Ekspedisi Dropdown --}}
                            <div class="border-t border-slate-200 pt-4">
                                <label class="block text-sm font-medium text-slate-900 mb-2">
                                    Pilih Ekspedisi
                                </label>
                                <select name="shipping_method[{{ $item['id'] }}]" 
                                    x-model="shippingMethods[{{ $item['id'] }}]"
                                    @change="updateShippingCost()"
                                    required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="standard" selected>JNE Reguler - Rp 15.000 (3-5 hari)</option>
                                    <option value="express">JNE Express - Rp 25.000 (1-2 hari)</option>
                                    <option value="jnt">JNT - Rp 16.000 (3-5 hari)</option>
                                    <option value="sicepat">SiCepat - Rp 18.000 (2-4 hari)</option>
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Right Column: Metode Pembayaran & Ringkasan --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Metode Pembayaran --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-slate-900">Metode Pembayaran</h2>

                        <div class="space-y-3">
                            <label class="flex cursor-pointer items-center gap-4 rounded-lg border-2 border-slate-200 p-4 hover:border-indigo-300 transition-colors"
                                :class="paymentMethod === 'transfer' ? 'border-indigo-500 bg-indigo-50' : ''">
                                <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                        <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900">Transfer Bank</span>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-center gap-4 rounded-lg border-2 border-slate-200 p-4 hover:border-indigo-300 transition-colors"
                                :class="paymentMethod === 'cod' ? 'border-indigo-500 bg-indigo-50' : ''">
                                <input type="radio" name="payment_method" value="cod" x-model="paymentMethod"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                        <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900">Cash on Delivery (COD)</span>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-center gap-4 rounded-lg border-2 border-slate-200 p-4 hover:border-indigo-300 transition-colors"
                                :class="paymentMethod === 'ewallet' ? 'border-indigo-500 bg-indigo-50' : ''">
                                <input type="radio" name="payment_method" value="ewallet" x-model="paymentMethod"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                        <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900">E-Wallet</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Ringkasan Total --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-slate-900">Ringkasan</h2>

                        <div class="space-y-3 border-b border-slate-200 pb-4">
                            <div class="flex items-center justify-between text-sm text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-medium text-slate-900">
                                    Rp <span x-text="formatPrice(subtotal)"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-slate-600">
                                <span>Ongkos Kirim</span>
                                <span class="font-medium text-slate-900">
                                    Rp <span x-text="formatPrice(shippingCost)"></span>
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-lg font-semibold text-slate-900">Total</span>
                            <span class="text-xl font-extrabold text-indigo-600">
                                Rp <span x-text="formatPrice(total)"></span>
                            </span>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit"
                            class="mt-6 w-full rounded-lg bg-indigo-500 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                            Buat Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Modal Informasi Alamat --}}
        <div x-show="showAddressModal" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" 
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0">
            {{-- Backdrop --}}
            <div @click="closeAddressModal()" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            {{-- Modal Content --}}
            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto"
                @click.stop
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90">
                
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">Informasi Pengiriman</h3>
                    <button @click="closeAddressModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-2">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="addressData.name" required
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-2">
                                Nomor Telepon <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" x-model="addressData.phone" required
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-2">
                            Email <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" x-model="addressData.email" required
                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-2">
                            Alamat Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="addressData.address" rows="3" required
                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-2">
                                Kota <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="addressData.city" required
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-2">
                                Provinsi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="addressData.province" required
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-2">
                                Kode Pos <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="addressData.postal_code" required
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="closeAddressModal()"
                        class="flex-1 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="saveAddress()"
                        class="flex-1 rounded-lg bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-600 transition-colors shadow-sm shadow-indigo-500/40">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
