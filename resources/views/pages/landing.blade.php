@extends('layouts.app')

@section('title', config('app.name') . ' – Mechanical Keyboard 11.11 Big Sales')

@section('content')

    {{-- HERO --}}
    <div class="container mx-auto max-w-6xl px-4 sm:px-6 lg:px-0 mt-6">
        <x-banner />
    </div>

    {{-- CONTENT WRAPPER --}}
    <section class="mt-14">
        <div class="container mx-auto max-w-6xl space-y-14 px-4 sm:px-6 lg:px-0">

            {{-- ========== KATEGORI POPULER + KOLEKSI ========== --}}
            <div class="space-y-10">

                {{-- Card kategori --}}
                <div
                    class="space-y-6 rounded-2xl border border-slate-200 bg-white/70 p-6 shadow-sm shadow-slate-200/60 backdrop-blur">

                    {{-- Title kategori --}}
                    <div
                        class="flex flex-col items-center gap-2 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left">
                        <h2 class="text-lg font-extrabold text-slate-900">
                            Kategori Populer
                        </h2>

                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center gap-1 rounded-full border border-indigo-500/40 bg-indigo-500/5 px-4 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 transition">
                            <span>Lihat semua</span>

                        </a>
                    </div>

                    {{-- CATEGORY PILLS (centered) --}}
                    <div class="flex flex-wrap justify-center gap-3">
                        @forelse ($popularCategories as $category)
                            <a href="{{ route('products.category', $category->slug) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white shadow-sm hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 cursor-pointer transition text-sm font-semibold text-slate-800 whitespace-nowrap">
                                <span>{{ $category->name }}</span>
                            </a>
                        @empty
                            <p class="text-center text-xs text-slate-500">
                                Belum ada kategori yang bisa ditampilkan.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- Card koleksi --}}
                <div
                    class="space-y-6 rounded-2xl border border-slate-200 bg-white/70 p-6 shadow-sm shadow-slate-200/60 backdrop-blur">

                    {{-- KOLEKSI TERBAIK --}}
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">
                                Koleksi Keyboard Terbaik
                            </h2>
                            <p class="mt-1 text-[13px] text-slate-500">
                                Pilihan keyboard premium mulai dari starter kit hingga full custom.
                            </p>
                        </div>

                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center gap-1 rounded-full border border-indigo-500/40 bg-indigo-500/5 px-4 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 transition">
                            <span>Lihat semua</span>

                        </a>
                    </div>

                    {{-- ====== PRODUCT LIST (featured) ====== --}}
                    <x-product-grid :products="$featuredProducts" />
                </div>
            </div>

            {{-- ========== ABOUT SECTION ========== --}}
            <section id="about"
                class="rounded-2xl border border-slate-200 bg-white/80 p-8 shadow-sm shadow-slate-200/60 backdrop-blur space-y-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-indigo-500 font-semibold">Tentang Kami</p>
                        <h3 class="text-xl font-extrabold text-slate-900 mt-1">Tentang LKBoard</h3>
                    </div>

                </div>
                <div class="grid gap-6 md:grid-cols-2 items-stretch mt-2">
                    <div class="rounded-2xl border border-slate-100 p-5 space-y-3 text-sm text-slate-600">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            LKBoard lahir dari komunitas mechanical keyboard Indonesia dengan misi menghadirkan setup
                            berkualitas tanpa repot. Kami mengkurasi koleksi keyboard, switch, dan aksesoris pilihan
                            serta menyediakan panduan modding untuk semua level pengguna — dari pemula sampai enthusiast.
                        </p>
                        <p>
                            Di experience store kami, kamu bisa mencoba langsung berbagai layout, profile switch, dan
                            mendengar sound test sebelum membeli. Lokasi berada di pusat kota Medan dengan akses mudah
                            menggunakan transportasi umum maupun kendaraan pribadi.
                        </p>
                        <ul class="space-y-2 text-xs text-slate-500">
                            <li>📍 Koordinat: 3.517367882399484, 98.673248005966</li>
                            <li>🕒 Jam buka: Senin–Minggu 10.00–20.00 WIB</li>
                            <li>📞 Reservasi sesi private: +62 815-3518-426</li>
                        </ul>
                        <a target="_blank" rel="noopener"
                            href="https://maps.google.com/?q=3.517367882399484,98.673248005966"
                            class="inline-flex items-center gap-1 rounded-full border border-indigo-500/40 bg-indigo-500/5 px-4 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 transition">
                            <span>Buka di Google Maps</span>

                        </a>
                    </div>
                    <div class="rounded-2xl border border-slate-100 overflow-hidden min-h-[260px]">
                        <iframe class="h-full w-full" style="border:0;" loading="lazy" allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=3.517367882399484,98.673248005966&z=15&output=embed"></iframe>
                    </div>
                </div>
            </section>

            {{-- ========== CONTACT CENTER ========== --}}
            <section id="contact-center"
                class="rounded-2xl border border-slate-100 bg-white text-slate-900 p-8 shadow-sm shadow-slate-200 space-y-6">
                <div class="flex flex-col gap-2">
                    <p class="text-xs uppercase tracking-[0.3em] text-indigo-500 font-semibold">Contact Center</p>
                    <h3 class="text-xl font-extrabold text-slate-900">Kirim feedback atau keluhan</h3>
                    <p class="text-sm text-slate-500">
                        Tim support LKBoard standby Senin–Minggu pukul 09.00–21.00 WIB. Sampaikan detail kendala kamu
                        lewat form berikut atau pilih kanal yang paling nyaman.
                    </p>
                </div>
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                            <p class="font-semibold text-slate-900">Sebelum mengirim:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-4 text-xs text-slate-500">
                                <li>Sertakan nomor pesanan atau bukti pembayaran jika ada.</li>
                                <li>Tulis detail kendala secara jelas agar tim kami bisa bantu lebih cepat.</li>
                                <li>Respon pertama rata-rata 1–2 jam kerja.</li>
                            </ul>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 text-sm">
                            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow">
                                <p class="text-xs text-slate-400">WhatsApp</p>
                                <p class="text-base font-semibold text-slate-900 mt-1">+62 815-3518-426</p>
                                <a href="https://wa.me/628153518426"
                                    class="mt-2 inline-flex items-center gap-1 rounded-full bg-indigo-500 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-indigo-400 transition">
                                    <span>Chat sekarang</span>

                                </a>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow">
                                <p class="text-xs text-slate-400">Email Support</p>
                                <p class="text-base font-semibold text-slate-900 mt-1">lkboard@laksaajaa.my.id</p>
                                <a href="mailto:lkboard@laksaajaa.my.id"
                                    class="mt-2 inline-flex items-center gap-1 rounded-full bg-indigo-500 px-4 py-1.5 text-[11px] font-semibold text-white hover:bg-indigo-400 transition">
                                    <span>Kirim email</span>

                                </a>
                            </div>

                        </div>
                    </div>
                    <form action="mailto:lkboard@laksaajaa.my.id" method="POST" enctype="text/plain"
                        class="space-y-4 rounded-xl border border-slate-100 bg-white p-6 text-sm shadow">
                        <div>
                            <label for="contact-name" class="text-xs uppercase tracking-[0.2em] text-slate-500">Nama
                                lengkap</label>
                            <input id="contact-name" name="name" type="text" required
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                placeholder="Nama sesuai pesanan" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact-email"
                                    class="text-xs uppercase tracking-[0.2em] text-slate-500">Email</label>
                                <input id="contact-email" name="email" type="email" required
                                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    placeholder="email@domain.com" />
                            </div>
                            <div>
                                <label for="contact-order" class="text-xs uppercase tracking-[0.2em] text-slate-500">No.
                                    pesanan (opsional)</label>
                                <input id="contact-order" name="order_id" type="text"
                                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    placeholder="ORD-2025-0012" />
                            </div>
                        </div>
                        <div>
                            <label for="contact-topic"
                                class="text-xs uppercase tracking-[0.2em] text-slate-500">Topik</label>
                            <select id="contact-topic" name="topic" required
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="feedback">Feedback produk / layanan</option>
                                <option value="complaint">Keluhan pesanan</option>
                                <option value="warranty">Garansi & klaim servis</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label for="contact-message"
                                class="text-xs uppercase tracking-[0.2em] text-slate-500">Pesan</label>
                            <textarea id="contact-message" name="message" rows="4" required
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                placeholder="Ceritakan detail kendala atau masukan kamu di sini..."></textarea>
                        </div>
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400 transition">
                            Kirim keluhan / feedback
                        </button>
                        <p class="text-[11px] text-slate-500 text-center">
                            Form ini mengirim email langsung ke tim support LKBoard. Untuk respon tercepat pastikan data
                            kamu akurat.
                        </p>
                    </form>
                </div>
            </section>
            {{-- ========== CTA BAWAH ========== --}}
            <section
                class="rounded-2xl bg-slate-950/70 border border-slate-800 px-6 py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 backdrop-blur">

                <div>
                    @auth
                        <h3 class="text-sm font-semibold text-slate-50">Siap upgrade setup keyboard kamu?</h3>
                        <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                            Jelajahi koleksi terbaru LKBoard dan lengkapi gear untuk produktivitas harianmu.
                        </p>
                    @else
                        <h3 class="text-sm font-semibold text-slate-50">Baru kenal mechanical keyboard?</h3>
                        <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                            Buat akun LKBoard dan mulai perjalanan setup pertamamu.
                        </p>
                    @endauth
                </div>

                <div class="flex gap-3">
                    @auth
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center rounded-full bg-indigo-500 px-5 py-2 text-xs font-semibold text-white hover:bg-indigo-400 transition">
                            Lihat Koleksi
                        </a>

                        <a href="{{ route('account.index') }}"
                            class="inline-flex items-center rounded-full border border-slate-700 px-5 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-900 transition">
                            Ke Akun Saya
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center rounded-full bg-indigo-500 px-5 py-2 text-xs font-semibold text-white hover:bg-indigo-400 transition">
                            Daftar Sekarang
                        </a>

                        <a href="{{ route('login') }}"
                            class="inline-flex items-center rounded-full border border-slate-700 px-5 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-900 transition">
                            Sudah punya akun?
                        </a>
                    @endauth
                </div>
            </section>
        </div>

    </section>

@endsection
