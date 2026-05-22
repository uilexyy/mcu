<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin RS Juwita',
            'email' => 'admin@rsjuwita.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'nip' => 'ADM001',
            'departemen' => 'Administrasi',
        ]);

        User::create([
            'name' => 'Dokter Umum',
            'email' => 'dokter@rsjuwita.com',
            'password' => bcrypt('password'),
            'role' => 'dokter_umum',
            'nip' => 'DOK001',
            'departemen' => 'Poliklinik Umum',
        ]);

        User::create([
            'name' => 'Laboratorium',
            'email' => 'lab@rsjuwita.com',
            'password' => bcrypt('password'),
            'role' => 'laboratorium',
            'nip' => 'LAB001',
            'departemen' => 'Laboratorium',
        ]);

        User::create([
            'name' => 'Radiologi',
            'email' => 'radio@rsjuwita.com',
            'password' => bcrypt('password'),
            'role' => 'radiologi',
            'nip' => 'RAD001',
            'departemen' => 'Radiologi',
        ]);

        $this->call(McuPackageSeeder::class);
    }
}
