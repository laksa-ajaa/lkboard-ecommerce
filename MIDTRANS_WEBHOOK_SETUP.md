# Setup Midtrans Webhook

Dokumentasi ini menjelaskan cara setup webhook Midtrans untuk menerima notifikasi pembayaran otomatis.

## 1. Route Webhook

Route webhook sudah dibuat di:
- **URL**: `POST /midtrans/webhook`
- **Controller**: `App\Http\Controllers\MidtransWebhookController@webhook`
- **File**: `app/Http/Controllers/MidtransWebhookController.php`

## 2. Mendaftarkan URL di Midtrans Dashboard

### Untuk Sandbox (Testing):
1. Login ke [Midtrans Dashboard Sandbox](https://dashboard.sandbox.midtrans.com/)
2. Masuk ke menu **Settings** → **Configuration**
3. Scroll ke bagian **Payment Notification URL**
4. Masukkan URL webhook Anda:
   ```
   https://your-domain.com/midtrans/webhook
   ```
   atau untuk development lokal dengan ngrok:
   ```
   https://your-ngrok-url.ngrok.io/midtrans/webhook
   ```
5. Klik **Save**

### Untuk Production:
1. Login ke [Midtrans Dashboard Production](https://dashboard.midtrans.com/)
2. Masuk ke menu **Settings** → **Configuration**
3. Scroll ke bagian **Payment Notification URL**
4. Masukkan URL webhook production:
   ```
   https://your-production-domain.com/midtrans/webhook
   ```
5. Klik **Save**

## 3. Testing Webhook di Sandbox

### Cara 1: Simulasi Pembayaran di Sandbox
1. Buat order baru melalui aplikasi
2. Gunakan kartu kredit test:
   - **Card Number**: `4811 1111 1111 1114`
   - **CVV**: `123`
   - **Expiry**: Bulan/tahun masa depan (misal: `12/25`)
   - **3D Secure Password**: `112233`
3. Setelah pembayaran berhasil, Midtrans akan mengirim notifikasi ke webhook
4. Cek log aplikasi untuk melihat notifikasi yang diterima:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Cara 2: Simulasi via Midtrans Dashboard
1. Login ke Midtrans Dashboard Sandbox
2. Masuk ke menu **Transactions**
3. Pilih transaksi yang ingin di-simulate
4. Klik tombol **Simulate Payment** atau **Actions** → **Simulate Payment**
5. Pilih status yang ingin di-simulate (misal: `settlement` untuk status paid)
6. Midtrans akan mengirim notifikasi ke webhook

### Cara 3: Manual Testing dengan cURL
Anda bisa test webhook secara manual dengan mengirim request POST:

```bash
curl -X POST https://your-domain.com/midtrans/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_time": "2024-01-01 12:00:00",
    "transaction_status": "settlement",
    "transaction_id": "test-transaction-id",
    "status_message": "midtrans payment notification",
    "status_code": "200",
    "signature_key": "test-signature",
    "payment_type": "credit_card",
    "order_id": "ORD-XXXXX",
    "gross_amount": "100000.00",
    "fraud_status": "accept"
  }'
```

**Catatan**: Untuk testing manual, Anda perlu menyesuaikan `order_id` dengan order number yang ada di database.

## 4. Status Transaksi yang Didukung

Webhook akan memproses status berikut:

| Status Midtrans | Status Order | Keterangan |
|----------------|--------------|------------|
| `settlement` | `paid` | Pembayaran berhasil (VA, e-wallet, dll) |
| `capture` (fraud_status: accept) | `paid` | Pembayaran kartu kredit berhasil |
| `capture` (fraud_status: challenge) | `challenge` | Pembayaran sedang di-challenge |
| `pending` | `pending` | Menunggu pembayaran |
| `deny` | `failed` | Pembayaran ditolak |
| `expire` | `expired` | Pembayaran expired |
| `cancel` | `cancelled` | Pembayaran dibatalkan |

## 5. Logging

Semua aktivitas webhook akan di-log di:
- **File**: `storage/logs/laravel.log`
- **Level**: `info` untuk sukses, `error` untuk error

Contoh log:
```
[2024-01-01 12:00:00] local.INFO: Midtrans Webhook Received {"order_id":"ORD-XXXXX","transaction_status":"settlement",...}
[2024-01-01 12:00:01] local.INFO: Order marked as paid {"order_id":"ORD-XXXXX","transaction_id":"..."}
```

## 6. Troubleshooting

### Webhook tidak menerima notifikasi
1. Pastikan URL webhook sudah benar di Midtrans Dashboard
2. Pastikan server dapat diakses dari internet (gunakan ngrok untuk development)
3. Cek firewall/server configuration
4. Cek log aplikasi untuk error

### Order tidak ter-update
1. Pastikan `order_number` di database sesuai dengan `order_id` dari Midtrans
2. Cek log untuk melihat notifikasi yang diterima
3. Pastikan tidak ada error di log

### Testing di Local Development
Untuk testing di local, gunakan ngrok atau tool serupa:
```bash
# Install ngrok
# Lalu jalankan:
ngrok http 8000

# Gunakan URL ngrok untuk webhook di Midtrans Dashboard:
https://xxxxx.ngrok.io/midtrans/webhook
```

## 7. Keamanan

Webhook endpoint ini **tidak memerlukan authentication** karena Midtrans akan mengirim notifikasi dari server mereka. Namun, Midtrans menggunakan signature verification untuk memastikan notifikasi valid.

**Catatan**: Pastikan untuk:
- Menggunakan HTTPS di production
- Memverifikasi signature key dari Midtrans (dapat ditambahkan di controller jika diperlukan)
- Tidak mengekspos endpoint ini ke public tanpa HTTPS

