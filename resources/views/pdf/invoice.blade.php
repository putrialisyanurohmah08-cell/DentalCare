<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
            .header { margin-bottom: 24px; }
            .title { font-size: 24px; font-weight: bold; }
            .muted { color: #6b7280; }
            table { width: 100%; border-collapse: collapse; margin-top: 16px; }
            th, td { padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: left; }
            .total { margin-top: 24px; text-align: right; font-size: 16px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">Invoice Pembayaran</div>
            <div class="muted">{{ config('clinic.name') }} • {{ $booking->booking_code }}</div>
        </div>

        <table>
            <tr>
                <th>Pasien</th>
                <td>{{ $booking->patient->name }}</td>
            </tr>
            <tr>
                <th>Dokter</th>
                <td>{{ $booking->doctor->name }}</td>
            </tr>
            <tr>
                <th>Jadwal</th>
                <td>{{ $booking->scheduleLabel() }}</td>
            </tr>
            <tr>
                <th>Layanan</th>
                <td>{{ $booking->service_name }}</td>
            </tr>
            <tr>
                <th>Metode</th>
                <td>{{ $booking->payment->payment_method ?: 'Midtrans' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $booking->payment->statusLabel() }}</td>
            </tr>
        </table>

        <div class="total">
            Total: Rp {{ number_format($booking->payment->amount, 0, ',', '.') }}
        </div>
    </body>
</html>
