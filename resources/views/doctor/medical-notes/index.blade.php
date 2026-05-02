@extends('layouts.app')

@section('title', 'Rekam Medis | '.config('clinic.name'))
@section('page_kicker', 'Area Dokter')
@section('page_title', 'Rekam Medis')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pasien</th>
                            <th>Jadwal</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_code }}</td>
                                <td>{{ $booking->patient->name }}</td>
                                <td>{{ $booking->scheduleLabel() }}</td>
                                <td>{{ $booking->service_name }}</td>
                                <td>{{ $booking->medicalNote ? 'Sudah diisi' : 'Belum diisi' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('doctor.medical-notes.create', $booking) }}">
                                        {{ $booking->medicalNote ? 'Edit' : 'Isi sekarang' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">Belum ada data rekam medis.</td>
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
