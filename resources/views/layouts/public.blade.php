@extends('layouts.base')

@section('body')
    <div class="public-shell">
        <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
            <div class="container py-2">
                <a class="navbar-brand fw-bold text-primary-emphasis" href="{{ route('home') }}">
                    {{ config('clinic.name') }}
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="publicNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">Layanan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}" href="{{ route('doctors.index') }}">Dokter</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('booking.*') ? 'active' : '' }}" href="{{ route('booking.create') }}">Reservasi</a>
                        </li>

                        @auth
                            <li class="nav-item ms-lg-3">
                                <a class="btn btn-primary rounded-pill px-4" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                        @else
                            <li class="nav-item ms-lg-3">
                                <a class="btn btn-outline-primary rounded-pill px-4" href="{{ route('login') }}">Masuk</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-primary rounded-pill px-4" href="{{ route('register') }}">Daftar</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>

        <footer class="border-top bg-white">
            <div class="container py-5">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-5">
                        <h5 class="fw-bold">{{ config('clinic.name') }}</h5>
                        <p class="text-secondary mb-3">{{ config('clinic.tagline') }}</p>
                        <div class="small text-secondary">
                            <div>{{ config('clinic.address') }}</div>
                            <div>{{ config('clinic.phone') }}</div>
                            <div>{{ config('clinic.email') }}</div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="text-uppercase small text-secondary mb-2">Navigasi</div>
                                <div class="d-grid gap-2">
                                    <a class="link-secondary text-decoration-none" href="{{ route('home') }}">Beranda</a>
                                    <a class="link-secondary text-decoration-none" href="{{ route('services.index') }}">Layanan</a>
                                    <a class="link-secondary text-decoration-none" href="{{ route('doctors.index') }}">Dokter</a>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-uppercase small text-secondary mb-2">Jam layanan</div>
                                <div class="small text-secondary d-grid gap-1">
                                    <span>Senin - Jumat: 08.00 - 20.00</span>
                                    <span>Sabtu: 09.00 - 17.00</span>
                                    <span>Minggu: By appointment</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection
