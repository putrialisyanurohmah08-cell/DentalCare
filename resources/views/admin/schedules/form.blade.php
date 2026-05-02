@extends('layouts.app')

@section('title', ($schedule->exists ? 'Edit' : 'Tambah').' Jadwal | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', $schedule->exists ? 'Edit Jadwal Dokter' : 'Tambah Jadwal Dokter')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ $schedule->exists ? route('admin.schedules.update', $schedule) : route('admin.schedules.store') }}" class="row g-3">
                @csrf
                @if ($schedule->exists)
                    @method('PUT')
                @endif

                <div class="col-md-6">
                    <label class="form-label">Dokter</label>
                    <select class="form-select" name="doctor_id" required>
                        <option value="">Pilih dokter</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(old('doctor_id', $schedule->doctor_id) == $doctor->id)>
                                {{ $doctor->name }} - {{ $doctor->doctorProfile?->specialization }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hari praktik</label>
                    <select class="form-select" name="day_of_week" required>
                        <option value="">Pilih hari</option>
                        @foreach ($dayOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('day_of_week', $schedule->day_of_week) == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mulai</label>
                    <input class="form-control" type="time" name="start_time" value="{{ old('start_time', $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : null) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Selesai</label>
                    <input class="form-control" type="time" name="end_time" value="{{ old('end_time', $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : null) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kuota harian</label>
                    <input class="form-control" type="number" name="quota" value="{{ old('quota', $schedule->quota ?: 10) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Durasi slot</label>
                    <input class="form-control" type="number" name="slot_minutes" value="{{ old('slot_minutes', $schedule->slot_minutes ?: config('clinic.slot_minutes')) }}" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('admin.schedules.index') }}">Batal</a>
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan jadwal</button>
                </div>
            </form>
        </div>
    </div>
@endsection
