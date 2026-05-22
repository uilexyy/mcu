<?php

namespace Tests\Feature;

use App\Models\McuPackage;
use App\Models\McuRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokterTest extends TestCase
{
    use RefreshDatabase;

    private User $dokter;
    private McuRegistration $registration;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->createUser('admin', ['email' => 'admin_dokter@test.com', 'nip' => 'ADM002']);
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_dokter@test.com', 'nip' => 'KRY002']);
        $this->dokter = $this->createUser('dokter_umum', ['email' => 'dokter_test@test.com', 'nip' => 'DOK002']);
        $package = $this->createPackage();

        $this->actingAs($karyawan, 'sanctum');
        $reg = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $package->id,
            'tanggal_jadwal' => '2026-06-01',
        ]);
        $regId = $reg->json('data.id');

        $this->actingAs($admin, 'sanctum');
        $this->putJson("/api/v1/admin/registrations/{$regId}/approve", [
            'tanggal_jadwal' => now()->toDateString(),
        ]);

        $this->registration = McuRegistration::find($regId);
    }

    public function test_queue(): void
    {
        $this->actingAs($this->dokter, 'sanctum');
        $response = $this->getJson('/api/v1/dokter/queue');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_physical_exam(): void
    {
        $this->actingAs($this->dokter, 'sanctum');
        $response = $this->postJson('/api/v1/dokter/physical-exam', [
            'registration_id' => $this->registration->id,
            'tekanan_darah' => '120/80',
            'berat_badan' => 70,
            'tinggi_badan' => 170,
            'imt' => 24.2,
            'anamnesis' => 'Sehat',
            'catatan' => 'Tidak ada keluhan',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('mcu_physical_exams', ['registration_id' => $this->registration->id]);
        $this->assertDatabaseHas('mcu_registrations', ['id' => $this->registration->id, 'status' => 'doctor_done']);
    }

    public function test_update_physical_exam(): void
    {
        $this->actingAs($this->dokter, 'sanctum');
        $this->postJson('/api/v1/dokter/physical-exam', [
            'registration_id' => $this->registration->id,
            'tekanan_darah' => '120/80',
            'berat_badan' => 70,
            'tinggi_badan' => 170,
        ]);

        $this->registration->refresh();

        $this->actingAs($this->dokter, 'sanctum');
        $response = $this->putJson("/api/v1/dokter/physical-exam/{$this->registration->id}", [
            'registration_id' => $this->registration->id,
            'tekanan_darah' => '130/85',
            'berat_badan' => 72,
        ]);

        $response->assertOk();
    }

    public function test_invalid_status_for_exam(): void
    {
        $this->registration->update(['status' => 'completed']);

        $this->actingAs($this->dokter, 'sanctum');
        $response = $this->postJson('/api/v1/dokter/physical-exam', [
            'registration_id' => $this->registration->id,
            'tekanan_darah' => '120/80',
        ]);

        $response->assertStatus(422);
    }

    public function test_riwayat(): void
    {
        $this->actingAs($this->dokter, 'sanctum');
        $this->postJson('/api/v1/dokter/physical-exam', [
            'registration_id' => $this->registration->id,
            'tekanan_darah' => '120/80',
        ]);

        $this->actingAs($this->dokter, 'sanctum');
        $response = $this->getJson('/api/v1/dokter/riwayat');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_history_detail(): void
    {
        $this->actingAs($this->dokter, 'sanctum');
        $response = $this->getJson('/api/v1/dokter/registrations/'.$this->registration->id.'/history');

        $response->assertOk();
    }
}
