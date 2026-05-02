@extends('layouts.app')

@section('title', ($service->exists ? 'Edit' : 'Tambah').' Layanan | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', $service->exists ? 'Edit Layanan' : 'Tambah Layanan')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="row g-3">
                @csrf
                @if ($service->exists)
                    @method('PUT')
                @endif

                <div class="col-md-7">
                    <label class="form-label">Nama layanan</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name', $service->name) }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Durasi (menit)</label>
                    <input class="form-control" type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes ?: 30) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga</label>
                    <input class="form-control" type="number" min="0" step="1000" name="price" value="{{ old('price', $service->price) }}" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('admin.services.index') }}">Batal</a>
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan layanan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
