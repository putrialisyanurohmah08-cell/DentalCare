USE `dentalcare_demo`;

-- =========================================================
-- 1. TUNJUKKAN TABEL YANG ADA
-- =========================================================
SHOW TABLES;

-- =========================================================
-- 2. TUNJUKKAN USER DAN ROLE
-- users menjadi pusat semua akun
-- =========================================================
SELECT
    id,
    name,
    email,
    role
FROM users
WHERE IsDeleted = 0
ORDER BY role, id;

-- =========================================================
-- 3. TUNJUKKAN DOKTER DAN PROFILNYA
-- =========================================================
SELECT
    u.name AS doctor_name,
    dp.specialization,
    dp.license_number,
    dp.experience_years
FROM users u
JOIN doctor_profiles dp
    ON dp.user_id = u.id
WHERE u.role = 'doctor'
  AND u.IsDeleted = 0
  AND dp.IsDeleted = 0
ORDER BY u.name;

-- =========================================================
-- 4. TUNJUKKAN JADWAL DOKTER
-- =========================================================
SELECT
    u.name AS doctor_name,
    CASE ds.day_of_week
        WHEN 1 THEN 'Senin'
        WHEN 2 THEN 'Selasa'
        WHEN 3 THEN 'Rabu'
        WHEN 4 THEN 'Kamis'
        WHEN 5 THEN 'Jumat'
        WHEN 6 THEN 'Sabtu'
        WHEN 7 THEN 'Minggu'
    END AS day_name,
    TIME_FORMAT(ds.start_time, '%H:%i') AS start_time,
    TIME_FORMAT(ds.end_time, '%H:%i') AS end_time,
    ds.quota
FROM doctor_schedules ds
JOIN users u
    ON u.id = ds.doctor_id
WHERE ds.IsDeleted = 0
ORDER BY u.name, ds.day_of_week;

-- =========================================================
-- 5. TUNJUKKAN ALUR UTAMA BOOKING
-- =========================================================
SELECT
    b.booking_code,
    patient.name AS patient_name,
    doctor.name AS doctor_name,
    b.service_name,
    b.booking_date,
    TIME_FORMAT(b.booking_time, '%H:%i') AS booking_time,
    b.queue_number,
    b.booking_status
FROM bookings b
JOIN users patient
    ON patient.id = b.patient_id
JOIN users doctor
    ON doctor.id = b.doctor_id
WHERE b.IsDeleted = 0
ORDER BY b.booking_date DESC, b.booking_time DESC;

-- =========================================================
-- 6. TUNJUKKAN HUBUNGAN BOOKING DAN PEMBAYARAN
-- =========================================================
SELECT
    b.booking_code,
    p.order_id,
    p.amount,
    p.payment_method,
    p.payment_status,
    p.paid_at
FROM payments p
JOIN bookings b
    ON b.id = p.booking_id
WHERE p.IsDeleted = 0
ORDER BY p.id;

-- =========================================================
-- 7. TUNJUKKAN REKAM MEDIS
-- =========================================================
SELECT
    b.booking_code,
    doctor.name AS doctor_name,
    patient.name AS patient_name,
    mn.diagnosis,
    mn.treatment
FROM medical_notes mn
JOIN bookings b
    ON b.id = mn.booking_id
JOIN users doctor
    ON doctor.id = mn.doctor_id
JOIN users patient
    ON patient.id = mn.patient_id
WHERE mn.IsDeleted = 0;

-- =========================================================
-- 8. QUERY PENUTUP: ALUR BISNIS LENGKAP
-- satu query terbaik untuk penjelasan akhir
-- =========================================================
SELECT
    b.booking_code,
    patient.name AS patient_name,
    doctor.name AS doctor_name,
    dp.specialization,
    b.service_name,
    b.booking_date,
    TIME_FORMAT(b.booking_time, '%H:%i') AS booking_time,
    b.booking_status,
    p.payment_status,
    p.amount,
    mn.diagnosis
FROM bookings b
JOIN users patient
    ON patient.id = b.patient_id
JOIN users doctor
    ON doctor.id = b.doctor_id
LEFT JOIN doctor_profiles dp
    ON dp.user_id = doctor.id
   AND dp.IsDeleted = 0
LEFT JOIN payments p
    ON p.booking_id = b.id
   AND p.IsDeleted = 0
LEFT JOIN medical_notes mn
    ON mn.booking_id = b.id
   AND mn.IsDeleted = 0
WHERE b.IsDeleted = 0
ORDER BY b.booking_date DESC, b.booking_time DESC;
