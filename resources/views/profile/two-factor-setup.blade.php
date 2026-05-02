@extends('layouts.app')

@section('title', 'Aktivasi 2FA | '.config('clinic.name'))
@section('page_kicker', 'Keamanan Akun')
@section('page_title', 'Aktivasi 2FA')

@section('content')
    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5 text-center">
                            <div class="d-inline-block border rounded-4 p-3 bg-white">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h2 class="h5 fw-bold mb-3">Pindai QR code</h2>
                            <p class="text-secondary mb-3">Gunakan Google Authenticator, Microsoft Authenticator, Authy, atau aplikasi TOTP lain.</p>

                            <div class="mb-4">
                                <label class="form-label">Secret key</label>
                                <input class="form-control font-monospace" type="text" value="{{ implode(' ', str_split($secret, 4)) }}" readonly>
                            </div>

                            <form method="POST" action="{{ route('profile.two-factor.enable') }}" class="d-grid gap-3">
                                @csrf
                                <div>
                                    <label class="form-label" for="code">Kode {{ $digits }} digit</label>
                                    <input
                                        class="form-control text-center fw-semibold fs-4"
                                        id="code"
                                        type="text"
                                        name="code"
                                        value="{{ old('code') }}"
                                        required
                                        autofocus
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        maxlength="{{ $digits }}"
                                    >
                                    @error('code')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-primary rounded-pill px-4" type="submit">Aktifkan 2FA</button>
                                    <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('profile.edit') }}">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
