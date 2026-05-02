@extends('layouts.base')

@section('body')
    <div class="auth-shell min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-8">
                    <div class="text-center mb-4">
                        <a class="text-decoration-none" href="{{ route('home') }}">
                            <div class="brand-pill mx-auto mb-3">{{ config('clinic.name') }}</div>
                        </a>
                        <h1 class="h3 fw-bold mb-2">@yield('auth_title', 'Selamat datang kembali')</h1>
                        <p class="text-secondary mb-0">@yield('auth_subtitle', config('clinic.tagline'))</p>
                    </div>

                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            @include('layouts.partials.flash')
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
