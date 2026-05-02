@extends('layouts.guest')

@section('title', 'Reset Password | '.config('clinic.name'))
@section('auth_title', 'Atur password baru')
@section('auth_subtitle', 'Gunakan password yang kuat agar akun Anda tetap aman.')

@section('content')
    <form method="POST" action="{{ route('password.store') }}" class="d-grid gap-3">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        </div>
        <div>
            <label class="form-label">Password baru</label>
            <input class="form-control" type="password" name="password" required autocomplete="new-password">
        </div>
        <div>
            <label class="form-label">Konfirmasi password</label>
            <input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button class="btn btn-primary rounded-pill" type="submit">Simpan password baru</button>
    </form>
@endsection
