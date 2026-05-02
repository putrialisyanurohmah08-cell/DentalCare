@extends('layouts.app')

@section('title', 'Edit User | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Edit User')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $managedUser->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $managedUser->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor telepon</label>
                    <input class="form-control @error('phone') is-invalid @enderror" type="text" name="phone" value="{{ old('phone', $managedUser->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select class="form-select @error('role') is-invalid @enderror" name="role" @disabled($managedUser->is(auth()->user()))>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $managedUser->role->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @if ($managedUser->is(auth()->user()))
                        <input type="hidden" name="role" value="{{ $managedUser->role->value }}">
                    @endif
                    @error('role')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select @error('Status') is-invalid @enderror" name="Status" @disabled($managedUser->is(auth()->user()))>
                        <option value="1" @selected((string) old('Status', $managedUser->Status) === '1')>Aktif</option>
                        <option value="0" @selected((string) old('Status', $managedUser->Status) === '0')>Nonaktif</option>
                    </select>
                    @if ($managedUser->is(auth()->user()))
                        <input type="hidden" name="Status" value="{{ $managedUser->Status }}">
                    @endif
                    @error('Status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="4">{{ old('address', $managedUser->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('admin.users.show', $managedUser) }}">Batal</a>
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan user</button>
                </div>
            </form>
        </div>
    </div>
@endsection
