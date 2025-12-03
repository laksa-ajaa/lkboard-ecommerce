@props([
    'title' => '11.11 Big Sales',
    'subtitle' => 'Diskon spesial untuk koleksi keyboard mechanical pilihan hingga 40% selama periode 1–11 November.',
    'ctaText' => 'Belanja Sekarang',
    'ctaHref' => null,
])

<section
    class="relative overflow-hidden rounded-3xl border border-slate-800 bg-linear-to-r from-slate-950 via-slate-900 to-indigo-950 text-slate-50 shadow-[0_24px_80px_rgba(15,23,42,0.85)]">
    {{-- Glow background --}}
    <div class="pointer-events-none absolute -left-32 top-10 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-10 -bottom-10 h-80 w-80 rounded-full bg-sky-500/10 blur-3xl"></div>

    <div class="relative grid gap-10 px-6 py-10 sm:px-10 sm:py-12 lg:grid-cols-2 lg:px-14 lg:py-14 items-center">
        {{-- Text content --}}
        <div class="max-w-lg space-y-4">
            <div
                class="inline-flex items-center gap-2 rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                <span class="text-[11px] font-medium uppercase tracking-[0.22em] text-indigo-200">
                    Limited • 11.11 Big Sales
                </span>
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight">
                {{ $title }}
            </h1>

            <p class="text-sm sm:text-base text-slate-300 max-w-md">
                {{ $subtitle }}
            </p>



            @if ($ctaText)
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="{{ $ctaHref ?? route('products.index') }}"
                        class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-indigo-500/40 hover:bg-indigo-400 transition-colors">
                        {{ $ctaText }}
                    </a>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center text-xs font-medium text-slate-300 hover:text-white">
                        Lihat semua koleksi
                    </a>
                </div>
            @endif
        </div>

        {{-- Hero image / keyboard showcase --}}
        <div class="relative">
            <div
                class="relative mx-auto aspect-4/3 max-w-md overflow-hidden rounded-3xl border border-slate-800 bg-linear-to-tr from-slate-900 via-slate-800 to-indigo-900 shadow-[0_20px_60px_rgba(15,23,42,0.9)]">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(129,140,248,0.3),transparent_60%),radial-gradient(circle_at_bottom,rgba(56,189,248,0.3),transparent_55%)] opacity-40">
                </div>

                {{-- Hero image from storage --}}
                <div class="relative z-10 h-full w-full">
                    <img src="{{ asset('storage/hero/hero.png') }}" alt="LKBoard hero"
                        class="h-full w-full object-cover object-center" loading="lazy">
                </div>

                <div class="absolute left-4 top-4 z-20 space-y-1 text-[10px] text-slate-200">
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-950/60 px-2 py-1 backdrop-blur border border-slate-700/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        In-stock & ready to ship
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-950/50 px-2 py-1 backdrop-blur border border-slate-700/60">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Tersedia di berbagai varian switch
                    </span>
                </div>

                <div
                    class="absolute right-4 bottom-4 z-20 rounded-2xl bg-slate-950/70 px-3 py-2 text-[11px] text-slate-100 border border-slate-700/70 backdrop-blur">
                    <p class="font-medium">Toko resmi keyboard custom & mechanical gear.</p>
                    <p class="text-[10px] text-slate-400">Build quality premium • Switch & layout lengkap.</p>
                </div>
            </div>
        </div>
    </div>
</section>
