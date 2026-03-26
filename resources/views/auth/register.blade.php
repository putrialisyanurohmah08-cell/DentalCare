@extends('layouts.guest')

@section('title', 'Daftar | '.config('clinic.name'))
@section('auth_title', 'Buat akun pasien')
@section('auth_subtitle', 'Daftar untuk mulai reservasi, memantau pembayaran, dan mengunduh resume medis.')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="row g-3">
        @csrf

        <div class="col-12">
            <label class="form-label">Nama lengkap</label>
            <input class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>
        <div class="col-12">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </div>
        <div class="col-12">
            <label class="form-label">No. telepon</label>
            <input class="form-control" type="text" name="phone" value="{{ old('phone') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="new-password">
        </div>
        <div class="col-md-6">
            <label class="form-label">Konfirmasi password</label>
            <input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>
        <div class="col-12">
            <button class="btn btn-primary rounded-pill w-100" type="submit">Daftar sekarang</button>
        </div>
    </form>

    <div class="text-center text-secondary small mt-4">
        Sudah punya akun?
        <a class="text-decoration-none fw-semibold" href="{{ route('login') }}">Masuk di sini</a>
    </div>
@endsection
