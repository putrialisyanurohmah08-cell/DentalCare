@extends('layouts.guest')

@section('title', 'Verifikasi 2FA | '.config('clinic.name'))
@section('auth_title', 'Verifikasi 2FA')
@section('auth_subtitle', 'Masukkan kode '.$digits.' digit dari aplikasi autentikator Anda.')

@section('content')
    <form method="POST" action="{{ route('two-factor.verify') }}" class="d-grid gap-3">
        @csrf

        <div>
            <label class="form-label" for="code">Kode autentikator atau recovery code</label>
            <input
                class="form-control text-center fw-semibold fs-4"
                id="code"
                type="text"
                name="code"
                value="{{ old('code') }}"
                required
                autofocus
                inputmode="text"
                autocomplete="one-time-code"
                maxlength="20"
            >
            <div class="form-text">
                Sesi login berlaku sampai {{ $expiresAt->timezone(config('app.timezone'))->format('H:i') }} WIB.
            </div>
        </div>

        <button class="btn btn-primary rounded-pill w-100" type="submit">Verifikasi dan masuk</button>
    </form>

    <div class="d-grid gap-2 mt-3">
        <form method="POST" action="{{ route('two-factor.cancel') }}">
            @csrf
            @method('DELETE')
            <button class="btn btn-link text-secondary text-decoration-none w-100" type="submit">Batalkan login</button>
        </form>
    </div>
@endsection
