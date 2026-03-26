<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Notifications\MedicalNoteReadyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesClinicData;
use Tests\TestCase;

class MedicalNoteTest extends TestCase
{
    use CreatesClinicData;
    use RefreshDatabase;

    public function test_doctor_can_store_medical_note_and_complete_booking(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();
        $booking = $this->createBooking($patient, $doctor, $service, [
            'booking_status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($doctor)->post(route('doctor.medical-notes.store', $booking), [
            'diagnosis' => 'Gigi sensitif',
            'treatment' => 'Scaling dan edukasi perawatan',
            'prescription' => 'Pasta gigi sensitif',
            'notes' => 'Kontrol 2 minggu lagi',
        ]);

        $response->assertRedirect(route('doctor.medical-notes.index'));

        $this->assertDatabaseHas('medical_notes', [
            'booking_id' => $booking->id,
            'doctor_id' => $doctor->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => BookingStatus::Completed->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => MedicalNoteReadyNotification::class,
            'notifiable_id' => $patient->id,
        ]);
    }

    public function test_patient_can_download_medical_record_pdf(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();
        $booking = $this->createBooking($patient, $doctor, $service, [
            'booking_status' => BookingStatus::Completed,
        ]);

        $booking->medicalNote()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'diagnosis' => 'Tes diagnosis',
            'treatment' => 'Tes treatment',
            'prescription' => 'Tes resep',
            'notes' => 'Tes catatan',
        ]);

        $this->actingAs($patient)
            ->get(route('history.medical-record', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
