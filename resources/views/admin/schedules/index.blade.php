@extends('layouts.app')

@section('title', 'Jadwal Dokter | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Manajemen Jadwal Dokter')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Jadwal praktik</h2>
                    <p class="text-secondary small mb-0">Atur hari praktik, jam layanan, kuota harian, dan panjang slot kunjungan.</p>
                </div>
                <a class="btn btn-primary rounded-pill px-4" href="{{ route('admin.schedules.create') }}">Tambah jadwal</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Dokter</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Kuota</th>
                            <th>Slot</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->doctor->name }}</td>
                                <td>{{ $schedule->dayLabel() }}</td>
                                <td>{{ $schedule->formattedTimeRange() }}</td>
                                <td>{{ $schedule->quota }} pasien</td>
                                <td>{{ $schedule->slot_minutes }} menit</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('admin.schedules.edit', $schedule) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">Belum ada jadwal dokter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
@endsection
