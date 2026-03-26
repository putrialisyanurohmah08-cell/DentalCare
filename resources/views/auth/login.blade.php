@extends('layouts.guest')

@section('title', 'Masuk | '.config('clinic.name'))
@section('auth_title', 'Masuk ke akun Anda')
@section('auth_subtitle', 'Akses dashboard pasien, dokter, atau admin sesuai peran Anda.')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
        @csrf

        <div>
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div>
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="current-password">
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label" for="remember_me">Ingat saya</label>
            </div>
            @if (Route::has('password.request'))
                <a class="small text-decoration-none" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
        </div>

        <button class="btn btn-primary rounded-pill w-100" type="submit">Masuk</button>
    </form>

    <div class="text-center text-secondary small mt-4">
        Belum punya akun?
        <a class="text-decoration-none fw-semibold" href="{{ route('register') }}">Daftar sebagai pasien</a>
    </div>
@endsection
