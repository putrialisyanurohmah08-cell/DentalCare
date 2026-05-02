<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
            .header { margin-bottom: 24px; }
            .title { font-size: 24px; font-weight: bold; }
            .section { margin-bottom: 16px; }
            .label { font-weight: bold; margin-bottom: 6px; }
            .box { border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px; }
            .muted { color: #6b7280; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">Resume Medis Pasien</div>
            <div class="muted">{{ config('clinic.name') }} • {{ $booking->booking_code }}</div>
        </div>

        <div class="section">
            <div class="label">Informasi kunjungan</div>
            <div class="box">
                <div>Pasien: {{ $booking->patient->name }}</div>
                <div>Dokter: {{ $booking->doctor->name }}</div>
                <div>Jadwal: {{ $booking->scheduleLabel() }}</div>
                <div>Layanan: {{ $booking->service_name }}</div>
            </div>
        </div>

        <div class="section">
            <div class="label">Diagnosis</div>
            <div class="box">{{ $medicalNote->diagnosis }}</div>
        </div>

        <div class="section">
            <div class="label">Tindakan</div>
            <div class="box">{{ $medicalNote->treatment }}</div>
        </div>

        <div class="section">
            <div class="label">Resep</div>
            <div class="box">{{ $medicalNote->prescription ?: '-' }}</div>
        </div>

        <div class="section">
            <div class="label">Catatan tambahan</div>
            <div class="box">{{ $medicalNote->notes ?: '-' }}</div>
        </div>
    </body>
</html>
