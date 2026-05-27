@extends('layouts.app')

@section('title', 'Laporan | '.config('clinic.name'))
@section('page_kicker', 'Area Admin')
@section('page_title', 'Laporan Klinik')

@section('content')
    <style>
        /* Styling tab filter */
        .btn-filter-type {
            transition: all 0.25s ease;
            font-size: 0.9rem;
        }
        
        /* Styling metric cards */
        .metric-card-custom {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }
        .metric-card-custom:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(11, 114, 133, 0.08) !important;
        }
        .metric-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Print styles */
        @media print {
            .dashboard-sidebar,
            .dashboard-topbar,
            .print-none,
            form,
            .btn,
            header {
                display: none !important;
            }
            
            body {
                background-color: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .dashboard-shell {
                display: block !important;
            }
            .dashboard-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            main {
                padding: 0 !important;
            }
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                background-color: #fff !important;
                page-break-inside: avoid;
                margin-bottom: 1.5rem !important;
            }
            .metric-card-custom {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                background-color: #fff !important;
                transform: none !important;
            }
            
            .print-only {
                display: block !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            canvas {
                max-width: 100% !important;
                height: auto !important;
            }
            .legend-scroll-container {
                max-height: none !important;
                overflow-y: visible !important;
            }
        }
    </style>

    <!-- Container Laporan untuk Ekspor PDF -->
    <div id="reportContainer" class="bg-white p-3 rounded-4">
        <!-- Kop Laporan khusus Cetak/PDF -->
        <div class="print-only d-none mb-4 border-bottom pb-3">
        <div class="row align-items-center">
            <div class="col-8">
                <h2 class="h3 fw-bold text-primary mb-1">{{ config('clinic.name') }}</h2>
                <p class="text-secondary mb-0 small">Laporan Keuangan & Kunjungan Klinik</p>
            </div>
            <div class="col-4 text-end">
                <div class="fw-semibold text-dark h5 mb-1">Laporan Eksekutif</div>
                <div class="small text-secondary">
                    Periode: 
                    @if($filters['filter_type'] === 'monthly')
                        {{ Carbon\Carbon::parse($filters['month'] . '-01')->translatedFormat('F Y') }}
                    @else
                        {{ Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d M Y') }} s.d. {{ Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d M Y') }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pilihan Mode Filter & Aksi Cetak -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-3 print-none">
        <div class="btn-group p-1 bg-white border rounded-pill" role="group">
            <button type="button" class="btn rounded-pill px-4 btn-filter-type {{ $filters['filter_type'] === 'daily' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary' }}" data-type="daily">
                Harian (Rentang Tanggal)
            </button>
            <button type="button" class="btn rounded-pill px-4 btn-filter-type {{ $filters['filter_type'] === 'monthly' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary' }}" data-type="monthly">
                Bulanan (Bulan & Tahun)
            </button>
        </div>
        
        <div>
            <button type="button" onclick="downloadReportPDF()" class="btn btn-outline-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                </svg>
                Unduh / Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Form Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 print-none bg-white">
        <div class="card-body p-4">
            <form id="filterForm" method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="filter_type" id="filterTypeInput" value="{{ $filters['filter_type'] }}">
                
                <!-- Daily Inputs -->
                <div class="col-md-4 filter-group-daily {{ $filters['filter_type'] === 'monthly' ? 'd-none' : '' }}">
                    <label class="form-label fw-semibold text-secondary small">Tanggal Mulai</label>
                    <input class="form-control rounded-3 border-secondary-subtle" type="date" name="start_date" value="{{ $filters['start_date'] }}">
                </div>
                <div class="col-md-4 filter-group-daily {{ $filters['filter_type'] === 'monthly' ? 'd-none' : '' }}">
                    <label class="form-label fw-semibold text-secondary small">Tanggal Akhir</label>
                    <input class="form-control rounded-3 border-secondary-subtle" type="date" name="end_date" value="{{ $filters['end_date'] }}">
                </div>

                <!-- Monthly Input -->
                <div class="col-md-8 filter-group-monthly {{ $filters['filter_type'] === 'daily' ? 'd-none' : '' }}">
                    <label class="form-label fw-semibold text-secondary small">Pilih Bulan & Tahun</label>
                    <input class="form-control rounded-3 border-secondary-subtle" type="month" name="month" value="{{ $filters['month'] }}">
                </div>

                <div class="col-md-4">
                    <button class="btn btn-primary w-100 rounded-pill px-4 py-2 fw-medium shadow-sm" type="submit">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Metrik Statistik Utama -->
    <div class="row g-4 mb-4">
        <!-- Pendapatan -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 metric-card-custom h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="metric-icon-wrapper bg-info-subtle text-info me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-wallet2" viewBox="0 0 16 16">
                            <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.463-1.498L12.136.326zM13.5 4H.5v9.5a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5V4zm-1.5-1H1.537l9.467-1.42A.5.5 0 0 1 12 2.08V3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-secondary mb-1">Pendapatan</div>
                        <div class="h3 fw-bold mb-0 text-dark">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kunjungan Pasien -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 metric-card-custom h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="metric-icon-wrapper bg-primary-subtle text-primary me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.047 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-secondary mb-1">Kunjungan Pasien</div>
                        <div class="h3 fw-bold mb-0 text-dark">{{ $stats['visits'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Lunas -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 metric-card-custom h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="metric-icon-wrapper bg-success-subtle text-success me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm3 0a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-secondary mb-1">Transaksi Lunas</div>
                        <div class="h3 fw-bold mb-0 text-dark">{{ $stats['paid_transactions'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kunjungan Selesai -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 metric-card-custom h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="metric-icon-wrapper bg-warning-subtle text-warning me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-patch-check" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                            <path d="m10.273 2.513-.921-.344.715-.698.814-.14-.186.81-.622.372zm-1.38-.347a.5.5 0 0 0-.273-.362l-1-.375a.5.5 0 0 0-.547.11L6.354 2.146l-.646.646a.5.5 0 0 0-.11.547l.375 1a.5.5 0 0 0 .362.273l1 .375a.5.5 0 0 0 .547-.11l.715-.715.646-.646a.5.5 0 0 0 .11-.547zM3.425 9.03l-.344.921-.698-.715-.14-.814.81.186.372.622zm.347 1.38a.5.5 0 0 0 .362.273l1 .375a.5.5 0 0 0 .11-.547L5.146 9.646l.646-.646a.5.5 0 0 0-.547-.11l-1 .375a.5.5 0 0 0-.273.362z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-secondary mb-1">Kunjungan Selesai</div>
                        <div class="h3 fw-bold mb-0 text-dark">{{ $stats['completed_visits'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Infografis Laporan -->
    <div class="row g-4 mb-4">
        <!-- Grafik Pendapatan -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-dark">Grafik Pendapatan ({{ $chartType === 'daily' ? 'Harian' : 'Bulanan' }})</h2>
                    <div style="position: relative; height: 320px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Layanan Terpopuler -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-dark">Layanan Terpopuler</h2>
                    @php
                        $totalServices = $serviceBreakdown->sum('total');
                        $colors = ['#0b7285', '#14b8a6', '#f59f00', '#ef4444', '#7c3aed'];
                    @endphp
                    
                    <div class="row align-items-center h-100" style="min-height: 280px;">
                        <div class="col-sm-5 col-12 mb-3 mb-sm-0 d-flex justify-content-center">
                            <div style="position: relative; width: 100%; max-width: 170px; height: 170px;">
                                <canvas id="serviceChart"></canvas>
                            </div>
                        </div>
                        <div class="col-sm-7 col-12">
                            <div class="legend-scroll-container pe-sm-1" style="max-height: 250px; overflow-y: auto;">
                                @if($totalServices > 0)
                                    @foreach($serviceBreakdown as $index => $item)
                                        @php
                                            $color = $colors[$index % count($colors)];
                                            $percent = round(($item->total / $totalServices) * 100);
                                        @endphp
                                        <div class="d-flex align-items-start justify-content-between mb-2 pb-2 border-bottom border-light-subtle">
                                            <div class="d-flex align-items-start me-2" style="min-width: 0;">
                                                <span class="badge-dot me-2 mt-1.5 flex-shrink-0" style="background-color: {{ $color }}; width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                                                <span class="text-dark small fw-semibold text-wrap lh-sm" style="word-break: break-word; font-size: 0.85rem;">
                                                    {{ $item->service_name }}
                                                </span>
                                            </div>
                                            <div class="text-end flex-shrink-0 ps-1" style="font-size: 0.85rem;">
                                                <span class="fw-bold text-dark me-1">{{ $item->total }}x</span>
                                                <span class="text-muted">({{ $percent }}%)</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted py-4 small">
                                        Tidak ada data layanan
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadReportPDF() {
            const reportArea = document.getElementById('reportContainer');
            const kop = document.querySelector('.print-only');
            
            // Tampilkan Kop khusus cetak sebelum menangkap canvas
            if (kop) {
                kop.classList.remove('d-none');
            }
            
            // Konfigurasi html2pdf
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     'laporan-klinik-' + (chartType === 'monthly' ? 'bulanan' : 'harian') + '-' + new Date().toISOString().slice(0, 10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true,
                    logging: false,
                    letterRendering: true
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };
            
            // Eksekusi pembuatan PDF
            html2pdf().set(opt).from(reportArea).save().then(() => {
                // Sembunyikan kembali Kop setelah cetak selesai
                if (kop) {
                    kop.classList.add('d-none');
                }
            });
        }
        // Logika Toggle Tab Filter Harian / Bulanan
        document.querySelectorAll('.btn-filter-type').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                document.getElementById('filterTypeInput').value = type;
                
                // Ubah style tombol aktif
                document.querySelectorAll('.btn-filter-type').forEach(b => {
                    b.classList.remove('btn-primary', 'shadow-sm', 'text-white');
                    b.classList.add('btn-light', 'text-secondary');
                });
                this.classList.add('btn-primary', 'shadow-sm', 'text-white');
                this.classList.remove('btn-light', 'text-secondary');
                
                // Tampilkan/sembunyikan input form yang bersangkutan
                if (type === 'daily') {
                    document.querySelectorAll('.filter-group-daily').forEach(el => el.classList.remove('d-none'));
                    document.querySelectorAll('.filter-group-monthly').forEach(el => el.classList.add('d-none'));
                } else {
                    document.querySelectorAll('.filter-group-daily').forEach(el => el.classList.add('d-none'));
                    document.querySelectorAll('.filter-group-monthly').forEach(el => el.classList.remove('d-none'));
                }
            });
        });

        // Setup Data Grafik dari Backend
        const chartData = @json($chartData);
        const serviceBreakdown = @json($serviceBreakdown);
        const chartType = @json($chartType);

        // Grafik Pendapatan (Line Chart)
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        const revGradient = revCtx.createLinearGradient(0, 0, 0, 300);
        revGradient.addColorStop(0, 'rgba(11, 114, 133, 0.3)');
        revGradient.addColorStop(1, 'rgba(11, 114, 133, 0.0)');

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: chartData.map(item => item.label),
                datasets: [{
                    label: 'Pendapatan',
                    data: chartData.map(item => Number(item.total)),
                    borderColor: '#0b7285',
                    backgroundColor: revGradient,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#0b7285',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: chartData.length > 31 ? 2 : 4,
                    pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: '#f1f3f5',
                        },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });

        // Grafik Layanan Terpopuler (Doughnut Chart)
        new Chart(document.getElementById('serviceChart'), {
            type: 'doughnut',
            data: {
                labels: serviceBreakdown.map(item => item.service_name),
                datasets: [{
                    data: serviceBreakdown.map(item => Number(item.total)),
                    backgroundColor: ['#0b7285', '#14b8a6', '#f59f00', '#ef4444', '#7c3aed'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = serviceBreakdown.reduce((sum, item) => sum + Number(item.total), 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return ` ${label}: ${value}x (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '72%'
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const { ctx, chartArea: { top, bottom, left, right, width, height } } = chart;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    
                    const total = serviceBreakdown.reduce((sum, item) => sum + Number(item.total), 0);
                    
                    // Render total count number
                    ctx.font = 'bold 16px Inter, system-ui, -apple-system, sans-serif';
                    ctx.fillStyle = '#212529';
                    ctx.fillText(total + 'x', left + width / 2, top + height / 2 - 6);
                    
                    // Render "Total" label text
                    ctx.font = '600 9px Inter, system-ui, -apple-system, sans-serif';
                    ctx.fillStyle = '#868e96';
                    ctx.fillText('TOTAL BOOKING', left + width / 2, top + height / 2 + 12);
                    ctx.restore();
                }
            }]
        });
    </script>
@endpush

