<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Karyawan Test',
            'email' => 'karyawan@test.com',
            'password' => 'password',
            'nip' => 'KRY001',
            'departemen' => 'IT',
            'tanggal_lahir' => '1995-01-01',
            'jenis_kelamin' => 'L',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'karyawan@test.com', 'role' => 'karyawan']);
    }

    public function test_register_validation_error(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

    public function test_login(): void
    {
        $this->createUser('karyawan', ['email' => 'login@test.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_login_wrong_password(): void
    {
        $this->createUser('karyawan', ['email' => 'login2@test.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login2@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
    }

    public function test_logout(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($user, 'sanctum');
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $this->assertCount(0, $user->tokens);
    }

    public function test_profile_show(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role']]);
    }

    public function test_profile_update(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($user, 'sanctum');
        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    public function test_update_password(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($user, 'sanctum');
        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password',
            'new_password' => 'newpassword',
        ]);

        $response->assertOk();
    }

    public function test_update_password_wrong_current(): void
    {
        $user = $this->createUser('karyawan');

        $this->actingAs($user, 'sanctum');
        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'wrongpass',
            'new_password' => 'newpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_access(): void
    {
        $response = $this->getJson('/api/v1/profile');
        $response->assertStatus(401);
    }
}
