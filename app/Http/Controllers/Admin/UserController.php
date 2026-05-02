<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $roles = UserRole::cases();
        $roleValues = array_map(fn (UserRole $role) => $role->value, $roles);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in($roleValues)],
            'status' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $users = User::query()
            ->withCount(['patientBookings', 'doctorBookings'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when(
                array_key_exists('status', $filters) && $filters['status'] !== null,
                fn ($query) => $query->where('Status', (int) $filters['status'])
            )
            ->orderByDesc('CreatedDate')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::query()->count(),
            'patients' => User::query()->where('role', UserRole::Patient->value)->count(),
            'doctors' => User::query()->where('role', UserRole::Doctor->value)->count(),
            'active' => User::query()->where('Status', 1)->count(),
        ];

        return view('admin.users.index', compact('filters', 'roles', 'stats', 'users'));
    }

    public function show(User $user): View
    {
        $user->loadCount(['patientBookings', 'doctorBookings']);

        return view('admin.users.show', [
            'user' => $user,
            'patientBookings' => $user->patientBookings()
                ->with(['doctor', 'payment'])
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->limit(5)
                ->get(),
            'doctorBookings' => $user->doctorBookings()
                ->with(['patient', 'payment'])
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->limit(5)
                ->get(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'managedUser' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:1000'],
            'role' => [
                'required',
                Rule::in(array_map(fn (UserRole $role) => $role->value, UserRole::cases())),
            ],
            'Status' => ['required', Rule::in(['0', '1'])],
        ]);

        if ($user->is($request->user()) && (
            $validated['role'] !== $user->role->value || (int) $validated['Status'] !== (int) $user->Status
        )) {
            return back()
                ->withErrors(['role' => 'Role dan status akun admin yang sedang digunakan tidak bisa diubah dari halaman ini.'])
                ->withInput();
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => $validated['role'],
            'Status' => (int) $validated['Status'],
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'Data user berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Akun admin yang sedang digunakan tidak bisa dinonaktifkan.');
        }

        $user->update(['Status' => $user->Status ? 0 : 1]);

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Akun admin yang sedang digunakan tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus dari daftar aktif.');
    }
}
