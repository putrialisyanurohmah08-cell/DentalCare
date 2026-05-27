<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Invoice - {{ $booking->booking_code }}</title>
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
            .invoice-title {
                font-size: 20px;
                font-weight: bold;
                color: #495057;
                text-align: right;
                margin: 0;
            }
            .invoice-meta {
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

            /* Info Tagihan */
            .info-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
                margin-bottom: 30px;
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
            .status-badge {
                background-color: #e6fcf5;
                color: #0ca678;
                border: 1px solid #c3fae8;
                border-radius: 4px;
                padding: 3px 8px;
                font-weight: bold;
                font-size: 9px;
                display: inline-block;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 4px;
            }

            /* Item Table */
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .items-table th {
                background-color: #0b7285;
                color: #ffffff;
                font-weight: bold;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 10px 12px;
                border: none;
            }
            .items-table td {
                padding: 12px;
                border-bottom: 1px solid #e9ecef;
                font-size: 10px;
                color: #495057;
                vertical-align: middle;
            }
            .items-table tr.zebra {
                background-color: #f8f9fa;
            }
            .item-name {
                font-weight: bold;
                color: #212529;
                font-size: 11px;
                margin-bottom: 3px;
            }
            .item-subtext {
                font-size: 9px;
                color: #868e96;
                font-style: italic;
            }

            /* Summary */
            .summary-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
                margin-top: 10px;
            }
            .summary-table td {
                border: none;
                padding: 0;
                vertical-align: top;
            }
            .note-box {
                font-size: 9px;
                color: #868e96;
                font-style: italic;
                line-height: 1.4;
                padding-right: 30px;
            }
            .total-box {
                background-color: #f1f7f9;
                border: 1px solid #0b7285;
                border-radius: 6px;
                padding: 12px 15px;
                text-align: right;
            }
            .total-label {
                font-size: 9px;
                font-weight: bold;
                color: #868e96;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 4px;
            }
            .total-amount {
                font-size: 18px;
                font-weight: bold;
                color: #0b7285;
            }

            /* Footer */
            .footer {
                margin-top: 80px;
                text-align: center;
                border-top: 1px dashed #dee2e6;
                padding-top: 15px;
            }
            .footer-msg {
                font-size: 10px;
                color: #868e96;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        @php
            $rawMethod = $booking->payment->payment_method ?: ($booking->payment->payment_type ?: 'Midtrans');
            $methodKey = strtolower(str_replace([' ', '_', '-'], '', $rawMethod));
            $methodLabel = match ($methodKey) {
                'creditcard' => 'Kartu Kredit',
                'banktransfer' => 'Transfer Bank',
                'gopay' => 'GoPay',
                'qris' => 'QRIS',
                'shopeepay' => 'ShopeePay',
                'bcava' => 'BCA Virtual Account',
                'bniva' => 'BNI Virtual Account',
                'briva' => 'BRI Virtual Account',
                'cimbva' => 'CIMB Virtual Account',
                'mandiribill' => 'Mandiri Bill Payment',
                'permatava' => 'Permata Virtual Account',
                default => ucwords(str_replace(['_', '-'], ' ', $rawMethod)),
            };
        @endphp

        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="logo-text">{{ strtoupper(config('clinic.name', 'DENTALCARE')) }}</h1>
                    <p class="logo-subtitle">Klinik Gigi &amp; Perawatan Mulut</p>
                </td>
                <td>
                    <h2 class="invoice-title">INVOICE PEMBAYARAN</h2>
                    <p class="invoice-meta">
                        Kode Booking: <strong>{{ $booking->booking_code }}</strong><br>
                        Tanggal: {{ \Carbon\Carbon::parse($booking->payment->paid_at)->translatedFormat('d F Y') }}
                    </p>
                </td>
            </tr>
        </table>

        <div class="divider-main"></div>

        <!-- Info Section -->
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-section-title">Ditagihkan Kepada:</div>
                    <div class="info-text-bold">{{ $booking->patient->name }}</div>
                    <div class="info-text-normal">Telepon: {{ $booking->patient->phone }}</div>
                    <div class="info-text-normal">Email: {{ $booking->patient->email }}</div>
                    <div class="info-text-normal" style="margin-top: 5px;">
                        Jadwal Kunjungan: <br>
                        <strong>{{ $booking->scheduleLabel() }}</strong>
                    </div>
                </td>
                <td style="text-align: right; padding-right: 5px;">
                    <div class="info-section-title">Detail Pembayaran:</div>
                    <div class="info-text-normal">Metode Pembayaran:</div>
                    <div class="info-text-bold" style="font-size: 11px; margin-bottom: 6px;">{{ $methodLabel }}</div>
                    <div class="info-text-normal">Waktu Pembayaran:</div>
                    <div class="info-text-normal" style="font-weight: bold; color: #212529;">
                        {{ \Carbon\Carbon::parse($booking->payment->paid_at)->translatedFormat('d F Y H:i') }}
                    </div>
                    <div>
                        <span class="status-badge">Lunas</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">No</th>
                    <th style="width: 57%; text-align: left;">Deskripsi Layanan / Tindakan</th>
                    <th style="width: 15%; text-align: center;">Kuantitas</th>
                    <th style="width: 20%; text-align: right;">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>
                        <div class="item-name">{{ $booking->service_name }}</div>
                        <div class="item-subtext">Dokter Pelaksana: {{ $booking->doctor->name }}</div>
                    </td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right; font-weight: bold; color: #212529;">
                        Rp {{ number_format($booking->payment->amount, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <table class="summary-table">
            <tr>
                <td style="width: 60%;">
                    <div class="note-box">
                        <strong>Catatan Penting:</strong><br>
                        - Lembar ini merupakan bukti pembayaran resmi yang sah dari {{ config('clinic.name') }}.<br>
                        - Harap simpan bukti pembayaran ini untuk keperluan catatan medis Anda atau klaim asuransi jika diperlukan.
                    </div>
                </td>
                <td style="width: 40%;">
                    <div class="total-box">
                        <div class="total-label">Total Tagihan</div>
                        <div class="total-amount">Rp {{ number_format($booking->payment->amount, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-msg">Terima kasih atas kepercayaan Anda kepada {{ config('clinic.name') }}. Semoga lekas sembuh dan senyum Anda selalu sehat!</p>
        </div>
    </body>
</html>
