@extends('layouts.public')

@section('title', 'Dokter | '.config('clinic.name'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <div class="text-uppercase small text-secondary mb-2">Dokter</div>
                <h1 class="display-6 fw-bold mb-2">Tim dokter yang siap mendampingi perawatan Anda dengan pendekatan yang hangat dan profesional.</h1>
                <p class="text-secondary mb-0">Pilih dokter berdasarkan spesialisasi dan jam praktik yang tersedia.</p>
            </div>

            <div class="row g-4">
                @foreach ($doctors as $doctor)
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="doctor-avatar mb-3">{{ str($doctor->name)->substr(0, 1) }}</div>
                                <h2 class="h4 fw-bold mb-1">{{ $doctor->name }}</h2>
                                <div class="text-primary-emphasis fw-semibold mb-3">{{ $doctor->doctorProfile?->specialization }}</div>
                                <p class="text-secondary">{{ $doctor->doctorProfile?->biography ?: 'Dokter berpengalaman dengan fokus pada edukasi dan kenyamanan pasien.' }}</p>
                                <div class="small text-secondary d-grid gap-1 mb-4">
                                    <span>SIP: {{ $doctor->doctorProfile?->license_number }}</span>
                                    <span>Pengalaman: {{ $doctor->doctorProfile?->experience_years ?? 0 }} tahun</span>
                                </div>
                                <div class="d-grid gap-2">
                                    @foreach ($doctor->doctorSchedules as $schedule)
                                        <div class="schedule-chip">{{ $schedule->dayLabel() }} • {{ $schedule->formattedTimeRange() }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $doctors->links() }}
            </div>
        </div>
    </section>
@endsection
