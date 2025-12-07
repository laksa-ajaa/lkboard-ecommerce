{{-- Modal Login Required --}}
<div x-show="showLoginModal" x-cloak 
    @login-required.window="showLoginModal = true"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    x-transition:enter="transition ease-out duration-300" 
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" 
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0">

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
            Anda perlu masuk terlebih dahulu untuk mengakses keranjang belanja.
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

