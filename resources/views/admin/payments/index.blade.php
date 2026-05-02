@extends('layouts.app')

@section('title', 'Pembayaran | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Monitoring Pembayaran')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Pasien</th>
                            <th>Dokter</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="fw-semibold">{{ $payment->order_id }}</td>
                                <td>{{ $payment->booking->patient->name }}</td>
                                <td>{{ $payment->booking->doctor->name }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>{{ $payment->payment_method ?: '-' }}</td>
                                <td><span class="badge text-bg-{{ $payment->badgeClass() }}">{{ $payment->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">Belum ada pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection
