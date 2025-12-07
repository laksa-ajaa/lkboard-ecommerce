<footer class="mt-8 border-t border-slate-800 bg-slate-950">
    <div class="container mx-auto px-4 py-10">
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 text-sm text-slate-300">
            {{-- Brand & Description --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('assets/img/logo.jpg') }}" alt="LKBoard Logo"
                        class="h-8 w-8 rounded-md object-cover">
                    <span class="text-sm font-semibold text-slate-50">LKBoard</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Toko resmi keyboard mechanical & custom gear. Koleksi keyboard, switch, keycaps, dan aksesoris
                    terpilih untuk setup terbaik.
                </p>
                <div class="flex flex-col gap-1 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <span>📍</span>
                        <span>Medan, Indonesia</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>🕒</span>
                        <span>Senin–Minggu 10.00–20.00 WIB</span>
                    </div>
                </div>
            </div>

            {{-- Produk & Kategori --}}
            <div>
                <h4 class="text-xs font-semibold text-slate-100 mb-3 uppercase tracking-wide">Produk</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('products.index') }}" class="hover:text-indigo-400 transition-colors">Semua
                            Produk</a></li>
                    <li><a href="{{ route('products.index') }}?category=keyboard"
                            class="hover:text-indigo-400 transition-colors">Keyboard</a></li>
                    <li><a href="{{ route('products.index') }}?category=switch"
                            class="hover:text-indigo-400 transition-colors">Switch</a></li>
                    <li><a href="{{ route('products.index') }}?category=keycaps"
                            class="hover:text-indigo-400 transition-colors">Keycaps</a></li>
                    <li><a href="{{ route('products.index') }}?category=aksesoris"
                            class="hover:text-indigo-400 transition-colors">Aksesoris</a></li>
                    @auth
                        <li><a href="{{ route('wishlist.index') }}" class="hover:text-indigo-400 transition-colors">Wishlist
                                Saya</a></li>
                    @endauth
                </ul>
            </div>

            {{-- Bantuan & Panduan --}}
            <div>
                <h4 class="text-xs font-semibold text-slate-100 mb-3 uppercase tracking-wide">Bantuan</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('home') }}#about" class="hover:text-indigo-400 transition-colors">Tentang
                            Kami</a></li>
                    <li><a href="{{ route('home') }}#about" class="hover:text-indigo-400 transition-colors">Kunjungi
                            Store</a></li>
                    <li><a href="{{ route('home') }}#about" class="hover:text-indigo-400 transition-colors">Cara Pilih
                            Switch</a></li>
                    <li><a href="{{ route('home') }}#about" class="hover:text-indigo-400 transition-colors">Panduan
                            Modding</a></li>
                    @auth
                        <li><a href="{{ route('account.transactions.index') }}"
                                class="hover:text-indigo-400 transition-colors">Lacak Pesanan</a></li>
                    @endauth
                </ul>
            </div>

            {{-- Kontak & Social --}}
            <div>
                <h4 class="text-xs font-semibold text-slate-100 mb-3 uppercase tracking-wide">Kontak</h4>
                <ul class="space-y-2 text-xs mb-4">
                    <li>
                        <a href="mailto:lkboard@laksaajaa.my.id"
                            class="hover:text-indigo-400 transition-colors flex items-center gap-2">
                            <span>📧</span>
                            <span>lkboard@laksaajaa.my.id</span>
                        </a>
                    </li>
                    <li>
                        <a href="tel:+628153518426"
                            class="hover:text-indigo-400 transition-colors flex items-center gap-2">
                            <span>📞</span>
                            <span>+62 815-3518-426</span>
                        </a>
                    </li>
                </ul>
                <div>
                    <h4 class="text-xs font-semibold text-slate-100 mb-3 uppercase tracking-wide">Ikuti Kami</h4>
                    <div class="flex gap-3">
                        <a href="https://web.facebook.com/laksmana.chairutama"
                            class="h-8 w-8 rounded-lg border border-slate-700 bg-slate-900/50 flex items-center justify-center hover:border-indigo-500 hover:bg-indigo-500/10 transition-colors text-sm"
                            aria-label="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/laksa_ajaa/"
                            class="h-8 w-8 rounded-lg border border-slate-700 bg-slate-900/50 flex items-center justify-center hover:border-indigo-500 hover:bg-indigo-500/10 transition-colors text-sm"
                            aria-label="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>
                        <a href="https://www.youtube.com/@laksa-ajaa"
                            class="h-8 w-8 rounded-lg border border-slate-700 bg-slate-900/50 flex items-center justify-center hover:border-indigo-500 hover:bg-indigo-500/10 transition-colors text-sm"
                            aria-label="YouTube">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Section --}}
        <div class="mt-8 pt-6 border-t border-slate-800">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-[11px] text-slate-500 text-center md:text-left">
                    &copy; {{ date('Y') }} {{ config('app.name', 'LKBoard') }}. All rights reserved.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-4 text-[11px] text-slate-500">
                    <a href="#" class="hover:text-indigo-400 transition-colors">Kebijakan Privasi</a>
                    <span class="text-slate-600">•</span>
                    <a href="#" class="hover:text-indigo-400 transition-colors">Syarat & Ketentuan</a>
                    <span class="text-slate-600">•</span>
                    <a href="#" class="hover:text-indigo-400 transition-colors">Kebijakan Pengembalian</a>
                </div>
            </div>
        </div>
    </div>
</footer>
