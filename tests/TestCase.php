<?php

namespace Tests;

use App\Models\McuPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createUser(string $role = 'karyawan', array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test '.ucfirst($role),
            'email' => strtolower($role).'@test.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'nip' => strtoupper(substr($role, 0, 3)).'001',
            'departemen' => 'Test Dept',
        ], $overrides));
    }

    protected function loginAs(string $role): User
    {
        $user = $this->createUser($role);
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer '.$token);
        return $user;
    }

    protected function createPackage(bool $hasRadiologi = false, array $items = []): McuPackage
    {
        $package = McuPackage::create([
            'nama_paket' => 'Test Package',
            'deskripsi' => 'Test description',
            'harga' => 100000,
            'is_active' => true,
            'has_radiologi' => $hasRadiologi,
        ]);

        if (empty($items)) {
            $items = [
                ['nama_pemeriksaan' => 'Hemoglobin', 'satuan' => 'g/dL', 'nilai_normal' => '13-17'],
            ];
        }

        foreach ($items as $item) {
            $package->items()->create($item);
        }

        return $package;
    }
}
