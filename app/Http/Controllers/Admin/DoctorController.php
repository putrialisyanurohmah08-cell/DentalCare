<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        return view('admin.doctors.index', [
            'doctors' => User::query()
                ->where('role', UserRole::Doctor->value)
                ->with(['doctorProfile', 'doctorSchedules'])
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.doctors.form', [
            'doctor' => new User(['role' => UserRole::Doctor]),
            'profile' => new DoctorProfile(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDoctor($request);

        DB::transaction(function () use ($validated): void {
            $doctor = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => UserRole::Doctor,
                'password' => Hash::make($validated['password']),
            ]);

            $doctor->doctorProfile()->create([
                'specialization' => $validated['specialization'],
                'license_number' => $validated['license_number'],
                'biography' => $validated['biography'] ?? null,
                'experience_years' => $validated['experience_years'] ?? 0,
            ]);
        });

        return redirect()->route('admin.doctors.index')->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function edit(User $doctor): View
    {
        abort_unless($doctor->isDoctor(), 404);

        return view('admin.doctors.form', [
            'doctor' => $doctor->loadMissing('doctorProfile'),
            'profile' => $doctor->doctorProfile,
        ]);
    }

    public function update(Request $request, User $doctor): RedirectResponse
    {
        abort_unless($doctor->isDoctor(), 404);

        $validated = $this->validateDoctor($request, $doctor);

        DB::transaction(function () use ($doctor, $validated): void {
            $doctor->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => filled($validated['password'] ?? null)
                    ? Hash::make($validated['password'])
                    : $doctor->password,
            ]);

            $doctor->doctorProfile()->updateOrCreate(
                ['user_id' => $doctor->id],
                [
                    'specialization' => $validated['specialization'],
                    'license_number' => $validated['license_number'],
                    'biography' => $validated['biography'] ?? null,
                    'experience_years' => $validated['experience_years'] ?? 0,
                ]
            );
        });

        return redirect()->route('admin.doctors.index')->with('success', 'Data dokter berhasil diperbarui.');
    }

    private function validateDoctor(Request $request, ?User $doctor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctor?->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => [$doctor ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'specialization' => ['required', 'string', 'max:255'],
            'license_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('doctor_profiles', 'license_number')->ignore($doctor?->doctorProfile?->id),
            ],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'biography' => ['nullable', 'string'],
        ]);
    }
}
