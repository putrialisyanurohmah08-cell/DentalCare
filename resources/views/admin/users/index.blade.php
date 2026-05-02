@extends('layouts.app')

@section('title', 'Data User | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Data User')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="text-secondary small">Total user</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="text-secondary small">Pasien</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['patients'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="text-secondary small">Dokter</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['doctors'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="text-secondary small">Aktif</div>
                    <div class="h4 fw-bold mb-0">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Daftar user terdaftar</h2>
                    <p class="text-secondary small mb-0">Cari, filter, lihat detail, edit, nonaktifkan, atau hapus data pengguna.</p>
                </div>
            </div>

            <form class="row g-3 align-items-end mb-4" method="GET" action="{{ route('admin.users.index') }}">
                <div class="col-md-5">
                    <label class="form-label">Pencarian</label>
                    <input class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, email, telepon, atau alamat">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="">Semua role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(($filters['role'] ?? '') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua</option>
                        <option value="1" @selected(($filters['status'] ?? '') === '1')>Aktif</option>
                        <option value="0" @selected(($filters['status'] ?? '') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary rounded-pill px-4 flex-fill" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary rounded-pill px-3" href="{{ route('admin.users.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th>Reservasi</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $managedUser)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $managedUser->name }}</div>
                                    <div class="small text-secondary">{{ $managedUser->email }}</div>
                                </td>
                                <td>{{ $managedUser->role->label() }}</td>
                                <td>{{ $managedUser->phone ?: '-' }}</td>
                                <td class="text-secondary small">{{ $managedUser->address ?: '-' }}</td>
                                <td>
                                    <div>{{ $managedUser->patient_bookings_count }} sebagai pasien</div>
                                    @if ($managedUser->doctor_bookings_count)
                                        <div class="small text-secondary">{{ $managedUser->doctor_bookings_count }} sebagai dokter</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $managedUser->Status ? 'success' : 'secondary' }}">
                                        {{ $managedUser->Status ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>{{ $managedUser->CreatedDate?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('admin.users.show', $managedUser) }}">Detail</a>
                                        <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('admin.users.edit', $managedUser) }}">Edit</a>
                                        @unless ($managedUser->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $managedUser) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning rounded-pill" type="submit">{{ $managedUser->Status ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Hapus user ini dari daftar aktif?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Hapus</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-secondary">Belum ada user yang cocok dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
