# Deploy DentalCare ke Hostinger hPanel

Panduan ini menargetkan shared hosting Hostinger dengan upload ZIP lewat File Manager. Project Laravel disimpan di luar `public_html`, sedangkan isi folder publik ditempatkan di `public_html`.

## Struktur di Hostinger

Di File Manager, siapkan struktur seperti ini pada folder domain atau temporary domain:

```text
domains/NAMA_DOMAIN/
├── dentalcare_app/
└── public_html/
```

`dentalcare_app` berisi source Laravel, `vendor`, `storage`, dan `.env`. `public_html` hanya berisi file publik seperti `index.php`, `.htaccess`, `build`, `favicon.ico`, dan `robots.txt`.

## Buat Paket ZIP

Jalankan dari root project lokal:

```bash
bash scripts/build-hostinger-package.sh
```

Output akan dibuat di:

```text
deploy/hostinger/
├── dentalcare_app.zip
├── public_html.zip
└── dentalcare_database.zip
```

## Upload via hPanel

1. Buka hPanel Hostinger, masuk ke File Manager untuk temporary domain.
2. Buat folder `dentalcare_app` sejajar dengan `public_html`.
3. Upload `deploy/hostinger/dentalcare_app.zip` ke folder `dentalcare_app`, lalu extract di sana.
4. Upload `deploy/hostinger/public_html.zip` ke folder `public_html`, lalu extract di sana.
5. Copy `.env.hostinger.example` menjadi `.env` di dalam folder `dentalcare_app`.
6. Edit `.env` dan isi nilai Hostinger:
   - `APP_URL`
   - `APP_KEY`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - kredensial SMTP, Google OAuth, dan Midtrans jika digunakan

## Database MySQL

1. Buat database MySQL dari hPanel.
2. Catat nama database, username, password, dan host database dari hPanel.
3. Import SQL lewat phpMyAdmin menggunakan file di `deploy/hostinger/database/dentalcare.sql` atau `dentalcare_database.zip`.
4. Sesuaikan `.env` di `dentalcare_app`.

Catatan: dump `dentalcare.sql` berisi data demo/current database. Jika ingin database kosong, jalankan migration/seed sendiri lewat SSH atau buat dump baru yang sesuai.

## Pengaturan Penting

- Set PHP version di hPanel minimal PHP 8.2. Project ini dikunci untuk Laravel 11 dan sudah diuji lokal dengan PHP 8.3.
- `QUEUE_CONNECTION=sync` disarankan untuk shared hosting upload-only, karena queue worker Docker tidak berjalan di hPanel.
- `APP_DEBUG=false` wajib untuk production.
- `APP_URL` harus sama dengan temporary domain Hostinger, termasuk `https://`.
- Untuk Google OAuth, tambahkan redirect URI ini di Google Console:

```text
https://ISI_TEMPORARY_DOMAIN_HOSTINGER/auth/google/callback
```

- Untuk Midtrans webhook/callback, gunakan:

```text
https://ISI_TEMPORARY_DOMAIN_HOSTINGER/payments/midtrans/callback
```

## Setelah Upload

Jika hPanel menyediakan Terminal/SSH, jalankan di folder `dentalcare_app`:

```bash
php artisan optimize:clear
php artisan storage:link
```

Jika tidak ada Terminal/SSH, app tetap bisa berjalan karena paket sudah menyertakan `vendor` dan asset hasil build. Untuk project ini, penggunaan `storage:link` tidak kritis karena tidak ditemukan upload publik yang bergantung pada `/storage`.

## Troubleshooting Cepat

- Error 500: cek `.env`, versi PHP, permission folder `storage` dan `bootstrap/cache`.
- Tampilan tanpa CSS/JS: pastikan isi `public_html/build` ikut ter-upload.
- Error database: pastikan kredensial DB di `.env` sama dengan hPanel dan SQL sudah di-import.
- Email tidak terkirim: cek SMTP Hostinger/Gmail dan pastikan `QUEUE_CONNECTION=sync`.
