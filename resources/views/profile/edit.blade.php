@extends('layouts.app')

@section('title', 'Profil | '.config('clinic.name'))
@section('page_kicker', 'Area Pengguna')
@section('page_title', 'Profil Akun')

@section('content')
    @if (session('recovery_codes'))
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="fw-semibold mb-2">Recovery code 2FA</div>
            <div class="row g-2">
                @foreach (session('recovery_codes') as $recoveryCode)
                    <div class="col-sm-6">
                        <code class="d-block bg-white rounded-3 px-3 py-2 text-dark">{{ $recoveryCode }}</code>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Informasi profil</h2>
                    <form method="POST" action="{{ route('profile.update') }}" class="row g-3">
                        @csrf
                        @method('PATCH')
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. telepon</label>
                            <input class="form-control" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Keamanan akun</h2>
                    <form method="POST" action="{{ route('password.update') }}" class="d-grid gap-3 mb-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="form-label">Password saat ini</label>
                            <input class="form-control" type="password" name="current_password" required>
                        </div>
                        <div>
                            <label class="form-label">Password baru</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div>
                            <label class="form-label">Konfirmasi password baru</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>
                        <button class="btn btn-outline-primary rounded-pill" type="submit">Ubah password</button>
                    </form>

                    <hr>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h3 class="h6 fw-bold mb-1">Authenticator app</h3>
                                <div class="small text-secondary">
                                    Status: {{ $user->hasTwoFactorEnabled() ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>

                            @if (! $user->hasTwoFactorEnabled())
                                <a class="btn btn-primary rounded-pill px-3" href="{{ route('profile.two-factor.setup') }}">Aktifkan</a>
                            @endif
                        </div>

                        @if ($user->hasTwoFactorEnabled())
                            <form method="POST" action="{{ route('profile.two-factor.recovery-codes') }}" class="d-grid gap-2 mb-3">
                                @csrf
                                <div>
                                    <label class="form-label">Password saat ini</label>
                                    <input class="form-control" type="password" name="password" required>
                                    @foreach ($errors->recoveryCodes->get('password') as $message)
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @endforeach
                                </div>
                                <button class="btn btn-outline-primary rounded-pill" type="submit">Buat recovery code baru</button>
                            </form>

                            <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="d-grid gap-2">
                                @csrf
                                @method('DELETE')
                                <div>
                                    <label class="form-label">Password saat ini</label>
                                    <input class="form-control" type="password" name="password" required>
                                    @foreach ($errors->disableTwoFactor->get('password') as $message)
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @endforeach
                                </div>
                                <button class="btn btn-outline-danger rounded-pill" type="submit">Nonaktifkan 2FA</button>
                            </form>
                        @endif
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="d-grid gap-3">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label class="form-label">Konfirmasi password untuk hapus akun</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <button class="btn btn-outline-danger rounded-pill" type="submit">Hapus akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
