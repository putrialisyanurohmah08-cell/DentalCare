<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.schedules.index', [
            'schedules' => DoctorSchedule::query()
                ->with(['doctor.doctorProfile'])
                ->orderBy('doctor_id')
                ->orderBy('day_of_week')
                ->paginate(12),
            'dayOptions' => DoctorSchedule::dayOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.schedules.form', [
            'schedule' => new DoctorSchedule(['slot_minutes' => config('clinic.slot_minutes')]),
            'doctors' => $this->doctorOptions(),
            'dayOptions' => DoctorSchedule::dayOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSchedule($request);

        DoctorSchedule::create($validated);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal dokter berhasil ditambahkan.');
    }

    public function edit(DoctorSchedule $schedule): View
    {
        return view('admin.schedules.form', [
            'schedule' => $schedule,
            'doctors' => $this->doctorOptions(),
            'dayOptions' => DoctorSchedule::dayOptions(),
        ]);
    }

    public function update(Request $request, DoctorSchedule $schedule): RedirectResponse
    {
        $validated = $this->validateSchedule($request, $schedule);

        $schedule->update($validated);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal dokter berhasil diperbarui.');
    }

    private function doctorOptions()
    {
        return User::query()
            ->where('role', UserRole::Doctor->value)
            ->with('doctorProfile')
            ->orderBy('name')
            ->get();
    }

    private function validateSchedule(Request $request, ?DoctorSchedule $schedule = null): array
    {
        $validated = $request->validate([
            'doctor_id' => ['required', Rule::exists('users', 'id')],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'quota' => ['required', 'integer', 'min:1', 'max:100'],
            'slot_minutes' => ['required', 'integer', 'min:15', 'max:120'],
        ]);

        $exists = DoctorSchedule::query()
            ->where('doctor_id', $validated['doctor_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->when($schedule, fn ($query) => $query->whereKeyNot($schedule->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'day_of_week' => 'Dokter tersebut sudah memiliki jadwal pada hari yang dipilih.',
            ]);
        }

        return $validated;
    }
}
