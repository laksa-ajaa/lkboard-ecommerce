<div
    class="fixed inset-0 z-40 hidden bg-black/40 md:hidden"
    data-mobile-menu
>
    <div class="absolute right-0 top-0 h-full w-72 bg-white shadow-xl flex flex-col">
        <div class="flex items-center justify-between px-4 h-14 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-900">
                Menu
            </span>
            <button type="button" class="p-2 text-gray-500 hover:text-gray-900" data-mobile-menu-close>
                ✕
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 text-sm">
            <a href="{{ url('/') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                Beranda
            </a>
            <a href="{{ route('products.index') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                Produk
            </a>
            <a href="{{ route('cart.index') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                Keranjang
            </a>
            <a href="{{ route('wishlist.index') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                Wishlist
            </a>

            <div class="mt-4 border-t border-gray-100 pt-3">
                @auth
                    <a href="{{ route('account.index') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                        Akun Saya
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-1">
                        @csrf
                        <button type="submit" class="w-full text-left px-2 py-2 rounded-md hover:bg-gray-50 text-red-600">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="block px-2 py-2 rounded-md hover:bg-gray-50">
                        Daftar
                    </a>
                @endauth
            </div>
        </nav>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menu = document.querySelector('[data-mobile-menu]');
        const openBtn = document.querySelector('[data-mobile-menu-trigger]');
        const closeBtn = document.querySelector('[data-mobile-menu-close]');

        if (!menu || !openBtn || !closeBtn) return;

        const open = () => menu.classList.remove('hidden');
        const close = () => menu.classList.add('hidden');

        openBtn.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        menu.addEventListener('click', (e) => {
            if (e.target === menu) close();
        });
    });
</script>
@endpush
