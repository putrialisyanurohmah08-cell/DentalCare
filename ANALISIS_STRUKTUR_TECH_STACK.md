# Analisis Struktur Project dan Tech Stack DentalCare

Tanggal analisis: 14 Mei 2026

## Ringkasan Project

DentalCare adalah aplikasi reservasi klinik gigi berbasis Laravel. Aplikasi ini memiliki tiga area utama:

- Area publik untuk landing page, katalog layanan, daftar dokter, dan akses booking.
- Area pasien untuk membuat booking, melihat riwayat, invoice, dan resume medis.
- Area internal untuk dokter dan admin, termasuk dashboard dokter, input resume medis, master data, pembayaran, dan laporan.

Secara arsitektur, project ini adalah aplikasi monolith Laravel dengan Blade sebagai view layer, database relasional, queue berbasis database, dan deployment berbasis Docker Compose.

## Tech Stack Utama

| Area | Teknologi | Keterangan |
| --- | --- | --- |
| Backend | PHP 8.2+ | `composer.json` mensyaratkan PHP `^8.2`, dengan platform lock PHP 8.3.6. |
| Framework | Laravel 11 | Dependency utama `laravel/framework:^11.31`. |
| Template engine | Blade | Semua halaman aplikasi berada di `resources/views/**/*.blade.php`. |
| Frontend build tool | Vite 6 | Dipakai melalui `laravel-vite-plugin`. |
| JavaScript | ES Module + Axios | `resources/js/app.js` dan `resources/js/bootstrap.js`. |
| CSS/UI | Bootstrap 5.3.3 CDN + custom CSS | Bootstrap dimuat dari CDN di layout utama, style custom ada di `resources/css/app.css`. |
| Chart | Chart.js 4.4.2 CDN | Dipakai pada halaman laporan admin. |
| Database | MySQL 8.4 di Docker | `docker-compose.yml` memakai image `mysql:8.4`. Laravel config juga masih mendukung SQLite, MariaDB, PostgreSQL, SQL Server secara default. |
| ORM | Eloquent | Model berada di `app/Models`. |
| Queue | Laravel Database Queue | Default queue connection adalah `database`, dengan worker container `queue`. |
| PDF | DomPDF | Paket `barryvdh/laravel-dompdf` dipakai untuk invoice dan resume medis PDF. |
| Payment gateway | Midtrans Snap | Paket `midtrans/midtrans-php`, service khusus di `app/Services/MidtransService.php`. |
| OAuth login | Laravel Socialite | Dipakai untuk login/register via Google. |
| 2FA | TOTP custom + Bacon QR Code | Service 2FA berada di `app/Services/Auth`, QR code dibuat dengan `bacon/bacon-qr-code`. |
| Testing | PHPUnit 11 + Laravel test runner | Test berada di `tests/Feature` dan `tests/Unit`. |
| Container | Docker + Docker Compose | Service utama: `app`, `queue`, `nginx`, dan `mysql`. |
| Web server | Nginx 1.27 Alpine | Konfigurasi di `docker/nginx`. |
| PHP runtime container | PHP 8.3 FPM | Dibangun dari `Dockerfile`. |

## Dependency PHP Penting

Dependency production dari `composer.json`:

- `laravel/framework`: framework utama aplikasi.
- `laravel/socialite`: OAuth login dengan Google.
- `midtrans/midtrans-php`: integrasi pembayaran Midtrans.
- `barryvdh/laravel-dompdf`: generate PDF invoice dan resume medis.
- `bacon/bacon-qr-code`: generate QR code untuk TOTP 2FA.
- `laravel/tinker`: REPL/debugging Laravel.

Dependency development:

- `laravel/breeze`: scaffolding autentikasi awal.
- `phpunit/phpunit`: testing.
- `laravel/pint`: code style formatter.
- `laravel/pail`: log viewer development.
- `laravel/sail`: tooling Docker development Laravel.
- `fakerphp/faker`, `mockery/mockery`, `nunomaduro/collision`: testing dan tooling development.

## Dependency JavaScript Penting

Dependency dari `package.json`:

- `vite`: build tool frontend.
- `laravel-vite-plugin`: integrasi Vite dengan Laravel.
- `axios`: request HTTP dari frontend.
- `concurrently`: menjalankan beberapa proses development.
- `postcss` dan `autoprefixer`: pipeline CSS.

Catatan: terdapat file `tailwind.config.js`, tetapi `tailwindcss` dan `@tailwindcss/forms` tidak tercatat di `package.json` maupun `package-lock.json`. UI aktif di project ini lebih jelas memakai Bootstrap CDN dan custom CSS. File `resources/views/welcome.blade.php` masih membawa CSS Tailwind bawaan Laravel, tetapi bukan pola utama aplikasi DentalCare.

## Struktur Folder Project

```text
app/
  Enums/                 Enum domain seperti role, status booking, status pembayaran
  Http/
    Controllers/         Controller publik, auth, pasien, dokter, admin, webhook
    Middleware/          Middleware role-based access
    Requests/            Form request untuk validasi
  Models/                Model Eloquent untuk entity utama
  Notifications/         Notifikasi booking, pembayaran, dan resume medis
  Providers/             Service provider Laravel
  Services/              Business logic booking, Midtrans, dan 2FA
  Support/               Helper internal aplikasi
  View/Components/       Komponen Blade layout

bootstrap/               Bootstrap aplikasi Laravel
config/                  Konfigurasi app, auth, database, queue, mail, Midtrans, 2FA
database/
  migrations/            Definisi schema database
  seeders/               Seeder data demo
  factories/             Factory test/data dummy
  demo/                  SQL demo presentasi
docker/
  nginx/                 Konfigurasi Nginx
  php/                   Entrypoint PHP container
docs/                    Dokumentasi tambahan deployment dan demo
public/                  Front controller dan asset publik
resources/
  css/                   CSS custom aplikasi
  js/                    Entry JavaScript Vite
  views/                 Blade templates
routes/                  Definisi route web, auth, console
scripts/                 Script helper deployment/presentasi
storage/                 Cache, logs, session, dan file runtime Laravel
tests/                   Test feature dan unit
```

## Struktur Area View

Folder `resources/views` dibagi sesuai area aplikasi:

- `public/`: halaman publik seperti home, services, doctors, dan partial booking.
- `auth/`: login, register, reset password, verification, dan 2FA challenge.
- `patient/`: booking pasien dan riwayat.
- `doctor/`: dashboard dokter dan medical notes.
- `admin/`: dashboard operasional admin untuk user, dokter, layanan, jadwal, pembayaran, dan laporan.
- `profile/`: update profil, password, delete account, dan setup 2FA.
- `pdf/`: template PDF invoice dan medical record.
- `layouts/` dan `components/`: layout dasar, navigasi, flash message, dan komponen form.

## Modul Backend

### Publik

Controller utama:

- `PublicController`
- `Patient\BookingController`

Fungsi utama:

- Menampilkan landing page.
- Menampilkan layanan dan dokter.
- Menyediakan form booking.

### Autentikasi

Controller berada di `app/Http/Controllers/Auth`:

- Login, register, logout.
- Reset password dan konfirmasi password.
- Email verification.
- Google OAuth via `GoogleAuthController`.
- Two-factor authentication via `TwoFactorLoginController`.

### Pasien

Controller utama:

- `Patient\BookingController`

Fungsi utama:

- Membuat booking.
- Melihat riwayat booking.
- Mengunduh invoice PDF.
- Mengunduh medical record PDF.

### Dokter

Controller utama:

- `Doctor\DashboardController`
- `Doctor\MedicalNoteController`

Fungsi utama:

- Melihat dashboard dokter.
- Membuat resume medis/medical notes untuk booking pasien.

### Admin

Controller utama:

- `Admin\UserController`
- `Admin\DoctorController`
- `Admin\ServiceController`
- `Admin\ScheduleController`
- `Admin\PaymentController`
- `Admin\ReportController`

Fungsi utama:

- Manajemen user.
- Manajemen dokter.
- Manajemen layanan klinik.
- Manajemen jadwal dokter.
- Monitoring pembayaran.
- Laporan statistik dan grafik pendapatan.

### Pembayaran

Komponen utama:

- `app/Services/MidtransService.php`
- `app/Http/Controllers/PaymentWebhookController.php`
- `config/midtrans.php`

Alur umum:

1. Booking dibuat oleh pasien.
2. Aplikasi membuat transaksi Midtrans jika konfigurasi key tersedia.
3. Midtrans mengirim callback ke route `/payments/midtrans/callback`.
4. Callback diverifikasi menggunakan signature key.
5. Status pembayaran diperbarui di database.

## Model dan Entity Utama

Model utama di `app/Models`:

- `User`: akun admin, dokter, dan pasien.
- `DoctorProfile`: profil dokter.
- `DoctorSchedule`: jadwal dokter.
- `Service`: layanan klinik gigi.
- `Booking`: reservasi pasien.
- `Payment`: data pembayaran booking.
- `MedicalNote`: resume medis.
- `TwoFactorChallenge`: sesi challenge 2FA.
- `BaseModel` dan `HasAuditFields`: fondasi audit fields.

Enum domain:

- `UserRole`
- `BookingStatus`
- `PaymentStatus`

## Database

Project memakai migration Laravel untuk schema utama. Tabel yang terlihat dari migration:

- `users`
- `doctor_profiles`
- `doctor_schedules`
- `services`
- `bookings`
- `medical_notes`
- `payments`
- `notifications`
- `jobs`
- `cache`
- `login_otp_challenges`

Database default Laravel di `config/database.php` adalah SQLite, tetapi dokumentasi dan Docker project ini mengarah ke MySQL. Untuk environment Docker, database memakai service `mysql:8.4`.

## Routing

File route utama:

- `routes/web.php`: route publik, pasien, dokter, admin, profile, dan webhook Midtrans.
- `routes/auth.php`: route auth, Google OAuth, 2FA, password reset, dan email verification.
- `routes/console.php`: command console Laravel.

Middleware penting:

- `auth`: proteksi user login.
- `role:patient`, `role:doctor`, `role:admin`: pembatasan akses berdasarkan role.

## Deployment dan Runtime

Project menyediakan Docker Compose dengan service:

- `app`: container PHP-FPM Laravel.
- `queue`: worker `php artisan queue:work database`.
- `nginx`: web server reverse proxy ke PHP-FPM.
- `mysql`: database MySQL 8.4.

File pendukung:

- `Dockerfile`: image PHP 8.3 FPM dengan Composer, Node 20, npm, ekstensi PHP, dan MySQL client.
- `docker/nginx/default.conf`: virtual host aplikasi.
- `docker/php/entrypoint.sh`: entrypoint container app.
- `Makefile`: shortcut command build, install, demo, deploy, test, audit, dan cache.

Command penting:

```bash
make build
make up
make install
make demo
make test
make verify
```

## Testing

Test suite berada di:

- `tests/Feature`: test flow aplikasi seperti auth, booking, payment webhook, medical note, report admin, dan user management.
- `tests/Unit`: test unit dasar.

Runner utama:

```bash
php artisan test
```

Di Docker, Makefile menyediakan:

```bash
make test
make verify
```

## Integrasi Eksternal

Integrasi yang terdeteksi:

- Midtrans Snap untuk pembayaran online.
- Google OAuth untuk login/register pasien.
- SMTP/mail provider untuk notifikasi email.
- Chart.js CDN untuk grafik laporan.
- Bootstrap CDN untuk UI.
- Google Fonts Manrope untuk font utama.

Environment variable penting:

- `MIDTRANS_SERVER_KEY`
- `MIDTRANS_CLIENT_KEY`
- `MIDTRANS_IS_PRODUCTION`
- `MIDTRANS_CALLBACK_URL`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- `TWO_FACTOR_ISSUER`, `TWO_FACTOR_DIGITS`, `TWO_FACTOR_PERIOD`
- `APPOINTMENT_SLOT_MINUTES`

## Catatan Teknis

- Project ini tampak berasal dari Laravel Breeze, tetapi UI aplikasi utama sudah banyak memakai Bootstrap dan custom CSS.
- File `tailwind.config.js` masih ada, namun package Tailwind tidak tercatat sebagai dependency aktif. Jika ingin memakai Tailwind kembali, dependency perlu ditambahkan dan CSS entry perlu memakai directive Tailwind.
- Queue sudah disiapkan untuk database queue dan ada container worker khusus.
- PDF invoice dan resume medis dibuat server-side dengan DomPDF.
- 2FA menggunakan implementasi TOTP custom, bukan Laravel Fortify.
- Ada script khusus untuk kebutuhan demo/presentasi dan deploy Hostinger di folder `scripts` dan `docs`.
- Worktree saat analisis sudah memiliki beberapa perubahan yang belum di-commit pada file lain; dokumentasi ini tidak mengubah file tersebut.

