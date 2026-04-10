# DentalCare Lite

DentalCare Lite adalah aplikasi reservasi klinik gigi berbasis Laravel 11 dengan tiga area utama:

- Publik: landing page, katalog layanan, daftar dokter
- Pasien: booking, pembayaran Midtrans, riwayat, invoice, resume medis
- Internal: dashboard dokter, input resume medis, admin report, master data dokter, layanan, dan jadwal

## Fitur utama

- Reservasi online berdasarkan dokter, layanan, tanggal, dan slot yang tersedia
- Validasi slot dengan mempertimbangkan durasi layanan dan kuota harian dokter
- Integrasi pembayaran Midtrans Snap dan webhook callback
- Login dan register pasien via Google
- Invoice PDF dan resume medis PDF
- Notifikasi database dan email untuk booking, pembayaran, dan resume medis
- Dashboard admin dengan statistik dan grafik pendapatan
- Audit fields standar perusahaan pada tabel utama

## Stack

- PHP 8.2+
- Laravel 11
- Blade + Bootstrap 5 + Vite
- MySQL 8
- Midtrans Snap
- DomPDF
- Docker Compose

## Quick start dengan Docker

1. Salin environment:

```bash
cp .env.example .env
```

2. Build dan jalankan container:

```bash
make build
make up
```

3. Install dependency dan siapkan database:

```bash
make install
```

4. Akses aplikasi di `http://localhost:8080`

## Menjalankan tanpa Docker

1. Install dependency:

```bash
composer install
npm install
```

2. Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur database MySQL di `.env`, lalu jalankan:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

## Akun demo hasil seeder

- Admin: `admin@dentalcare.test` / `password`
- Pasien: `patient@dentalcare.test` / `password`
- Dokter 1: `dr.aji@dentalcare.test` / `password`
- Dokter 2: `dr.salsa@dentalcare.test` / `password`
- Dokter 3: `dr.rizky@dentalcare.test` / `password`

## Environment penting

- `MIDTRANS_SERVER_KEY`
- `MIDTRANS_CLIENT_KEY`
- `MIDTRANS_IS_PRODUCTION`
- `MIDTRANS_CALLBACK_URL`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- `APPOINTMENT_SLOT_MINUTES`

Jika key Midtrans belum diisi, aplikasi tetap bisa membuat booking untuk demo, tetapi pembayaran eksternal tidak akan berjalan penuh.

Untuk Google login, buat OAuth Client di Google Cloud Console lalu arahkan callback ke URL absolut aplikasi Anda, misalnya `http://localhost:8080/auth/google/callback`.

## Testing

Menjalankan test suite:

```bash
php artisan test
```

Ekstensi PHP yang dibutuhkan PHPUnit antara lain `dom`, `xml`, dan `xmlwriter`.

## Struktur area

- `/` halaman publik
- `/booking/create` form booking
- `/history` riwayat booking pasien
- `/doctor/dashboard` dashboard dokter
- `/doctor/medical-notes` resume medis
- `/admin/reports` laporan admin
- `/admin/payments` monitoring pembayaran

## Catatan implementasi

- Semua tabel utama memakai 7 audit fields melalui helper schema dan trait model.
- Booking aktif dibatasi unique slot per dokter, tanggal, dan jam untuk mengurangi double booking.
- Invoice hanya bisa diunduh setelah pembayaran berstatus `paid`.
