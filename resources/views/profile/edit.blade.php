@extends('layouts.app')

@section('title', 'Profil | '.config('clinic.name'))
@section('page_kicker', 'Area Pengguna')
@section('page_title', 'Profil Akun')

@section('content')
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
