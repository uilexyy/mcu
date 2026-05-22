<?php

namespace Tests\Feature;

use App\Models\McuPackage;
use App\Models\McuRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTest extends TestCase
{
    use RefreshDatabase;

    private User $lab;
    private McuRegistration $registration;
    private array $items;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->createUser('admin', ['email' => 'admin_lab@test.com', 'nip' => 'ADM003']);
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_lab@test.com', 'nip' => 'KRY003']);
        $dokter = $this->createUser('dokter_umum', ['email' => 'dokter_lab@test.com', 'nip' => 'DOK003']);
        $this->lab = $this->createUser('laboratorium', ['email' => 'lab_test@test.com', 'nip' => 'LAB002']);

        $package = $this->createPackage(false, [
            ['nama_pemeriksaan' => 'Hemoglobin', 'satuan' => 'g/dL', 'nilai_normal' => '13-17'],
            ['nama_pemeriksaan' => 'Leukosit', 'satuan' => '/μL', 'nilai_normal' => '4000-10000'],
        ]);
        $this->items = $package->items->toArray();

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

        $registration = McuRegistration::find($regId);

        $this->actingAs($dokter, 'sanctum');
        $this->postJson('/api/v1/dokter/physical-exam', [
            'registration_id' => $registration->id,
            'tekanan_darah' => '120/80',
            'berat_badan' => 70,
            'tinggi_badan' => 170,
        ]);

        $this->registration = $registration->fresh();
    }

    public function test_queue(): void
    {
        $this->actingAs($this->lab, 'sanctum');
        $response = $this->getJson('/api/v1/lab/queue');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_results_for_non_radiologi_package_completes_flow(): void
    {
        $this->actingAs($this->lab, 'sanctum');
        $response = $this->postJson('/api/v1/lab/results', [
            'registration_id' => $this->registration->id,
            'results' => [
                ['item_id' => $this->items[0]['id'], 'nilai' => '14.5', 'keterangan' => 'Normal'],
                ['item_id' => $this->items[1]['id'], 'nilai' => '5500', 'keterangan' => 'Normal'],
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('mcu_lab_results', ['registration_id' => $this->registration->id]);
        $this->assertDatabaseHas('mcu_registrations', [
            'id' => $this->registration->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('mcu_results', ['registration_id' => $this->registration->id]);
    }

    public function test_store_results(): void
    {
        $this->actingAs($this->lab, 'sanctum');
        $response = $this->postJson('/api/v1/lab/results', [
            'registration_id' => $this->registration->id,
            'results' => [
                ['item_id' => $this->items[0]['id'], 'nilai' => '14.5', 'keterangan' => 'Normal'],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('mcu_lab_results', ['registration_id' => $this->registration->id]);
    }

    public function test_update_results(): void
    {
        $this->actingAs($this->lab, 'sanctum');
        $this->postJson('/api/v1/lab/results', [
            'registration_id' => $this->registration->id,
            'results' => [
                ['item_id' => $this->items[0]['id'], 'nilai' => '14.5', 'keterangan' => 'Normal'],
            ],
        ]);

        $this->actingAs($this->lab, 'sanctum');
        $response = $this->putJson('/api/v1/lab/results/'.$this->registration->id, [
            'registration_id' => $this->registration->id,
            'results' => [
                ['item_id' => $this->items[0]['id'], 'nilai' => '15.0', 'keterangan' => 'Normal'],
            ],
        ]);

        $response->assertOk();
    }

    public function test_invalid_status_for_lab(): void
    {
        $this->registration->update(['status' => 'pending']);

        $this->actingAs($this->lab, 'sanctum');
        $response = $this->postJson('/api/v1/lab/results', [
            'registration_id' => $this->registration->id,
            'results' => [
                ['item_id' => $this->items[0]['id'], 'nilai' => '14.5', 'keterangan' => 'Normal'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_riwayat(): void
    {
        $this->actingAs($this->lab, 'sanctum');
        $response = $this->getJson('/api/v1/lab/riwayat');

        $response->assertOk();
    }

    public function test_history(): void
    {
        $this->actingAs($this->lab, 'sanctum');
        $response = $this->getJson('/api/v1/lab/registrations/'.$this->registration->id.'/history');

        $response->assertOk();
    }
}
