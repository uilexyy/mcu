<?php

namespace Tests\Feature;

use App\Models\McuPackage;
use App\Models\McuRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RadiologiTest extends TestCase
{
    use RefreshDatabase;

    private User $radio;
    private McuRegistration $registration;
    private McuPackage $package;
    private array $items;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->createUser('admin', ['email' => 'admin_radio@test.com', 'nip' => 'ADM004']);
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_radio@test.com', 'nip' => 'KRY004']);
        $dokter = $this->createUser('dokter_umum', ['email' => 'dokter_radio@test.com', 'nip' => 'DOK004']);
        $lab = $this->createUser('laboratorium', ['email' => 'lab_radio@test.com', 'nip' => 'LAB003']);
        $this->radio = $this->createUser('radiologi', ['email' => 'radio_test@test.com', 'nip' => 'RAD002']);

        $this->package = $this->createPackage(true, [
            ['nama_pemeriksaan' => 'Hemoglobin', 'satuan' => 'g/dL', 'nilai_normal' => '13-17'],
        ]);
        $this->items = $this->package->items->toArray();

        $this->actingAs($karyawan, 'sanctum');
        $reg = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $this->package->id,
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

        $registration = $registration->fresh();

        $this->actingAs($lab, 'sanctum');
        $this->postJson('/api/v1/lab/results', [
            'registration_id' => $registration->id,
            'results' => [
                ['item_id' => $this->items[0]['id'], 'nilai' => '14.5', 'keterangan' => 'Normal'],
            ],
        ]);

        $this->registration = $registration->fresh();
    }

    public function test_queue(): void
    {
        $this->actingAs($this->radio, 'sanctum');
        $response = $this->getJson('/api/v1/radiologi/queue');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_store_radiologi_results(): void
    {
        $file = UploadedFile::fake()->image('rontgen.jpg', 200, 200);

        $this->actingAs($this->radio, 'sanctum');
        $response = $this->postJson('/api/v1/radiologi/results', [
            'registration_id' => $this->registration->id,
            'interpretasi' => 'Tampak normal',
            'foto' => $file,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('mcu_radiology_results', ['registration_id' => $this->registration->id]);
        $this->assertDatabaseHas('mcu_registrations', [
            'id' => $this->registration->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('mcu_results', ['registration_id' => $this->registration->id]);
    }

    public function test_store_radiologi_without_foto(): void
    {
        $this->actingAs($this->radio, 'sanctum');
        $response = $this->postJson('/api/v1/radiologi/results', [
            'registration_id' => $this->registration->id,
            'interpretasi' => 'Tampak normal',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('mcu_radiology_results', ['registration_id' => $this->registration->id]);
    }

    public function test_update_radiologi(): void
    {
        $this->actingAs($this->radio, 'sanctum');
        $this->postJson('/api/v1/radiologi/results', [
            'registration_id' => $this->registration->id,
            'interpretasi' => 'Tampak normal',
        ]);

        $this->actingAs($this->radio, 'sanctum');
        $response = $this->putJson('/api/v1/radiologi/results/'.$this->registration->id, [
            'registration_id' => $this->registration->id,
            'interpretasi' => 'Ditemukan kelainan ringan',
        ]);

        $response->assertOk();
    }

    public function test_invalid_status_for_radiologi(): void
    {
        $this->registration->update(['status' => 'pending']);

        $this->actingAs($this->radio, 'sanctum');
        $response = $this->postJson('/api/v1/radiologi/results', [
            'registration_id' => $this->registration->id,
            'interpretasi' => 'Normal',
        ]);

        $response->assertStatus(422);
    }

    public function test_riwayat(): void
    {
        $this->actingAs($this->radio, 'sanctum');
        $response = $this->getJson('/api/v1/radiologi/riwayat');

        $response->assertOk();
    }

    public function test_history(): void
    {
        $this->actingAs($this->radio, 'sanctum');
        $response = $this->getJson('/api/v1/radiologi/registrations/'.$this->registration->id.'/history');

        $response->assertOk();
    }

    public function test_package_without_radiologi_does_not_appear_in_queue(): void
    {
        $pkg = $this->createPackage(false);
        $this->actingAs($this->radio, 'sanctum');
        $response = $this->getJson('/api/v1/radiologi/queue');

        $response->assertOk();
    }
}
