@extends('layouts.app')

@section('title', 'Laporan | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Laporan Klinik')

@section('content')
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.index') }}">
                <div class="col-md-4">
                    <label class="form-label">Tanggal mulai</label>
                    <input class="form-control" type="date" name="start_date" value="{{ $filters['start_date'] }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal akhir</label>
                    <input class="form-control" type="date" name="end_date" value="{{ $filters['end_date'] }}">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Terapkan filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach ([
            ['label' => 'Pendapatan', 'value' => 'Rp '.number_format($stats['revenue'], 0, ',', '.')],
            ['label' => 'Kunjungan pasien', 'value' => $stats['visits']],
            ['label' => 'Transaksi lunas', 'value' => $stats['paid_transactions']],
            ['label' => 'Kunjungan selesai', 'value' => $stats['completed_visits']],
        ] as $item)
            <div class="col-xl-3 col-sm-6">
                <div class="metric-card h-100">
                    <div class="small text-secondary mb-2">{{ $item['label'] }}</div>
                    <div class="h2 fw-bold mb-0">{{ $item['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Grafik pendapatan bulanan</h2>
                    <canvas id="revenueChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Layanan terpopuler</h2>
                    <canvas id="serviceChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        const monthlyRevenue = @json($monthlyRevenue);
        const serviceBreakdown = @json($serviceBreakdown);

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: monthlyRevenue.map(item => item.month),
                datasets: [{
                    label: 'Pendapatan',
                    data: monthlyRevenue.map(item => Number(item.total)),
                    borderColor: '#0b7285',
                    backgroundColor: 'rgba(11, 114, 133, 0.18)',
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
            }
        });

        new Chart(document.getElementById('serviceChart'), {
            type: 'doughnut',
            data: {
                labels: serviceBreakdown.map(item => item.service_name),
                datasets: [{
                    data: serviceBreakdown.map(item => Number(item.total)),
                    backgroundColor: ['#0b7285', '#14b8a6', '#f59f00', '#ef4444', '#7c3aed'],
                }]
            }
        });
    </script>
@endpush
