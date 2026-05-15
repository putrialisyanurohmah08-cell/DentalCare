@extends('layouts.public')

@section('title', 'Layanan | '.config('clinic.name'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <div class="text-uppercase small text-secondary mb-2">Layanan</div>
                <h1 class="display-6 fw-bold mb-2">Perawatan gigi yang tersusun jelas dari konsultasi hingga tindakan lanjutan.</h1>
                <p class="text-secondary mb-0">Setiap layanan dilengkapi estimasi durasi dan biaya agar pasien bisa merencanakan kunjungan dengan nyaman.</p>
            </div>

            <div class="row g-4">
                @foreach ($services as $service)
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="service-icon mb-3">{{ str($service->name)->substr(0, 1) }}</div>
                                <h2 class="h4 fw-bold">{{ $service->name }}</h2>
                                <p class="text-secondary">{{ $service->description ?: 'Tindakan dilakukan oleh dokter berpengalaman dengan prosedur yang jelas dan aman.' }}</p>
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

            <div class="mt-4">
                {{ $services->links() }}
            </div>
        </div>
    </section>
@endsection
