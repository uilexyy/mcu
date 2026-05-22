<?php

namespace Tests\Feature;

use App\Models\McuPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private McuPackage $package;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser('karyawan');
        $this->package = $this->createPackage();
    }

    public function test_index_own_registrations(): void
    {
        $this->actingAs($this->user, 'sanctum');
        $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $this->package->id,
            'tanggal_jadwal' => '2026-06-01',
        ]);

        $this->actingAs($this->user, 'sanctum');
        $response = $this->getJson('/api/v1/karyawan/registrations');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_registration(): void
    {
        $this->actingAs($this->user, 'sanctum');
        $response = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $this->package->id,
            'tanggal_jadwal' => '2026-06-15',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('mcu_registrations', [
            'status' => 'pending',
            'package_id' => $this->package->id,
        ]);
    }

    public function test_store_registration_validation(): void
    {
        $this->actingAs($this->user, 'sanctum');
        $response = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => 9999,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_see_other_user_registrations(): void
    {
        $other = $this->createUser('karyawan', ['email' => 'other@test.com', 'nip' => 'OTH001']);

        $this->actingAs($other, 'sanctum');
        $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $this->package->id,
            'tanggal_jadwal' => '2026-06-01',
        ]);

        $this->actingAs($this->user, 'sanctum');
        $response = $this->getJson('/api/v1/karyawan/registrations');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_packages_list(): void
    {
        $this->actingAs($this->user, 'sanctum');
        $response = $this->getJson('/api/v1/packages');

        $response->assertOk();
    }

    public function test_non_karyawan_cannot_register(): void
    {
        $dokter = $this->createUser('dokter_umum');

        $this->actingAs($dokter, 'sanctum');
        $response = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $this->package->id,
        ]);

        $response->assertStatus(403);
    }
}
