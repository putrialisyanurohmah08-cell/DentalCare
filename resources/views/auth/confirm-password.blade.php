@extends('layouts.guest')

@section('title', 'Konfirmasi Password | '.config('clinic.name'))
@section('auth_title', 'Konfirmasi password')
@section('auth_subtitle', 'Masukkan password Anda sekali lagi untuk melanjutkan ke area yang aman.')

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}" class="d-grid gap-3">
        @csrf
        <div>
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary rounded-pill" type="submit">Konfirmasi</button>
    </form>
@endsection
