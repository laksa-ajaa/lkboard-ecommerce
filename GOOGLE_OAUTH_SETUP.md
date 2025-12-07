# Setup Google OAuth Login - Step by Step Guide

Dokumentasi ini menjelaskan langkah-langkah untuk mengkonfigurasi Google OAuth di luar project ini, sehingga Anda dapat setup sendiri di Google Cloud Console.

## 📋 Prerequisites

-   Akun Google (Gmail)
-   Akses ke Google Cloud Console
-   Project Laravel yang sudah terinstall Laravel Socialite

---

## 🚀 Langkah 1: Buat Project di Google Cloud Console

1. **Buka Google Cloud Console**

    - Kunjungi: https://console.cloud.google.com/
    - Login dengan akun Google Anda

2. **Buat Project Baru (jika belum ada)**

    - Klik dropdown project di bagian atas
    - Klik "New Project"
    - Isi nama project (contoh: "LKBoard Ecommerce")
    - Klik "Create"

3. **Pilih Project yang Baru Dibuat**
    - Pastikan project yang baru dibuat sudah terpilih di dropdown

---

## 🔐 Langkah 2: Enable Google+ API

1. **Buka API Library**

    - Di sidebar kiri, klik "APIs & Services" > "Library"
    - Atau kunjungi: https://console.cloud.google.com/apis/library

2. **Cari dan Enable Google+ API**

    - Di search box, ketik "Google+ API"
    - Klik pada "Google+ API" dari hasil pencarian
    - Klik tombol "ENABLE"
    - Tunggu hingga proses selesai

    **Catatan:** Google+ API sudah deprecated, tapi masih diperlukan untuk OAuth. Alternatifnya, Anda bisa menggunakan "Google Identity Services" tapi memerlukan konfigurasi tambahan.

---

## 🔑 Langkah 3: Buat OAuth 2.0 Credentials

1. **Buka Credentials Page**

    - Di sidebar, klik "APIs & Services" > "Credentials"
    - Atau kunjungi: https://console.cloud.google.com/apis/credentials

2. **Buat OAuth Consent Screen (jika belum ada)**

    - Klik "OAuth consent screen" di bagian atas
    - Pilih "External" (untuk testing) atau "Internal" (jika menggunakan Google Workspace)
    - Klik "CREATE"

    **Isi Form OAuth Consent Screen:**

    - **App name:** Nama aplikasi Anda (contoh: "LKBoard Ecommerce")
    - **User support email:** Email Anda
    - **Developer contact information:** Email Anda
    - Klik "SAVE AND CONTINUE"

    **Scopes (langkah berikutnya):**

    - Klik "ADD OR REMOVE SCOPES"
    - Pilih scope berikut:
        - `.../auth/userinfo.email`
        - `.../auth/userinfo.profile`
    - Klik "UPDATE" lalu "SAVE AND CONTINUE"

    **Test users (untuk External apps):**

    - Tambahkan email test user (opsional untuk development)
    - Klik "SAVE AND CONTINUE"
    - Klik "BACK TO DASHBOARD"

3. **Buat OAuth 2.0 Client ID**

    - Kembali ke halaman "Credentials"
    - Klik "CREATE CREDENTIALS" > "OAuth client ID"
    - Pilih "Web application" sebagai Application type
    - Isi nama (contoh: "LKBoard Web Client")

    **Authorized JavaScript origins:**

    - Tambahkan URL aplikasi Anda:
        - Development: `http://localhost:8000`
        - Production: `https://yourdomain.com`
        - Tambahkan semua environment yang digunakan

    **Authorized redirect URIs:**

    - Tambahkan callback URL:

        - Development: `http://localhost:8000/auth/google/callback`
        - Production: `https://yourdomain.com/auth/google/callback`
        - Pastikan path sesuai dengan route yang sudah dibuat

    - Klik "CREATE"

4. **Copy Credentials**
    - Setelah dibuat, akan muncul popup dengan:
        - **Client ID** (contoh: `123456789-abcdefghijklmnop.apps.googleusercontent.com`)
        - **Client Secret** (contoh: `GOCSPX-abcdefghijklmnopqrstuvwxyz`)
    - **PENTING:** Copy kedua nilai ini, karena Client Secret hanya ditampilkan sekali!

---

## ⚙️ Langkah 4: Konfigurasi di Laravel Project

1. **Buka file `.env` di root project**

    ```bash
    nano .env
    # atau
    code .env
    ```

2. **Tambahkan konfigurasi Google OAuth**

    ```env
    GOOGLE_CLIENT_ID=your-client-id-here
    GOOGLE_CLIENT_SECRET=your-client-secret-here
    GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
    ```

    **Untuk Production:**

    ```env
    GOOGLE_CLIENT_ID=your-client-id-here
    GOOGLE_CLIENT_SECRET=your-client-secret-here
    GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
    ```

3. **Pastikan konfigurasi sudah benar di `config/services.php`**
    - File ini sudah dikonfigurasi dengan benar
    - Pastikan ada bagian:
    ```php
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    ```

---

## 🗄️ Langkah 5: Jalankan Migration

1. **Jalankan migration untuk update tabel users**

    ```bash
    php artisan migrate
    ```

    Migration ini akan:

    - Membuat kolom `password` menjadi nullable (untuk user OAuth)
    - Menambahkan kolom `google_id` untuk menyimpan Google ID user

---

## ✅ Langkah 6: Test Google OAuth

1. **Pastikan server Laravel berjalan**

    ```bash
    php artisan serve
    ```

2. **Buka halaman login**

    - Kunjungi: `http://localhost:8000/login`

3. **Klik tombol Google**
    - Anda akan diarahkan ke Google untuk login
    - Setelah login, Anda akan kembali ke aplikasi
    - User baru akan otomatis dibuat atau user existing akan login

---

## 🔒 Security Best Practices

1. **Jangan commit file `.env` ke Git**

    - Pastikan `.env` ada di `.gitignore`

2. **Gunakan environment variables yang berbeda untuk development dan production**

    - Development: `http://localhost:8000`
    - Production: `https://yourdomain.com`

3. **Rotate credentials secara berkala**

    - Jika credentials terkompromi, buat ulang di Google Cloud Console

4. **Gunakan HTTPS di production**
    - Google OAuth memerlukan HTTPS untuk production

---

## 🐛 Troubleshooting

### Error: "redirect_uri_mismatch"

-   **Penyebab:** Redirect URI di `.env` tidak sesuai dengan yang didaftarkan di Google Cloud Console
-   **Solusi:** Pastikan redirect URI di Google Cloud Console sama persis dengan yang di `.env`

### Error: "access_denied"

-   **Penyebab:** User membatalkan proses OAuth
-   **Solusi:** Ini normal, user bisa mencoba lagi

### Error: "invalid_client"

-   **Penyebab:** Client ID atau Client Secret salah
-   **Solusi:** Periksa kembali nilai di `.env` dan pastikan sudah benar

### User tidak terbuat setelah login Google

-   **Penyebab:** Migration belum dijalankan atau ada error di controller
-   **Solusi:**
    -   Jalankan `php artisan migrate`
    -   Periksa log di `storage/logs/laravel.log`

---

## 📝 Checklist Setup

-   [ ] Project dibuat di Google Cloud Console
-   [ ] Google+ API sudah di-enable
-   [ ] OAuth Consent Screen sudah dikonfigurasi
-   [ ] OAuth 2.0 Client ID sudah dibuat
-   [ ] Authorized redirect URIs sudah ditambahkan
-   [ ] Client ID dan Client Secret sudah di-copy
-   [ ] Konfigurasi sudah ditambahkan di `.env`
-   [ ] Migration sudah dijalankan
-   [ ] Test login dengan Google berhasil

---

## 🔗 Referensi

-   [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
-   [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
-   [Google Cloud Console](https://console.cloud.google.com/)

---

## 📞 Support

Jika mengalami masalah, periksa:

1. Log Laravel: `storage/logs/laravel.log`
2. Google Cloud Console > APIs & Services > Credentials
3. Pastikan semua URL sudah sesuai (tanpa trailing slash)

---

**Selamat! Google OAuth login sudah siap digunakan! 🎉**
