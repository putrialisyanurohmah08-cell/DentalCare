@extends('layouts.app')

@section('title', 'Isi Rekam Medis | '.config('clinic.name'))
@section('page_kicker', 'Area Dokter')
@section('page_title', 'Isi Rekam Medis')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Ringkasan kunjungan</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-secondary">Kode</dt>
                        <dd class="col-7">{{ $booking->booking_code }}</dd>
                        <dt class="col-5 text-secondary">Pasien</dt>
                        <dd class="col-7">{{ $booking->patient->name }}</dd>
                        <dt class="col-5 text-secondary">Layanan</dt>
                        <dd class="col-7">{{ $booking->service_name }}</dd>
                        <dt class="col-5 text-secondary">Jadwal</dt>
                        <dd class="col-7">{{ $booking->scheduleLabel() }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('doctor.medical-notes.store', $booking) }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label class="form-label">Diagnosis</label>
                            <textarea class="form-control" name="diagnosis" rows="4" required>{{ old('diagnosis', $medicalNote?->diagnosis) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Tindakan</label>
                            <textarea class="form-control" name="treatment" rows="4" required>{{ old('treatment', $medicalNote?->treatment) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Resep</label>
                            <textarea class="form-control" name="prescription" rows="3">{{ old('prescription', $medicalNote?->prescription) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Catatan tambahan</label>
                            <textarea class="form-control" name="notes" rows="3">{{ old('notes', $medicalNote?->notes) }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('doctor.medical-notes.index') }}">Batal</a>
                            <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan resume medis</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
