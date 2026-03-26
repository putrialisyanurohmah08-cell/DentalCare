-- DentalCare Lite
-- Standalone demo database for MySQL 8+
-- Tujuan:
-- 1. Bisa di-import langsung ke MySQL lokal tanpa harus menjalankan Docker / Laravel
-- 2. Meniru struktur database inti project untuk kebutuhan presentasi

SET NAMES utf8mb4;
SET time_zone = '+07:00';

DROP DATABASE IF EXISTS `dentalcare_demo`;
CREATE DATABASE `dentalcare_demo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `dentalcare_demo`;

SET @company_code = 'DCL';
SET @audit_user = 'system';
SET @audit_now = '2026-03-26 08:00:00';
SET @password_hash = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/.AyD0e.vfD2y.';

-- =========================================================
-- TABEL FRAMEWORK / SUPPORT
-- =========================================================

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(32) NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'patient',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB;

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB;

-- =========================================================
-- TABEL DOMAIN BISNIS
-- =========================================================

CREATE TABLE `doctor_profiles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `specialization` VARCHAR(255) NOT NULL,
  `license_number` VARCHAR(255) NOT NULL,
  `biography` TEXT NULL,
  `experience_years` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctor_profiles_license_number_unique` (`license_number`),
  KEY `doctor_profiles_user_id_index` (`user_id`),
  CONSTRAINT `doctor_profiles_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `doctor_schedules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` BIGINT UNSIGNED NOT NULL,
  `day_of_week` TINYINT UNSIGNED NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `quota` INT UNSIGNED NOT NULL DEFAULT 10,
  `slot_minutes` INT UNSIGNED NOT NULL DEFAULT 30,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctor_schedules_doctor_day_unique` (`doctor_id`, `day_of_week`),
  CONSTRAINT `doctor_schedules_doctor_id_foreign`
    FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `services` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `duration_minutes` INT UNSIGNED NOT NULL DEFAULT 30,
  `price` DECIMAL(12,2) NOT NULL,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_slug_unique` (`slug`)
) ENGINE=InnoDB;

CREATE TABLE `bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_code` VARCHAR(255) NOT NULL,
  `patient_id` BIGINT UNSIGNED NOT NULL,
  `doctor_id` BIGINT UNSIGNED NOT NULL,
  `service_id` BIGINT UNSIGNED NOT NULL,
  `booking_date` DATE NOT NULL,
  `booking_time` TIME NOT NULL,
  `queue_number` INT UNSIGNED NOT NULL,
  `booking_status` VARCHAR(32) NOT NULL DEFAULT 'pending_payment',
  `service_name` VARCHAR(255) NOT NULL,
  `service_price` DECIMAL(12,2) NOT NULL,
  `notes` TEXT NULL,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_booking_code_unique` (`booking_code`),
  KEY `bookings_doctor_date_index` (`doctor_id`, `booking_date`),
  KEY `bookings_patient_date_index` (`patient_id`, `booking_date`),
  KEY `bookings_service_id_index` (`service_id`),
  CONSTRAINT `bookings_patient_id_foreign`
    FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `bookings_doctor_id_foreign`
    FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `bookings_service_id_foreign`
    FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `medical_notes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `doctor_id` BIGINT UNSIGNED NOT NULL,
  `patient_id` BIGINT UNSIGNED NOT NULL,
  `diagnosis` LONGTEXT NOT NULL,
  `treatment` LONGTEXT NOT NULL,
  `prescription` LONGTEXT NULL,
  `notes` TEXT NULL,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medical_notes_booking_id_unique` (`booking_id`),
  KEY `medical_notes_doctor_id_index` (`doctor_id`),
  KEY `medical_notes_patient_id_index` (`patient_id`),
  CONSTRAINT `medical_notes_booking_id_foreign`
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `medical_notes_doctor_id_foreign`
    FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `medical_notes_patient_id_foreign`
    FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `order_id` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_method` VARCHAR(64) NULL,
  `payment_type` VARCHAR(64) NULL,
  `payment_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `snap_token` VARCHAR(255) NULL,
  `redirect_url` TEXT NULL,
  `transaction_id` VARCHAR(128) NULL,
  `raw_response` JSON NULL,
  `paid_at` DATETIME NULL,
  `CompanyCode` VARCHAR(20) NOT NULL,
  `Status` TINYINT NOT NULL DEFAULT 1,
  `IsDeleted` TINYINT NOT NULL DEFAULT 0,
  `CreatedBy` VARCHAR(32) NOT NULL,
  `CreatedDate` DATETIME NOT NULL,
  `LastUpdatedBy` VARCHAR(32) NOT NULL,
  `LastUpdatedDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_booking_id_unique` (`booking_id`),
  UNIQUE KEY `payments_order_id_unique` (`order_id`),
  CONSTRAINT `payments_booking_id_foreign`
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `notifications` (
  `id` CHAR(36) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `data` TEXT NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB;

-- =========================================================
-- DATA DEMO
-- =========================================================

INSERT INTO `users`
(`id`, `name`, `email`, `phone`, `role`, `email_verified_at`, `password`, `remember_token`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 'Admin DentalCare', 'admin@dentalcare.test', '081234567890', 'admin',   @audit_now, @password_hash, NULL, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(2, 'Putri Alisha',     'patient@dentalcare.test', '081298765432', 'patient', @audit_now, @password_hash, NULL, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(3, 'drg. Aji Pratama', 'dr.aji@dentalcare.test',  '081200000111', 'doctor',  @audit_now, @password_hash, NULL, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(4, 'drg. Salsa Maharani', 'dr.salsa@dentalcare.test', '081200000222', 'doctor', @audit_now, @password_hash, NULL, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(5, 'drg. Rizky Hanif', 'dr.rizky@dentalcare.test', '081200000333', 'doctor', @audit_now, @password_hash, NULL, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now);

INSERT INTO `doctor_profiles`
(`id`, `user_id`, `specialization`, `license_number`, `biography`, `experience_years`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 3, 'Konservasi Gigi', 'SIP-DC-001', 'Fokus pada tambal gigi estetik dan perawatan akar dengan pendekatan ramah pasien.', 8, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(2, 4, 'Ortodonti', 'SIP-DC-002', 'Menangani konsultasi behel dan perawatan susunan gigi untuk remaja maupun dewasa.', 6, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(3, 5, 'Bedah Mulut', 'SIP-DC-003', 'Berpengalaman dalam tindakan cabut gigi bungsu dan prosedur bedah minor.', 10, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now);

INSERT INTO `doctor_schedules`
(`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `quota`, `slot_minutes`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 3, 1, '09:00:00', '15:00:00', 10, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(2, 3, 3, '09:00:00', '15:00:00', 10, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(3, 3, 5, '10:00:00', '16:00:00',  8, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(4, 4, 1, '09:00:00', '15:00:00', 10, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(5, 4, 3, '09:00:00', '15:00:00', 10, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(6, 4, 5, '10:00:00', '16:00:00',  8, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(7, 5, 1, '09:00:00', '15:00:00', 10, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(8, 5, 3, '09:00:00', '15:00:00', 10, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(9, 5, 5, '10:00:00', '16:00:00',  8, 30, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now);

INSERT INTO `services`
(`id`, `name`, `slug`, `description`, `duration_minutes`, `price`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 'Scaling', 'scaling', 'Pembersihan karang gigi dengan evaluasi awal kondisi gusi.', 45, 350000.00, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(2, 'Cabut Gigi', 'cabut-gigi', 'Tindakan pencabutan gigi dengan anestesi lokal dan observasi pasca tindakan.', 60, 500000.00, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(3, 'Tambal Estetik', 'tambal-estetik', 'Perawatan gigi berlubang menggunakan bahan komposit warna gigi.', 50, 425000.00, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now),
(4, 'Konsultasi Ortodonti', 'konsultasi-ortodonti', 'Pemeriksaan susunan gigi dan rekomendasi perawatan behel.', 40, 250000.00, @company_code, 1, 0, @audit_user, @audit_now, @audit_user, @audit_now);

INSERT INTO `bookings`
(`id`, `booking_code`, `patient_id`, `doctor_id`, `service_id`, `booking_date`, `booking_time`, `queue_number`, `booking_status`, `service_name`, `service_price`, `notes`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 'DC-DEMO001', 2, 3, 1, '2026-03-21', '10:00:00', 1, 'completed',        'Scaling',                350000.00, 'Ingin membersihkan karang gigi dan cek sensitivitas.', @company_code, 1, 0, @audit_user, '2026-03-21 09:00:00', @audit_user, '2026-03-21 11:00:00'),
(2, 'DC-DEMO002', 2, 4, 4, '2026-03-28', '09:30:00', 2, 'confirmed',        'Konsultasi Ortodonti',   250000.00, 'Konsultasi posisi gigi depan.', @company_code, 1, 0, @audit_user, '2026-03-26 09:00:00', @audit_user, '2026-03-26 09:00:00'),
(3, 'DC-DEMO003', 2, 5, 2, '2026-03-30', '10:30:00', 3, 'pending_payment',  'Cabut Gigi',             500000.00, 'Keluhan gigi bungsu.', @company_code, 1, 0, @audit_user, '2026-03-26 10:00:00', @audit_user, '2026-03-26 10:00:00');

INSERT INTO `payments`
(`id`, `booking_id`, `order_id`, `amount`, `payment_method`, `payment_type`, `payment_status`, `snap_token`, `redirect_url`, `transaction_id`, `raw_response`, `paid_at`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 1, 'PAY-DC-DEMO001', 350000.00, 'BCA VA', 'bank_transfer', 'paid',    'demo-paid-token', NULL, 'txn-demo-paid', JSON_OBJECT('seeded', TRUE, 'status', 'paid'), '2026-03-21 11:05:00', @company_code, 1, 0, @audit_user, '2026-03-21 09:01:00', @audit_user, '2026-03-21 11:05:00'),
(2, 2, 'PAY-DC-DEMO002', 250000.00, 'GoPay',  'gopay',         'paid',    'demo-confirmed-token', 'https://app.sandbox.midtrans.com/snap/v4/redirection/demo-confirmed', 'txn-demo-confirmed', JSON_OBJECT('seeded', TRUE, 'status', 'paid'), '2026-03-27 13:00:00', @company_code, 1, 0, @audit_user, '2026-03-26 09:01:00', @audit_user, '2026-03-27 13:00:00'),
(3, 3, 'PAY-DC-DEMO003', 500000.00, NULL,     NULL,            'pending', 'demo-pending-token', 'https://app.sandbox.midtrans.com/snap/v4/redirection/demo-pending', NULL, JSON_OBJECT('seeded', TRUE, 'status', 'pending'), NULL, @company_code, 1, 0, @audit_user, '2026-03-26 10:01:00', @audit_user, '2026-03-26 10:01:00');

INSERT INTO `medical_notes`
(`id`, `booking_id`, `doctor_id`, `patient_id`, `diagnosis`, `treatment`, `prescription`, `notes`, `CompanyCode`, `Status`, `IsDeleted`, `CreatedBy`, `CreatedDate`, `LastUpdatedBy`, `LastUpdatedDate`)
VALUES
(1, 1, 3, 2, 'Karang gigi ringan dengan sensitivitas pada gigi depan bawah.', 'Dilakukan scaling ultrasonik dan edukasi teknik menyikat gigi.', 'Pasta gigi untuk gigi sensitif, digunakan dua kali sehari.', 'Kontrol ulang bila sensitivitas tidak membaik dalam 2 minggu.', @company_code, 1, 0, @audit_user, '2026-03-21 11:10:00', @audit_user, '2026-03-21 11:10:00');

INSERT INTO `notifications`
(`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`)
VALUES
('11111111-1111-1111-1111-111111111111', 'App\\Notifications\\BookingCreatedNotification', 'App\\Models\\User', 2, '{"title":"Reservasi berhasil dibuat","message":"Reservasi DC-DEMO003 telah dibuat dan menunggu pembayaran.","url":"http://localhost/history"}', NULL, '2026-03-26 10:02:00', '2026-03-26 10:02:00'),
('22222222-2222-2222-2222-222222222222', 'App\\Notifications\\PaymentPaidNotification', 'App\\Models\\User', 2, '{"title":"Pembayaran berhasil","message":"Pembayaran reservasi DC-DEMO002 telah dikonfirmasi.","url":"http://localhost/history"}', NULL, '2026-03-27 13:01:00', '2026-03-27 13:01:00'),
('33333333-3333-3333-3333-333333333333', 'App\\Notifications\\MedicalNoteReadyNotification', 'App\\Models\\User', 2, '{"title":"Resume medis siap","message":"Resume medis untuk reservasi DC-DEMO001 tersedia.","url":"http://localhost/history/DC-DEMO001/medical-record"}', '2026-03-21 12:00:00', '2026-03-21 11:15:00', '2026-03-21 12:00:00');

-- =========================================================
-- QUERY CEK CEPAT SETELAH IMPORT
-- =========================================================

SELECT DATABASE() AS active_database;
SHOW TABLES;

