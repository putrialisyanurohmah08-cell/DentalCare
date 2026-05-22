@extends('layouts.public')

@section('title', 'DentalCare Lite | Reservasi Klinik Gigi Modern')

@section('content')
    <section class="hero-section py-5 py-lg-6">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="eyebrow-pill mb-3">Reservasi digital untuk klinik gigi</span>
                    <h1 class="display-5 fw-bold mb-3">Rawat senyum keluarga dengan booking online, pembayaran instan, dan rekam medis yang rapi.</h1>
                    <p class="lead text-secondary mb-4">{{ config('clinic.tagline') }}</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a class="btn btn-primary btn-lg rounded-pill px-4" href="{{ route('home') }}#booking-section">Buat Reservasi</a>
                        <a class="btn btn-outline-dark btn-lg rounded-pill px-4" href="{{ route('doctors.index') }}">Lihat Dokter</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="rounded-5 overflow-hidden shadow-lg mb-4" style="height: 350px;">
                        <img src="https://images.unsplash.com/photo-1606811841689-23dfddce3e95?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Klinik Gigi" class="w-100 h-100" style="object-fit: cover;">
                    </div>
                    <div class="hero-card shadow-sm border rounded-5 p-4 bg-white">
                        <div class="row g-3 text-center">
                            @foreach ([
                                ['label' => 'Dokter', 'value' => $stats['doctors']],
                                ['label' => 'Layanan', 'value' => $stats['services']],
                                ['label' => 'Reservasi', 'value' => $stats['bookings']],
                                ['label' => 'Transaksi', 'value' => $stats['payments']],
                            ] as $item)
                                <div class="col-3">
                                    <div class="stat-card h-100">
                                        <div class="h4 fw-bold mb-1 text-primary">{{ $item['value'] }}</div>
                                        <div class="text-secondary small" style="font-size: 0.75rem;">{{ $item['label'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('public.partials.booking-section')

    <section class="py-5 bg-white">
        <div class="container">
            <div class="section-heading d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <div class="text-uppercase small text-secondary mb-2">Layanan unggulan</div>
                    <h2 class="h1 fw-bold mb-0">Perawatan yang jelas, nyaman, dan transparan.</h2>
                </div>
                <a class="btn btn-outline-primary rounded-pill px-4" href="{{ route('services.index') }}">Lihat semua</a>
            </div>
            <div class="row g-4">
                @php
                $getServiceImage = function($serviceName) {
                    $lower = strtolower($serviceName);
                    if (str_contains($lower, 'atas bawah')) {
                        return 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    } elseif (str_contains($lower, 'bawah')) {
                        return 'https://images.unsplash.com/photo-1609840114035-3c981b782dfe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    } elseif (str_contains($lower, 'atas') || str_contains($lower, 'behel')) {
                        return 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    } elseif (str_contains($lower, 'scaling')) {
                        return 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    }
                    return 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                };
                @endphp
                @foreach ($featuredServices as $service)
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                            <img src="{{ $getServiceImage($service->name) }}" class="card-img-top" alt="{{ $service->name }}" style="height: 160px; object-fit: cover;">
                            <div class="card-body p-4">
                                <h3 class="h5 fw-bold">{{ $service->name }}</h3>
                                <p class="text-secondary small">{{ $service->description ?: 'Perawatan profesional dengan standar klinik modern.' }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        @if ($service->hasDiscount())
                                            <span class="small text-secondary text-decoration-line-through d-block">Rp {{ number_format($service->originalPrice(), 0, ',', '.') }}</span>
                                            <span class="fw-semibold">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                            <span class="badge rounded-pill text-bg-warning ms-1">Diskon {{ $service->discountPercent() }}%</span>
                                        @else
                                            <span class="fw-semibold">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                        @endif
                                    </span>
                                    <span class="badge rounded-pill text-bg-light">{{ $service->duration_minutes }} menit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="section-heading d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <div class="text-uppercase small text-secondary mb-2">Tim dokter</div>
                    <h2 class="h1 fw-bold mb-0">Temukan dokter yang sesuai dengan kebutuhan Anda.</h2>
                </div>
                <a class="btn btn-outline-primary rounded-pill px-4" href="{{ route('doctors.index') }}">Jelajahi dokter</a>
            </div>
            <div class="row g-4">
                @php
                $doctorImages = [
                    'https://images.unsplash.com/photo-1537368910025-700350fe46c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80',
                    'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80',
                    'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80',
                    'https://images.unsplash.com/photo-1594824436998-d463d1222453?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80'
                ];
                @endphp
                @foreach ($featuredDoctors as $doctor)
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4">
                            <div class="card-body p-4 text-center">
                                <img src="{{ $doctorImages[$loop->index % 4] }}" alt="{{ $doctor->name }}" class="rounded-circle mb-3 shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                                <h3 class="h5 fw-bold mb-1">{{ $doctor->name }}</h3>
                                <div class="text-primary-emphasis fw-semibold small mb-3">{{ $doctor->doctorProfile?->specialization }}</div>
                                <p class="text-secondary small mb-3">
                                    {{ $doctor->doctorProfile?->biography ?: 'Dokter berpengalaman dengan pendekatan perawatan yang ramah pasien.' }}
                                </p>
                                <div class="small text-secondary d-grid gap-1">
                                    @foreach ($doctor->doctorSchedules->take(2) as $schedule)
                                        <span>{{ $schedule->dayLabel() }} • {{ $schedule->formattedTimeRange() }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="cta-banner rounded-5 p-4 p-lg-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 position-relative overflow-hidden" style="background: linear-gradient(rgba(13, 110, 253, 0.85), rgba(13, 110, 253, 0.85)), url('https://images.unsplash.com/photo-1606811841689-23dfddce3e95?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;">
                <div class="position-relative z-1">
                    <div class="text-uppercase small text-white-50 mb-2">Siap periksa gigi?</div>
                    <h2 class="display-6 fw-bold text-white mb-2">Booking sekarang dan amankan antrean Anda hari ini.</h2>
                    <p class="text-white-50 mb-0">Pilih dokter, tentukan jadwal, lalu selesaikan pembayaran secara otomatis lewat Midtrans.</p>
                </div>
                <a class="btn btn-light btn-lg rounded-pill px-4 position-relative z-1" href="{{ route('home') }}#booking-section">Mulai reservasi</a>
            </div>
        </div>
    </section>
@endsection
