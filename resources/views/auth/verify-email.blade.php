@extends('layouts.guest')

@section('title', 'Verifikasi Email | '.config('clinic.name'))
@section('auth_title', 'Verifikasi email Anda')
@section('auth_subtitle', 'Klik tautan verifikasi yang dikirim ke email untuk mengaktifkan akun sepenuhnya.')

@section('content')
    <div class="d-grid gap-3">
        <p class="text-secondary mb-0">Jika email belum masuk, Anda bisa mengirim ulang tautan verifikasi di bawah ini.</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn btn-primary rounded-pill w-100" type="submit">Kirim ulang email verifikasi</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-secondary rounded-pill w-100" type="submit">Keluar</button>
        </form>
    </div>
@endsection
