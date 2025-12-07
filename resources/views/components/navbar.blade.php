<header class="fixed inset-x-0 top-0 z-40 border-b border-slate-800/30 bg-slate-950/90 backdrop-blur">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16 gap-4">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('assets/img/logo.jpg') }}" alt="LKBoard Logo" class="h-8 w-8 rounded-md object-cover shadow-sm shadow-indigo-500/50">
                <div class="flex flex-col">
                    <span class="text-sm font-semibold tracking-tight text-slate-50">
                        LKBoard
                    </span>
                    <span class="text-[10px] uppercase tracking-[0.18em] text-slate-400">
                        Mechanical Keyboards
                    </span>
                </div>
            </a>

            {{-- Center nav (desktop) --}}
            <nav class="hidden md:flex items-center gap-6 text-xs font-medium text-slate-200">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-1 border-b-2 pb-1 transition-colors border-transparent hover:border-indigo-400 hover:text-white @if (request()->routeIs('home')) border-indigo-400 text-white @endif">
                    <span>Home</span>
                </a>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-1 border-b-2 pb-1 transition-colors border-transparent hover:border-indigo-400 hover:text-white @if (request()->routeIs('products.*')) border-indigo-400 text-white @endif">
                    <span>Produk</span>
                </a>
                @auth
                    <a href="{{ route('cart.index') }}"
                        class="inline-flex items-center gap-1 border-b-2 pb-1 transition-colors border-transparent hover:border-indigo-400 hover:text-white @if (request()->routeIs('cart.*')) border-indigo-400 text-white @endif">
                        <span>Keranjang Saya</span>
                    </a>
                @else
                    <button type="button" @click="showLoginModal = true"
                        class="inline-flex items-center gap-1 border-b-2 pb-1 transition-colors border-transparent hover:border-indigo-400 hover:text-white">
                        <span>Keranjang Saya</span>
                    </button>
                @endauth
                <a href="#about"
                    class="inline-flex items-center gap-1 border-b-2 pb-1 transition-colors border-transparent hover:border-indigo-400 hover:text-white @if (request()->routeIs('login')) border-indigo-400 text-white @endif">
                    <span>Tentang Kami</span>
                </a>
            </nav>

            {{-- Search (desktop) --}}
            <div x-data="searchDropdown()" class="hidden lg:flex flex-1 max-w-md mx-2 relative">
                <form action="{{ route('products.index') }}" method="GET" class="relative flex-1">
                    <label class="relative flex-1">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500 text-xs">
                            🔍
                        </span>
                        <input type="text" name="q" placeholder="Cari keyboard, switch, atau keycaps..."
                            x-model="query"
                            @input.debounce.300ms="search()"
                            @focus="showDropdown = true"
                            @blur="setTimeout(() => showDropdown = false, 200)"
                            @keydown.enter.prevent="submitSearch()"
                            class="w-full rounded-lg border border-slate-300 bg-white/95 pl-8 pr-4 py-1.5 text-xs text-slate-900 placeholder:text-slate-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            value="{{ request('q') }}" autocomplete="off">
                    </label>
                </form>

                {{-- Dropdown Suggestions --}}
                <div x-show="showDropdown && (suggestions.products.length > 0 || suggestions.categories.length > 0)"
                    x-cloak
                    x-transition
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
                    <template x-if="suggestions.categories.length > 0">
                        <div class="p-2 border-b border-slate-100">
                            <p class="px-2 py-1 text-[10px] font-semibold text-slate-500 uppercase">Kategori</p>
                            <template x-for="category in suggestions.categories" :key="category.id">
                                <a :href="`{{ url('/products/category') }}/${category.slug}`"
                                    class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-50 text-xs text-slate-700">
                                    <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span x-text="category.name"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="suggestions.products.length > 0">
                        <div class="p-2">
                            <p class="px-2 py-1 text-[10px] font-semibold text-slate-500 uppercase">Produk</p>
                            <template x-for="product in suggestions.products" :key="product.id">
                                <a :href="`{{ url('/products') }}/${product.slug}`"
                                    class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-50 text-xs">
                                    <img :src="product.thumbnail || '/placeholder.png'" :alt="product.name"
                                        class="h-8 w-8 rounded object-cover">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-slate-900 truncate" x-text="product.name"></p>
                                        <p class="text-[10px] text-slate-500" x-text="product.category || ''"></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>
                    <div class="p-2 border-t border-slate-100">
                        <button type="button" @click="submitSearch()"
                            class="w-full text-center text-xs font-medium text-indigo-600 hover:text-indigo-700 py-1">
                            Lihat semua hasil untuk "<span x-text="query"></span>"
                        </button>
                    </div>
                </div>
            </div>

            <script>
                function searchDropdown() {
                    return {
                        query: '{{ request('q') }}',
                        showDropdown: false,
                        suggestions: {
                            products: [],
                            categories: []
                        },
                        search() {
                            if (this.query.length < 2) {
                                this.suggestions = { products: [], categories: [] };
                                return;
                            }

                            fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(this.query)}`)
                                .then(response => response.json())
                                .then(data => {
                                    this.suggestions = data;
                                })
                                .catch(error => {
                                    console.error('Search error:', error);
                                });
                        },
                        submitSearch() {
                            if (this.query.trim()) {
                                window.location.href = `{{ route('products.index') }}?q=${encodeURIComponent(this.query)}`;
                            }
                        }
                    }
                }
            </script>

            {{-- Right actions --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('wishlist.index') }}"
                    class="hidden sm:inline-flex relative h-8 px-3 items-center justify-center rounded-lg border border-slate-800 bg-slate-900/70 text-sm text-slate-300 hover:text-white hover:border-indigo-500 hover:bg-slate-900 transition-colors">
                    ♥
                </a>
                @auth
                    <a href="{{ route('cart.index') }}"
                        class="relative inline-flex h-8 px-3 items-center justify-center rounded-lg border border-slate-800 bg-slate-900/70 text-sm text-slate-200 hover:text-white hover:border-indigo-500 hover:bg-slate-900 transition-colors">
                        🛒
                        {{-- Badge jumlah cart bisa ditambahkan di sini --}}
                    </a>
                @else
                    <button type="button" @click="showLoginModal = true"
                        class="relative inline-flex h-8 px-3 items-center justify-center rounded-lg border border-slate-800 bg-slate-900/70 text-sm text-slate-200 hover:text-white hover:border-indigo-500 hover:bg-slate-900 transition-colors">
                        🛒
                    </button>
                @endauth

                @auth
                    <div x-data="{ open: false }" class="relative hidden sm:block">
                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900/80 px-3 py-1.5 text-[11px] font-medium text-slate-100 hover:border-indigo-500 hover:bg-slate-900 transition-colors cursor-pointer"
                            @click="open = !open">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500 text-white text-[11px]">
                                {{ strtoupper(Str::limit(auth()->user()->name, 2, '')) }}
                            </span>
                            <span class="max-w-[80px] truncate">{{ auth()->user()->name }}</span>
                            <svg class="h-3 w-3 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false"
                            class="absolute right-0 mt-2 w-44 rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-900/10 text-[11px] text-slate-900 py-1.5">
                            <a href="{{ route('account.profile') }}"
                                class="flex items-center px-3 py-2 hover:bg-slate-100">
                                Profile
                            </a>
                            <a href="{{ route('cart.index') }}"
                                class="flex items-center px-3 py-2 hover:bg-slate-100">
                                Keranjang Saya
                            </a>
                            <a href="{{ route('wishlist.index') }}"
                                class="flex items-center px-3 py-2 hover:bg-slate-100">
                                Wishlist
                            </a>
                            <div class="my-1 border-t border-slate-200"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center px-3 py-2 text-[11px] font-semibold text-red-500 hover:bg-red-50">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="hidden sm:inline-flex items-center rounded-full border border-slate-800 bg-slate-900/70 px-3 py-1.5 text-[11px] font-medium text-slate-100 hover:border-indigo-500 hover:bg-slate-900 transition-colors">
                        Login
                    </a>
                @endauth

                {{-- Mobile menu button --}}
                <button type="button"
                    class="inline-flex items-center justify-center rounded-full border border-slate-800 bg-slate-900/80 p-2 text-slate-100 hover:border-indigo-500 hover:bg-slate-900 md:hidden"
                    data-mobile-menu-trigger>
                    ☰
                </button>
            </div>
        </div>
    </div>
</header>
