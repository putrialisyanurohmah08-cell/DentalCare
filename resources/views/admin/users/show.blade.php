@extends('layouts.app')

@section('title', 'Detail User | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Detail User')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('admin.users.index') }}">Kembali</a>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary rounded-pill px-4" href="{{ route('admin.users.edit', $user) }}">Edit user</a>
            @unless ($user->is(auth()->user()))
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-warning rounded-pill px-4" type="submit">{{ $user->Status ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini dari daftar aktif?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger rounded-pill px-4" type="submit">Hapus</button>
                </form>
            @endunless
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h2 class="h4 fw-bold mb-1">{{ $user->name }}</h2>
                            <div class="text-secondary">{{ $user->email }}</div>
                        </div>
                        <span class="badge text-bg-{{ $user->Status ? 'success' : 'secondary' }}">
                            {{ $user->Status ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Role</dt>
                        <dd class="col-sm-8 fw-semibold">{{ $user->role->label() }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Telepon</dt>
                        <dd class="col-sm-8">{{ $user->phone ?: '-' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Alamat</dt>
                        <dd class="col-sm-8">{{ $user->address ?: '-' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Email verified</dt>
                        <dd class="col-sm-8">{{ $user->email_verified_at?->translatedFormat('d M Y H:i') ?? 'Belum' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Terdaftar</dt>
                        <dd class="col-sm-8">{{ $user->CreatedDate?->translatedFormat('d M Y H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Update terakhir</dt>
                        <dd class="col-sm-8">{{ $user->LastUpdatedDate?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-secondary small">Reservasi sebagai pasien</div>
                            <div class="h4 fw-bold mb-0">{{ $user->patient_bookings_count }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-secondary small">Reservasi sebagai dokter</div>
                            <div class="h4 fw-bold mb-0">{{ $user->doctor_bookings_count }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3">Riwayat reservasi pasien</h3>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Dokter</th>
                                    <th>Jadwal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patientBookings as $booking)
                                    <tr>
                                        <td class="fw-semibold">{{ $booking->booking_code }}</td>
                                        <td>{{ $booking->doctor->name }}</td>
                                        <td>{{ $booking->scheduleLabel() }}</td>
                                        <td><span class="badge text-bg-{{ $booking->badgeClass() }}">{{ $booking->statusLabel() }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">Belum ada reservasi sebagai pasien.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($user->isDoctor())
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">Reservasi ditangani dokter</h3>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Pasien</th>
                                        <th>Jadwal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($doctorBookings as $booking)
                                        <tr>
                                            <td class="fw-semibold">{{ $booking->booking_code }}</td>
                                            <td>{{ $booking->patient->name }}</td>
                                            <td>{{ $booking->scheduleLabel() }}</td>
                                            <td><span class="badge text-bg-{{ $booking->badgeClass() }}">{{ $booking->statusLabel() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-secondary">Belum ada reservasi sebagai dokter.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
