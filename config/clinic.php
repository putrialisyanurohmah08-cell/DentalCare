<?php

return [
    'name' => env('CLINIC_NAME', 'DentalCare Lite'),
    'company_code' => env('APP_COMPANY_CODE', 'DCL'),
    'tagline' => env('CLINIC_TAGLINE', 'Klinik gigi modern untuk keluarga dengan reservasi online yang mudah.'),
    'address' => env('CLINIC_ADDRESS', 'Jl. Senyum Sehat No. 12, Jakarta'),
    'phone' => env('CLINIC_PHONE', '+62 812-3456-7890'),
    'email' => env('CLINIC_EMAIL', 'halo@dentalcarelite.test'),
    'slot_minutes' => (int) env('APPOINTMENT_SLOT_MINUTES', 30),
];
