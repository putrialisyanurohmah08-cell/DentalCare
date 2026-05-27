<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Resume Medis - {{ $booking->booking_code }}</title>
        <style>
            @page {
                margin: 40px;
            }
            body {
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                color: #212529;
                font-size: 11px;
                line-height: 1.5;
            }
            /* Header */
            .header-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
                margin-bottom: 5px;
            }
            .header-table td {
                border: none;
                padding: 0;
                vertical-align: middle;
            }
            .logo-text {
                font-size: 24px;
                font-weight: bold;
                color: #0b7285;
                letter-spacing: 0.5px;
                margin: 0;
            }
            .logo-subtitle {
                font-size: 9px;
                color: #868e96;
                margin: 2px 0 0 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .doc-title {
                font-size: 20px;
                font-weight: bold;
                color: #495057;
                text-align: right;
                margin: 0;
            }
            .doc-meta {
                text-align: right;
                font-size: 10px;
                color: #495057;
                margin: 2px 0 0 0;
            }
            .divider-main {
                height: 3px;
                background-color: #0b7285;
                margin-top: 15px;
                margin-bottom: 25px;
            }

            /* Info Kunjungan */
            .info-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
                margin-bottom: 25px;
            }
            .info-table td {
                border: none;
                padding: 0;
                vertical-align: top;
                width: 50%;
            }
            .info-section-title {
                font-size: 8px;
                font-weight: bold;
                color: #868e96;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 6px;
            }
            .info-text-bold {
                font-size: 12px;
                font-weight: bold;
                color: #212529;
                margin-bottom: 4px;
            }
            .info-text-normal {
                font-size: 10px;
                color: #495057;
                margin-bottom: 3px;
            }

            /* Section Cards */
            .section-card {
                border: 1px solid #e9ecef;
                border-radius: 6px;
                padding: 12px 15px;
                margin-bottom: 16px;
            }
            .border-diagnosis {
                border-left: 4px solid #0b7285;
                background-color: #f1f7f9;
            }
            .border-treatment {
                border-left: 4px solid #1098ad;
                background-color: #f3f8fa;
            }
            .border-prescription {
                border-left: 4px solid #12b886;
                background-color: #f4fbf7;
            }
            .border-notes {
                border-left: 4px solid #6c757d;
                background-color: #f8f9fa;
            }
            .section-title {
                font-size: 8px;
                font-weight: bold;
                color: #868e96;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 5px;
            }
            .section-body {
                font-size: 11px;
                color: #212529;
                line-height: 1.4;
            }

            /* Signature */
            .sig-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
                margin-top: 50px;
            }
            .sig-table td {
                border: none;
                padding: 0;
                vertical-align: top;
            }
            .sig-title {
                font-size: 10px;
                color: #495057;
                margin-bottom: 60px;
            }
            .sig-name {
                font-size: 11px;
                font-weight: bold;
                color: #212529;
                margin: 0;
            }
            .sig-license {
                font-size: 9px;
                color: #868e96;
                margin: 2px 0 0 0;
            }
        </style>
    </head>
    <body>
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="logo-text">{{ strtoupper(config('clinic.name', 'DENTALCARE')) }}</h1>
                    <p class="logo-subtitle">Klinik Gigi &amp; Perawatan Mulut</p>
                </td>
                <td>
                    <h2 class="doc-title">RESUME MEDIS PASIEN</h2>
                    <p class="doc-meta">
                        Kode Booking: <strong>{{ $booking->booking_code }}</strong><br>
                        Tanggal Cetak: {{ now()->translatedFormat('d F Y') }}
                    </p>
                </td>
            </tr>
        </table>

        <div class="divider-main"></div>

        <!-- Info Kunjungan -->
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-section-title">Identitas Pasien:</div>
                    <div class="info-text-bold">{{ $booking->patient->name }}</div>
                    <div class="info-text-normal">Telepon: {{ $booking->patient->phone }}</div>
                    <div class="info-text-normal">Email: {{ $booking->patient->email }}</div>
                </td>
                <td style="text-align: right; padding-right: 5px;">
                    <div class="info-section-title">Detail Kunjungan Medis:</div>
                    <div class="info-text-normal">Dokter Pemeriksa:</div>
                    <div class="info-text-bold" style="font-size: 11px; margin-bottom: 6px;">{{ $booking->doctor->name }}</div>
                    <div class="info-text-normal">Layanan / Tindakan: <strong>{{ $booking->service_name }}</strong></div>
                    <div class="info-text-normal">Waktu Kunjungan: <strong>{{ $booking->scheduleLabel() }}</strong></div>
                </td>
            </tr>
        </table>

        <!-- Diagnosis Card -->
        <div class="section-card border-diagnosis">
            <div class="section-title">Diagnosis Medis</div>
            <div class="section-body">{{ $medicalNote->diagnosis }}</div>
        </div>

        <!-- Treatment Card -->
        <div class="section-card border-treatment">
            <div class="section-title">Tindakan / Perawatan</div>
            <div class="section-body">{{ $medicalNote->treatment }}</div>
        </div>

        <!-- Prescription Card -->
        <div class="section-card border-prescription">
            <div class="section-title">Resep Obat</div>
            <div class="section-body">{{ $medicalNote->prescription ?: '-' }}</div>
        </div>

        <!-- Notes Card -->
        <div class="section-card border-notes">
            <div class="section-title">Catatan Tambahan / Edukasi</div>
            <div class="section-body">{{ $medicalNote->notes ?: '-' }}</div>
        </div>

        <!-- Signature Block -->
        <table class="sig-table">
            <tr>
                <td style="width: 65%;"></td>
                <td style="width: 35%; text-align: center;">
                    <div class="sig-title">Dokter Pemeriksa,</div>
                    <div style="height: 50px;"></div>
                    <div class="sig-name">{{ $booking->doctor->name }}</div>
                    <div class="sig-license">No. Izin: {{ $booking->doctor->doctorProfile?->license_number ?: '-' }}</div>
                </td>
            </tr>
        </table>
    </body>
</html>
