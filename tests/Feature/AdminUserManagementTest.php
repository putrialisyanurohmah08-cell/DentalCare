<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesClinicData;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use CreatesClinicData;
    use RefreshDatabase;

    public function test_admin_can_view_registered_users(): void
    {
        $admin = $this->createAdmin();
        $patient = $this->createPatient([
            'name' => 'Putri Alisha',
            'email' => 'putri@example.com',
            'address' => 'Jl. Melati No. 10',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'Putri']))
            ->assertOk()
            ->assertSee('Data User')
            ->assertSee($patient->name)
            ->assertSee($patient->email)
            ->assertSee('Jl. Melati No. 10');
    }

    public function test_patient_cannot_open_admin_user_page(): void
    {
        $patient = $this->createPatient();

        $this->actingAs($patient)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_update_user_profile_and_status(): void
    {
        $admin = $this->createAdmin();
        $patient = $this->createPatient();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $patient), [
                'name' => 'Pasien Baru',
                'email' => 'pasien-baru@example.com',
                'phone' => '0811112222',
                'address' => 'Jl. Kenanga No. 3',
                'role' => UserRole::Patient->value,
                'Status' => '0',
            ])
            ->assertRedirect(route('admin.users.show', $patient));

        $patient->refresh();

        $this->assertSame('Pasien Baru', $patient->name);
        $this->assertSame('pasien-baru@example.com', $patient->email);
        $this->assertSame('0811112222', $patient->phone);
        $this->assertSame('Jl. Kenanga No. 3', $patient->address);
        $this->assertSame(0, $patient->Status);
    }

    public function test_admin_can_soft_delete_user(): void
    {
        $admin = $this->createAdmin();
        $patient = $this->createPatient();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $patient))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'IsDeleted' => 1,
        ]);
    }
}
