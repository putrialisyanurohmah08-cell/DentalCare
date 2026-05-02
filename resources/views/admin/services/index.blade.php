@extends('layouts.app')

@section('title', 'Layanan | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Manajemen Layanan')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Daftar layanan</h2>
                    <p class="text-secondary small mb-0">Kelola katalog perawatan dan harga layanan klinik.</p>
                </div>
                <a class="btn btn-primary rounded-pill px-4" href="{{ route('admin.services.create') }}">Tambah layanan</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Durasi</th>
                            <th>Harga</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $service->name }}</div>
                                    <div class="small text-secondary">{{ $service->description }}</div>
                                </td>
                                <td>{{ $service->duration_minutes }} menit</td>
                                <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">Belum ada layanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $services->links() }}
            </div>
        </div>
    </div>
@endsection
