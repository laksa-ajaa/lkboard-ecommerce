<footer class="mt-8 border-t border-slate-800 bg-slate-950">
    <div class="container mx-auto px-4 py-8">
        <div class="grid gap-6 md:grid-cols-4 text-sm text-slate-300">
            <div>
                <h3 class="text-sm font-semibold text-slate-50 mb-3">
                    {{ config('app.name', 'LKBoard') }}
                </h3>
                <p class="text-xs text-slate-400">
                    Toko online untuk kebutuhan harian, elektronik, fashion, dan banyak lagi.
                </p>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-slate-100 mb-2">Bantuan</h4>
                <ul class="space-y-1 text-xs">
                    <li><a href="#" class="hover:text-indigo-400">Pusat Bantuan</a></li>
                    <li><a href="#" class="hover:text-indigo-400">Cara Belanja</a></li>
                    <li><a href="#" class="hover:text-indigo-400">Pengiriman</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-slate-100 mb-2">Tentang</h4>
                <ul class="space-y-1 text-xs">
                    <li><a href="#" class="hover:text-indigo-400">Tentang Kami</a></li>
                    <li><a href="#" class="hover:text-indigo-400">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-indigo-400">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-slate-100 mb-2">Ikuti kami</h4>
                <div class="flex gap-3 text-lg text-slate-300">
                    <a href="#" class="hover:text-indigo-400">📘</a>
                    <a href="#" class="hover:text-indigo-400">📸</a>
                    <a href="#" class="hover:text-indigo-400">▶️</a>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-800 pt-4 flex flex-col md:flex-row items-center justify-between gap-2">
            <p class="text-[11px] text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'LKBoard') }}. All rights reserved.
            </p>
            <p class="text-[11px] text-slate-500">
                Dibangun dengan Laravel & Tailwind CSS.
            </p>
        </div>
    </div>
</footer>
