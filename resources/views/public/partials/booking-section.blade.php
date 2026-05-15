<section class="py-5 bg-white border-top" id="booking-section">
    <div class="container">
        <div class="section-heading d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <div class="text-uppercase small text-secondary mb-2">Reservasi online</div>
                <h2 class="h1 fw-bold mb-0">Booking tetap di halaman utama, tanpa pindah ke tampilan lain.</h2>
            </div>
            <div class="text-secondary">
                Pilih dokter, layanan, dan tanggal, lalu lanjutkan reservasi di section ini.
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="text-uppercase small text-secondary mb-2">Langkah 1</div>
                        <h3 class="h4 fw-bold mb-3">Pilih dokter, layanan, dan tanggal kunjungan</h3>
                        <p class="text-secondary">Sistem akan menampilkan slot yang tersedia berdasarkan jadwal praktik, durasi layanan, dan kuota harian dokter.</p>

                        <form method="GET" action="{{ route('home') }}#booking-section" class="d-grid gap-3" id="booking-filter-form">
                            <div>
                                <label class="form-label">Dokter</label>
                                <select class="form-select" name="doctor_id" id="booking-doctor-select" required>
                                    <option value="">Pilih dokter</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected(request('doctor_id') == $doctor->id)>
                                            {{ $doctor->name }} - {{ $doctor->doctorProfile?->specialization }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Layanan</label>
                                <select class="form-select" name="service_id" id="booking-service-select" required>
                                    <option value="">Pilih layanan</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>
                                            {{ $service->name }} - {{ $service->duration_minutes }} menit - Rp {{ number_format($service->price, 0, ',', '.') }}@if ($service->hasDiscount()) (Diskon {{ $service->discountPercent() }}%, dari Rp {{ number_format($service->originalPrice(), 0, ',', '.') }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Tanggal kunjungan</label>
                                <input class="form-control" type="date" name="booking_date" id="booking-date-input" min="{{ now()->toDateString() }}" value="{{ $selectedDate }}" required>
                            </div>

                            <button class="btn btn-primary rounded-pill" type="submit" name="check_slots" value="1" id="check-slots-button">Cek slot tersedia</button>
                        </form>

                        <div class="selected-card mt-4 {{ $selectedDoctor ? '' : 'd-none' }}" id="doctor-schedule-card">
                            @if ($selectedDoctor)
                                <div class="fw-semibold mb-1">{{ $selectedDoctor->name }}</div>
                                <div class="small text-secondary mb-3">{{ $selectedDoctor->doctorProfile?->specialization }}</div>
                                @if ($selectedService)
                                    <div class="small text-secondary mb-3">
                                        Layanan dipilih: <span class="fw-semibold text-dark">{{ $selectedService->name }}</span>
                                        • {{ $selectedService->duration_minutes }} menit
                                    </div>
                                @endif
                                <div class="small text-secondary d-grid gap-1">
                                    @foreach ($selectedDoctor->doctorSchedules as $schedule)
                                        <span>{{ $schedule->dayLabel() }} • {{ $schedule->formattedTimeRange() }} • Kuota {{ $schedule->quota }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="text-uppercase small text-secondary mb-2">Langkah 2</div>
                        <h3 class="h4 fw-bold mb-3">Pilih slot waktu dan simpan reservasi</h3>

                        @guest
                            <div class="alert alert-warning border-0 mb-0">
                                Anda perlu masuk sebagai pasien untuk menyelesaikan reservasi.
                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                    <a class="btn btn-dark rounded-pill px-4" href="{{ route('login', ['redirect' => url()->full().'#booking-section']) }}">Masuk</a>
                                    <a class="btn btn-outline-dark rounded-pill px-4" href="{{ route('register', ['redirect' => url()->full().'#booking-section']) }}">Daftar</a>
                                </div>
                            </div>
                        @elseif (! auth()->user()->isPatient())
                            <div class="alert alert-light border mb-0">
                                Reservasi online hanya tersedia untuk akun pasien. Gunakan akun pasien untuk melanjutkan booking dari halaman utama ini.
                            </div>
                        @else
                            <form method="POST" action="{{ route('booking.store') }}" class="d-grid gap-3">
                                @csrf
                                <input type="hidden" name="doctor_id" id="booking-store-doctor-id" value="{{ request('doctor_id') }}">
                                <input type="hidden" name="service_id" id="booking-store-service-id" value="{{ request('service_id') }}">
                                <input type="hidden" name="booking_date" id="booking-store-date" value="{{ $selectedDate }}">

                                <div class="alert alert-light border mb-0 {{ $selectedService ? '' : 'd-none' }}" id="selected-service-summary">
                                    @if ($selectedService)
                                        <div class="fw-semibold">{{ $selectedService->name }}</div>
                                        <div class="small text-secondary">
                                            Durasi {{ $selectedService->duration_minutes }} menit
                                            @if ($selectedService->hasDiscount())
                                                • Harga normal <span class="text-decoration-line-through">Rp {{ number_format($selectedService->originalPrice(), 0, ',', '.') }}</span>
                                                • Diskon {{ $selectedService->discountPercent() }}%
                                                • Harga setelah diskon Rp {{ number_format($selectedService->price, 0, ',', '.') }}
                                            @else
                                                • Rp {{ number_format($selectedService->price, 0, ',', '.') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="form-label">Slot waktu</label>
                                    <div id="slot-panel">
                                        @if ($slotSearchRequested && $selectedDoctor && $selectedService && $selectedDate)
                                        <div class="slot-grid">
                                            @forelse ($availableSlots as $slot)
                                                <label class="slot-option">
                                                    <input type="radio" name="booking_time" value="{{ $slot }}" @checked(old('booking_time') === $slot) required>
                                                    <span>{{ $slot }}</span>
                                                </label>
                                            @empty
                                                <div class="alert alert-light border">Belum ada slot tersedia pada tanggal ini. Coba dokter atau tanggal lain.</div>
                                            @endforelse
                                        </div>
                                    @elseif ($selectedDoctor && $selectedService && $selectedDate)
                                        <div class="alert alert-light border">Klik tombol Cek slot tersedia untuk melihat pilihan jam kunjungan.</div>
                                    @else
                                        <div class="alert alert-light border">Pilih dokter, layanan, dan tanggal terlebih dahulu untuk melihat slot tersedia.</div>
                                    @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Catatan untuk dokter</label>
                                    <textarea class="form-control" name="notes" rows="4" placeholder="Contoh: gigi sensitif, ingin konsultasi scaling, dll.">{{ old('notes') }}</textarea>
                                </div>

                                <button class="btn btn-primary rounded-pill" type="submit" id="booking-submit-button" @disabled(! $selectedDoctor || ! $selectedService || empty($availableSlots))>
                                    Simpan reservasi dan lanjutkan pembayaran
                                </button>
                            </form>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@php
    $bookingDoctorData = $doctors->map(fn ($doctor) => [
        'id' => $doctor->id,
        'name' => $doctor->name,
        'specialization' => $doctor->doctorProfile?->specialization,
        'schedules' => $doctor->doctorSchedules->map(fn ($schedule) => [
            'label' => $schedule->dayLabel(),
            'time' => $schedule->formattedTimeRange(),
            'quota' => $schedule->quota,
        ])->values(),
    ])->values();
    $bookingServiceData = $services->map(fn ($service) => [
        'id' => $service->id,
        'name' => $service->name,
        'duration' => $service->duration_minutes,
        'price' => (float) $service->price,
        'discountPercent' => $service->discountPercent(),
        'originalPrice' => $service->originalPrice(),
    ])->values();
@endphp

<script>
    (() => {
        const doctors = @json($bookingDoctorData);
        const services = @json($bookingServiceData);
        const initialSlots = @json($availableSlots);
        const slotSearchRequested = @json($slotSearchRequested);
        const slotsUrl = @json(route('booking.slots'));

        const form = document.getElementById('booking-filter-form');
        const doctorSelect = document.getElementById('booking-doctor-select');
        const serviceSelect = document.getElementById('booking-service-select');
        const dateInput = document.getElementById('booking-date-input');
        const checkButton = document.getElementById('check-slots-button');
        const doctorCard = document.getElementById('doctor-schedule-card');
        const serviceSummary = document.getElementById('selected-service-summary');
        const slotPanel = document.getElementById('slot-panel');
        const submitButton = document.getElementById('booking-submit-button');
        const storeDoctorInput = document.getElementById('booking-store-doctor-id');
        const storeServiceInput = document.getElementById('booking-store-service-id');
        const storeDateInput = document.getElementById('booking-store-date');

        const doctorsById = new Map(doctors.map((doctor) => [String(doctor.id), doctor]));
        const servicesById = new Map(services.map((service) => [String(service.id), service]));

        const formatPrice = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

        const el = (tag, className, text) => {
            const node = document.createElement(tag);
            if (className) {
                node.className = className;
            }
            if (text !== undefined) {
                node.textContent = text;
            }
            return node;
        };

        const updateStoreInputs = () => {
            if (storeDoctorInput) {
                storeDoctorInput.value = doctorSelect?.value || '';
            }
            if (storeServiceInput) {
                storeServiceInput.value = serviceSelect?.value || '';
            }
            if (storeDateInput) {
                storeDateInput.value = dateInput?.value || '';
            }
        };

        const renderDoctorCard = () => {
            const doctor = doctorsById.get(doctorSelect?.value || '');

            if (! doctorCard || ! doctor) {
                doctorCard?.classList.add('d-none');
                doctorCard?.replaceChildren();
                return;
            }

            doctorCard.classList.remove('d-none');
            doctorCard.replaceChildren(
                el('div', 'fw-semibold mb-1', doctor.name),
                el('div', 'small text-secondary mb-3', doctor.specialization || '')
            );

            const service = servicesById.get(serviceSelect?.value || '');
            if (service) {
                const selectedService = el('div', 'small text-secondary mb-3');
                selectedService.append('Layanan dipilih: ');
                selectedService.appendChild(el('span', 'fw-semibold text-dark', service.name));
                selectedService.append(` • ${service.duration} menit`);
                doctorCard.appendChild(selectedService);
            }

            const scheduleList = el('div', 'small text-secondary d-grid gap-1');
            if (doctor.schedules.length === 0) {
                scheduleList.appendChild(el('span', null, 'Belum ada jadwal praktik.'));
            } else {
                doctor.schedules.forEach((schedule) => {
                    scheduleList.appendChild(el('span', null, `${schedule.label} • ${schedule.time} • Kuota ${schedule.quota}`));
                });
            }
            doctorCard.appendChild(scheduleList);
        };

        const renderServiceSummary = () => {
            const service = servicesById.get(serviceSelect?.value || '');

            if (! serviceSummary || ! service) {
                serviceSummary?.classList.add('d-none');
                serviceSummary?.replaceChildren();
                return;
            }

            serviceSummary.classList.remove('d-none');
            serviceSummary.replaceChildren(el('div', 'fw-semibold', service.name));

            const meta = el('div', 'small text-secondary');
            meta.append(`Durasi ${service.duration} menit`);

            if (service.discountPercent) {
                meta.append(' • Harga normal ');
                meta.appendChild(el('span', 'text-decoration-line-through', `Rp ${formatPrice(service.originalPrice)}`));
                meta.append(` • Diskon ${service.discountPercent}% • Harga setelah diskon Rp ${formatPrice(service.price)}`);
            } else {
                meta.append(` • Rp ${formatPrice(service.price)}`);
            }

            serviceSummary.appendChild(meta);
        };

        const renderSlotMessage = (message) => {
            if (! slotPanel) {
                return;
            }

            slotPanel.replaceChildren(el('div', 'alert alert-light border', message));
            if (submitButton) {
                submitButton.disabled = true;
            }
        };

        const renderSlots = (slots) => {
            if (! slotPanel) {
                return;
            }

            if (slots.length === 0) {
                renderSlotMessage('Belum ada slot tersedia pada tanggal ini. Coba dokter atau tanggal lain.');
                return;
            }

            const grid = el('div', 'slot-grid');
            slots.forEach((slot) => {
                const label = el('label', 'slot-option');
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'booking_time';
                input.value = slot;
                input.required = true;
                label.appendChild(input);
                label.appendChild(el('span', null, slot));
                grid.appendChild(label);
            });

            slotPanel.replaceChildren(grid);
            if (submitButton) {
                submitButton.disabled = false;
            }
        };

        const resetSlotsAfterSelectionChange = () => {
            const hasCompleteSelection = doctorSelect?.value && serviceSelect?.value && dateInput?.value;
            renderSlotMessage(
                hasCompleteSelection
                    ? 'Klik tombol Cek slot tersedia untuk melihat pilihan jam kunjungan.'
                    : 'Pilih dokter, layanan, dan tanggal terlebih dahulu untuk melihat slot tersedia.'
            );
        };

        const fetchSlots = async () => {
            updateStoreInputs();

            if (! doctorSelect?.value || ! serviceSelect?.value || ! dateInput?.value) {
                resetSlotsAfterSelectionChange();
                return;
            }

            const originalButtonText = checkButton?.textContent;
            if (checkButton) {
                checkButton.disabled = true;
                checkButton.textContent = 'Memuat slot...';
            }
            renderSlotMessage('Memuat slot tersedia...');

            const params = new URLSearchParams({
                doctor_id: doctorSelect.value,
                service_id: serviceSelect.value,
                booking_date: dateInput.value,
            });

            try {
                const response = await fetch(`${slotsUrl}?${params.toString()}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (! response.ok) {
                    const payload = await response.json().catch(() => ({}));
                    const firstError = Object.values(payload.errors || {})[0]?.[0];
                    throw new Error(firstError || 'Slot belum bisa dicek. Periksa pilihan dokter, layanan, dan tanggal.');
                }

                const payload = await response.json();
                renderSlots(payload.slots || []);
            } catch (error) {
                renderSlotMessage(error.message);
            } finally {
                if (checkButton) {
                    checkButton.disabled = false;
                    checkButton.textContent = originalButtonText || 'Cek slot tersedia';
                }
            }
        };

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            fetchSlots();
        });

        [doctorSelect, serviceSelect, dateInput].forEach((input) => {
            input?.addEventListener('change', () => {
                updateStoreInputs();
                renderDoctorCard();
                renderServiceSummary();
                resetSlotsAfterSelectionChange();
            });
        });

        updateStoreInputs();
        renderDoctorCard();
        renderServiceSummary();

        if (slotSearchRequested && initialSlots.length > 0) {
            renderSlots(initialSlots);
        } else if (! slotSearchRequested) {
            resetSlotsAfterSelectionChange();
        }
    })();
</script>
