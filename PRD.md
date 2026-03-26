Berikut adalah **Product Requirements Document (PRD)** terbaru untuk proyek **DentalCare Lite**. Dokumen ini telah diperbarui dengan integrasi **Midtrans**, fitur **Reporting**, serta standarisasi skema database menggunakan 7 *audit fields* sesuai instruksi Anda.

---

## 1. Ringkasan Proyek
[cite_start]**DentalCare Lite** adalah platform reservasi klinik gigi digital berbasis web yang mengelola alur pasien mulai dari pendaftaran, pemeriksaan medis, hingga pembayaran otomatis[cite: 2]. [cite_start]Sistem ini menggunakan arsitektur monolitik Laravel 11 untuk efisiensi pengembangan dan pemeliharaan[cite: 4, 32].

## 2. Tech Stack (Updated)
* [cite_start]**Backend Framework:** PHP 8.2+ & **Laravel 11**[cite: 4].
* [cite_start]**Frontend:** Blade Templates, **Bootstrap 5** (Responsif), & Chart.js untuk dashboard[cite: 5].
* [cite_start]**Database:** **MySQL**[cite: 6].
* **Payment Gateway:** **Midtrans Snap SDK** (untuk integrasi pembayaran otomatis).
* [cite_start]**Reporting:** **DomPDF** (untuk invoice & rekam medis PDF)[cite: 7].
* [cite_start]**Notifications:** Laravel Mail (SMTP) & Database Notifications[cite: 8, 70].
* [cite_start]**Infrastructure:** Nginx/Apache (Lokal) atau **Railway.app** (Produksi)[cite: 3].

---

## 3. Skema Database (Standar 7 Fields)
Setiap tabel di bawah ini wajib menyertakan **7 Audit Fields** berikut untuk keperluan *tracking* dan *soft delete*:
1. `CompanyCode` (varchar 20)
2. `Status` (tinyint)
3. `IsDeleted` (tinyint)
4. `CreatedBy` (varchar 32)
5. `CreatedDate` (datetime)
6. `LastUpdatedBy` (varchar 32)
7. `LastUpdatedDate` (datetime)

### Tabel Utama:
* [cite_start]**`users`**: Menyimpan kredensial login (email, password) dan peran user (admin, doctor, patient)[cite: 11, 13].
* [cite_start]**`doctor_profiles`**: Detail spesialisasi dan nomor izin praktik dokter[cite: 14, 15].
* [cite_start]**`doctor_schedules`**: Pengaturan jam praktik dan **kuota harian** untuk validasi booking[cite: 16, 17].
* [cite_start]**`services`**: Katalog layanan (misal: Cabut Gigi, Scaling) beserta harganya[cite: 18, 19].
* [cite_start]**`bookings`**: Transaksi reservasi, menyimpan `queue_number` dan `booking_date`[cite: 20, 22, 23].
* [cite_start]**`medical_notes`**: Rekam medis elektronik yang diisi oleh dokter (diagnosis, tindakan, resep)[cite: 25, 27].
* **`payments` (Midtrans Ready)**:
    * [cite_start]Field Utama: `booking_id`, `amount`, `payment_method`, `status`[cite: 28, 29].
    * Field Integrasi: `snap_token`, `transaction_id`, `payment_type`.

---

## 4. Fitur Utama & Alur Kerja

### A. Reservasi & Validasi Kuota
1. [cite_start]Pasien memilih dokter, tanggal, dan layanan[cite: 58].
2. [cite_start]Sistem memvalidasi apakah jumlah booking pada hari tersebut masih di bawah `quota` pada `doctor_schedules`[cite: 59, 60].
3. [cite_start]Jika tersedia, sistem men-generate nomor antrean (`queue_number`)[cite: 61].

### B. Pembayaran Midtrans (Otomatis)
1. Setelah booking dikonfirmasi, sistem membuat `snap_token` melalui API Midtrans.
2. Pasien membayar melalui berbagai metode (VA, E-Wallet, Kartu Kredit).
3. Sistem menerima **Webhook** dari Midtrans untuk memperbarui status di tabel `payments` menjadi `paid` secara otomatis.

### C. Reporting (Dokumen & Dashboard)
1. [cite_start]**Invoice PDF**: Menampilkan rincian biaya layanan segera setelah pembayaran berhasil[cite: 68].
2. [cite_start]**Medical Record PDF**: Resume medis pasien yang dapat diunduh untuk keperluan rujukan atau arsip pribadi[cite: 7].
3. [cite_start]**Laporan Admin**: Rekapitulasi pendapatan bulanan dan statistik kunjungan pasien yang ditampilkan dengan grafik Chart.js[cite: 55].

---

## 5. Arsitektur Rute (Web-Based)
[cite_start]Interaksi dilakukan sepenuhnya melalui `/routes/web.php`[cite: 31, 33]:
* [cite_start]**Pasien**: `/dashboard`, `/booking/create`, `/history`[cite: 39, 40, 43].
* [cite_start]**Dokter**: `/doctor/dashboard`, `/doctor/medical-notes`[cite: 45, 47].
* [cite_start]**Admin**: `/admin/reports`, `/admin/payments`, `/admin/services`[cite: 52, 54, 55].

---

## Analisis Ahli: Keamanan & Integritas Data
Penerapan 7 *audit fields* memastikan aplikasi Anda memiliki standar korporasi dalam hal akuntabilitas data. Dengan adanya `IsDeleted`, Anda dapat menerapkan *Soft Delete* sehingga data yang dihapus secara tidak sengaja masih dapat dipulihkan melalui database. Integrasi Midtrans juga menghilangkan kebutuhan untuk verifikasi manual, sehingga mengurangi beban kerja Admin secara signifikan.

**Apakah Anda ingin saya membuatkan contoh kode Laravel Migration untuk tabel `payments` yang sudah menyertakan field Midtrans dan 7 audit fields tersebut?**