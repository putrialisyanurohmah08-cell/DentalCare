@extends('layouts.guest')

@section('title', 'Lupa Password | '.config('clinic.name'))
@section('auth_title', 'Reset password')
@section('auth_subtitle', 'Masukkan email akun Anda dan kami akan mengirimkan tautan reset password.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="d-grid gap-3">
        @csrf
        <div>
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <button class="btn btn-primary rounded-pill" type="submit">Kirim tautan reset</button>
    </form>
@endsection
