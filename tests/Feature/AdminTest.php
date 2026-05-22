<?php

namespace Tests\Feature;

use App\Models\McuPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createUser('admin');
    }

    public function test_index_packages(): void
    {
        $this->createPackage();
        $this->createPackage();

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/packages');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_store_package(): void
    {
        $this->actingAs($this->admin, 'sanctum');
        $response = $this->postJson('/api/v1/admin/packages', [
            'nama_paket' => 'New Package',
            'deskripsi' => 'Desc',
            'harga' => 200000,
            'is_active' => true,
            'has_radiologi' => true,
            'items' => [
                ['nama_pemeriksaan' => 'Test Item', 'satuan' => 'mg', 'nilai_normal' => '10-20'],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nama_paket', 'New Package')
            ->assertJsonPath('data.has_radiologi', true);

        $this->assertDatabaseHas('mcu_packages', ['nama_paket' => 'New Package']);
        $this->assertDatabaseHas('mcu_package_items', ['nama_pemeriksaan' => 'Test Item']);
    }

    public function test_show_package(): void
    {
        $pkg = $this->createPackage();

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/packages/'.$pkg->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $pkg->id);
    }

    public function test_update_package(): void
    {
        $pkg = $this->createPackage();

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->putJson('/api/v1/admin/packages/'.$pkg->id, [
            'nama_paket' => 'Updated Package',
            'harga' => 300000,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('mcu_packages', ['nama_paket' => 'Updated Package']);
    }

    public function test_destroy_package(): void
    {
        $pkg = $this->createPackage();

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->deleteJson('/api/v1/admin/packages/'.$pkg->id);

        $response->assertOk();
        $this->assertDatabaseMissing('mcu_packages', ['id' => $pkg->id]);
    }

    public function test_index_users(): void
    {
        $this->createUser('karyawan');
        $this->createUser('dokter_umum');

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/users');

        $response->assertOk();
    }

    public function test_store_user(): void
    {
        $this->actingAs($this->admin, 'sanctum');
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password',
            'role' => 'laboratorium',
            'nip' => 'NEW001',
            'departemen' => 'IT',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_show_user(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/users/'.$user->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_update_user(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->putJson('/api/v1/admin/users/'.$user->id, [
            'name' => 'Updated User',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['name' => 'Updated User']);
    }

    public function test_destroy_user(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->deleteJson('/api/v1/admin/users/'.$user->id);

        $response->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_index_registrations(): void
    {
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_idx@test.com', 'nip' => 'IDX001']);
        $pkg = $this->createPackage();

        $this->actingAs($karyawan, 'sanctum');
        $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $pkg->id,
            'tanggal_jadwal' => '2026-06-01',
        ]);

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/registrations');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_approve_registration(): void
    {
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_appr@test.com', 'nip' => 'APR001']);
        $pkg = $this->createPackage();

        $this->actingAs($karyawan, 'sanctum');
        $reg = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $pkg->id,
            'tanggal_jadwal' => '2026-06-01',
        ]);
        $regId = $reg->json('data.id');

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->putJson("/api/v1/admin/registrations/{$regId}/approve", [
            'tanggal_jadwal' => now()->toDateString(),
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('mcu_registrations', ['id' => $regId, 'status' => 'approved']);
    }

    public function test_reject_registration(): void
    {
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_rej@test.com', 'nip' => 'REJ001']);
        $pkg = $this->createPackage();

        $this->actingAs($karyawan, 'sanctum');
        $reg = $this->postJson('/api/v1/karyawan/registrations', [
            'package_id' => $pkg->id,
            'tanggal_jadwal' => '2026-06-01',
        ]);
        $regId = $reg->json('data.id');

        $this->actingAs($this->admin, 'sanctum');
        $response = $this->putJson("/api/v1/admin/registrations/{$regId}/reject", [
            'catatan_admin' => 'Data tidak lengkap',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('mcu_registrations', ['id' => $regId, 'status' => 'rejected']);
    }

    public function test_stats(): void
    {
        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['summary', 'monthly', 'by_package', 'recent']]);
    }

    public function test_activity_logs(): void
    {
        $this->actingAs($this->admin, 'sanctum');
        $response = $this->getJson('/api/v1/admin/activity-logs');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $karyawan = $this->createUser('karyawan', ['email' => 'karyawan_noadmin@test.com', 'nip' => 'NOA001']);

        $this->actingAs($karyawan, 'sanctum');
        $response = $this->getJson('/api/v1/admin/packages');
        $response->assertStatus(403);
    }
}
