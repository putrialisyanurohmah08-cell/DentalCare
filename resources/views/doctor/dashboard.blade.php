@extends('layouts.app')

@section('title', 'Dashboard Dokter | '.config('clinic.name'))
@section('page_kicker', 'Area Dokter')
@section('page_title', 'Dashboard Dokter')

@section('content')
    <div class="row g-4 mb-4">
        @foreach ([
            ['label' => 'Reservasi hari ini', 'value' => $stats['today']],
            ['label' => 'Terkonfirmasi', 'value' => $stats['confirmed']],
            ['label' => 'Selesai', 'value' => $stats['completed']],
            ['label' => 'Pendapatan', 'value' => 'Rp '.number_format($stats['revenue'], 0, ',', '.')],
        ] as $item)
            <div class="col-xl-3 col-sm-6">
                <div class="metric-card h-100">
                    <div class="small text-secondary mb-2">{{ $item['label'] }}</div>
                    <div class="h2 fw-bold mb-0">{{ $item['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Daftar pasien hari ini</h2>
                    <p class="text-secondary small mb-0">Pantau antrean dan lanjutkan ke pengisian resume medis.</p>
                </div>
                <a class="btn btn-outline-primary rounded-pill px-3" href="{{ route('doctor.medical-notes.index') }}">Kelola rekam medis</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>No. antrean</th>
                            <th>Pasien</th>
                            <th>Layanan</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todayBookings as $booking)
                            <tr>
                                <td>#{{ $booking->queue_number }}</td>
                                <td>{{ $booking->patient->name }}</td>
                                <td>{{ $booking->service_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                                <td><span class="badge text-bg-{{ $booking->badgeClass() }}">{{ $booking->statusLabel() }}</span></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-dark rounded-pill" href="{{ route('doctor.medical-notes.create', $booking) }}">
                                        {{ $booking->medicalNote ? 'Edit catatan' : 'Isi catatan' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">Belum ada pasien untuk hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
