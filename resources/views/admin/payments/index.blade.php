@extends('layouts.app')

@section('title', 'Pembayaran | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Monitoring Pembayaran')

@section('content')
    {{-- Stats cards --}}
    <div class="row g-4 mb-4">
        @foreach ([
            ['label' => 'Total transaksi', 'value' => $stats['total'], 'class' => 'text-dark'],
            ['label' => 'Menunggu pembayaran', 'value' => $stats['pending'], 'class' => 'text-warning'],
            ['label' => 'Sudah dibayar', 'value' => $stats['paid'], 'class' => 'text-success'],
            ['label' => 'Pendapatan masuk', 'value' => 'Rp '.number_format($stats['revenue'], 0, ',', '.'), 'class' => 'text-primary'],
        ] as $item)
            <div class="col-xl-3 col-sm-6">
                <div class="metric-card h-100">
                    <div class="small text-secondary mb-2">{{ $item['label'] }}</div>
                    <div class="h2 fw-bold mb-0 {{ $item['class'] }}">{{ $item['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.payments.index') }}">
                <div class="col-md-4">
                    <label class="form-label">Status pembayaran</label>
                    <select class="form-select" name="status">
                        <option value="">Semua</option>
                        @foreach (\App\Enums\PaymentStatus::cases() as $s)
                            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Filter</button>
                    @if (request()->hasAny(['status']))
                        <a class="btn btn-outline-secondary rounded-pill px-3" href="{{ route('admin.payments.index') }}">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Pasien</th>
                            <th>Dokter</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Waktu bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="fw-semibold">{{ $payment->order_id }}</td>
                                <td>{{ $payment->booking->patient->name }}</td>
                                <td>{{ $payment->booking->doctor->name }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>{{ $payment->payment_method ?: '-' }}</td>
                                <td><span class="badge text-bg-{{ $payment->badgeClass() }}">{{ $payment->statusLabel() }}</span></td>
                                <td>
                                    @if ($payment->paid_at)
                                        <div>{{ $payment->paid_at->translatedFormat('d M Y') }}</div>
                                        <div class="small text-secondary">{{ $payment->paid_at->translatedFormat('H:i') }}</div>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-secondary">Belum ada pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection
