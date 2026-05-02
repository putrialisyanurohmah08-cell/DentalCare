@extends('layouts.app')

@section('title', ($doctor->exists ? 'Edit' : 'Tambah').' Dokter | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', $doctor->exists ? 'Edit Dokter' : 'Tambah Dokter')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ $doctor->exists ? route('admin.doctors.update', $doctor) : route('admin.doctors.store') }}" class="row g-3">
                @csrf
                @if ($doctor->exists)
                    @method('PUT')
                @endif

                <div class="col-md-6">
                    <label class="form-label">Nama dokter</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name', $doctor->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email', $doctor->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. telepon</label>
                    <input class="form-control" type="text" name="phone" value="{{ old('phone', $doctor->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Spesialisasi</label>
                    <input class="form-control" type="text" name="specialization" value="{{ old('specialization', $profile?->specialization) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password {{ $doctor->exists ? '(opsional)' : '' }}</label>
                    <input class="form-control" type="password" name="password" {{ $doctor->exists ? '' : 'required' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi password</label>
                    <input class="form-control" type="password" name="password_confirmation" {{ $doctor->exists ? '' : 'required' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor SIP</label>
                    <input class="form-control" type="text" name="license_number" value="{{ old('license_number', $profile?->license_number) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pengalaman (tahun)</label>
                    <input class="form-control" type="number" name="experience_years" value="{{ old('experience_years', $profile?->experience_years) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Biografi singkat</label>
                    <textarea class="form-control" name="biography" rows="4">{{ old('biography', $profile?->biography) }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('admin.doctors.index') }}">Batal</a>
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan dokter</button>
                </div>
            </form>
        </div>
    </div>
@endsection
