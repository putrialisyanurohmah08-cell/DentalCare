@extends('layouts.app')

@section('title', 'Dokter | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Manajemen Dokter')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Daftar dokter</h2>
                    <p class="text-secondary small mb-0">Kelola akun dokter dan profil profesionalnya.</p>
                </div>
                <a class="btn btn-primary rounded-pill px-4" href="{{ route('admin.doctors.create') }}">Tambah dokter</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Aksi</th>
                            <th>Dokter</th>
                            <th>Spesialisasi</th>
                            <th>SIP</th>
                            <th>Jadwal aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctors as $doctor)
                            <tr>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('admin.doctors.edit', $doctor) }}">Edit</a>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $doctor->name }}</div>
                                    <div class="small text-secondary">{{ $doctor->email }}</div>
                                </td>
                                <td>{{ $doctor->doctorProfile?->specialization }}</td>
                                <td>{{ $doctor->doctorProfile?->license_number }}</td>
                                <td>{{ $doctor->doctorSchedules->count() }} hari</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary">Belum ada dokter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $doctors->links() }}
            </div>
        </div>
    </div>
@endsection
