@extends('layouts.app')

@section('title', 'Riwayat Reservasi | '.config('clinic.name'))
@section('page_kicker', 'Area Pasien')
@section('page_title', 'Riwayat Reservasi')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Dokter & layanan</th>
                            <th>Jadwal</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="fw-semibold">{{ $booking->booking_code }}</td>
                                <td>
                                    <div>{{ $booking->doctor->name }}</div>
                                    <div class="small text-secondary">{{ $booking->service_name }}</div>
                                </td>
                                <td>
                                    <div>{{ $booking->scheduleLabel() }}</div>
                                    <span class="badge text-bg-{{ $booking->badgeClass() }}">{{ $booking->statusLabel() }}</span>
                                </td>
                                <td>
                                    @if ($booking->payment)
                                        <div class="fw-semibold">Rp {{ number_format($booking->payment->amount, 0, ',', '.') }}</div>
                                        <span class="badge text-bg-{{ $booking->payment->badgeClass() }}">{{ $booking->payment->statusLabel() }}</span>
                                    @else
                                        <span class="text-secondary">Belum ada pembayaran</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-grid gap-2">
                                        @if ($booking->payment && $booking->payment->payment_status === \App\Enums\PaymentStatus::Pending && $booking->payment->redirect_url)
                                            <a class="btn btn-sm btn-primary rounded-pill" href="{{ $booking->payment->redirect_url }}" target="_blank">Bayar sekarang</a>
                                        @endif

                                        @if ($booking->payment && $booking->payment->payment_status === \App\Enums\PaymentStatus::Paid)
                                            <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('history.invoice', $booking) }}">Unduh invoice</a>
                                        @endif

                                        @if ($booking->medicalNote)
                                            <a class="btn btn-sm btn-outline-dark rounded-pill" href="{{ route('history.medical-record', $booking) }}">Resume medis</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary">Belum ada riwayat reservasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
@endsection
