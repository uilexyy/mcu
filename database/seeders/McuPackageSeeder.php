<?php

namespace Database\Seeders;

use App\Models\McuPackage;
use Illuminate\Database\Seeder;

class McuPackageSeeder extends Seeder
{
    public function run(): void
    {
        $paketDasar = McuPackage::create([
            'nama_paket' => 'MCU Dasar',
            'deskripsi' => 'Pemeriksaan kesehatan dasar meliputi pemeriksaan fisik, darah lengkap, dan urinalisis',
            'harga' => 500000,
            'is_active' => true,
        ]);

        $itemsDasar = [
            ['nama_pemeriksaan' => 'Hemoglobin', 'satuan' => 'g/dL', 'nilai_normal' => 'L: 13-17, P: 12-16'],
            ['nama_pemeriksaan' => 'Leukosit', 'satuan' => '/μL', 'nilai_normal' => '4000-10000'],
            ['nama_pemeriksaan' => 'Trombosit', 'satuan' => '/μL', 'nilai_normal' => '150000-450000'],
            ['nama_pemeriksaan' => 'Gula Darah Puasa', 'satuan' => 'mg/dL', 'nilai_normal' => '70-110'],
            ['nama_pemeriksaan' => 'Kolesterol Total', 'satuan' => 'mg/dL', 'nilai_normal' => '<200'],
            ['nama_pemeriksaan' => 'Asam Urat', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: 3.4-7.0, P: 2.4-6.0'],
            ['nama_pemeriksaan' => 'Urinalisis - pH', 'satuan' => '', 'nilai_normal' => '4.5-8.0'],
            ['nama_pemeriksaan' => 'Urinalisis - Protein', 'satuan' => '', 'nilai_normal' => 'Negatif'],
            ['nama_pemeriksaan' => 'Urinalisis - Glukosa', 'satuan' => '', 'nilai_normal' => 'Negatif'],
        ];

        foreach ($itemsDasar as $item) {
            $paketDasar->items()->create($item);
        }

        $paketLengkap = McuPackage::create([
            'nama_paket' => 'MCU Lengkap',
            'deskripsi' => 'Pemeriksaan kesehatan lengkap meliputi pemeriksaan fisik, darah lengkap, fungsi hati, fungsi ginjal, urinalisis, dan rontgen thorax',
            'harga' => 1500000,
            'is_active' => true,
        ]);

        $itemsLengkap = [
            ['nama_pemeriksaan' => 'Hemoglobin', 'satuan' => 'g/dL', 'nilai_normal' => 'L: 13-17, P: 12-16'],
            ['nama_pemeriksaan' => 'Leukosit', 'satuan' => '/μL', 'nilai_normal' => '4000-10000'],
            ['nama_pemeriksaan' => 'Trombosit', 'satuan' => '/μL', 'nilai_normal' => '150000-450000'],
            ['nama_pemeriksaan' => 'Eritrosit', 'satuan' => 'juta/μL', 'nilai_normal' => 'L: 4.5-5.5, P: 4.0-5.0'],
            ['nama_pemeriksaan' => 'Hematokrit', 'satuan' => '%', 'nilai_normal' => 'L: 40-50, P: 35-45'],
            ['nama_pemeriksaan' => 'Gula Darah Puasa', 'satuan' => 'mg/dL', 'nilai_normal' => '70-110'],
            ['nama_pemeriksaan' => 'Kolesterol Total', 'satuan' => 'mg/dL', 'nilai_normal' => '<200'],
            ['nama_pemeriksaan' => 'Kolesterol LDL', 'satuan' => 'mg/dL', 'nilai_normal' => '<130'],
            ['nama_pemeriksaan' => 'Kolesterol HDL', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: >40, P: >50'],
            ['nama_pemeriksaan' => 'Trigliserida', 'satuan' => 'mg/dL', 'nilai_normal' => '<150'],
            ['nama_pemeriksaan' => 'Asam Urat', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: 3.4-7.0, P: 2.4-6.0'],
            ['nama_pemeriksaan' => 'SGOT (AST)', 'satuan' => 'U/L', 'nilai_normal' => '<35'],
            ['nama_pemeriksaan' => 'SGPT (ALT)', 'satuan' => 'U/L', 'nilai_normal' => '<45'],
            ['nama_pemeriksaan' => 'Kreatinin', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: 0.7-1.2, P: 0.5-1.0'],
            ['nama_pemeriksaan' => 'Ureum', 'satuan' => 'mg/dL', 'nilai_normal' => '10-50'],
            ['nama_pemeriksaan' => 'Urinalisis - pH', 'satuan' => '', 'nilai_normal' => '4.5-8.0'],
            ['nama_pemeriksaan' => 'Urinalisis - Protein', 'satuan' => '', 'nilai_normal' => 'Negatif'],
            ['nama_pemeriksaan' => 'Urinalisis - Glukosa', 'satuan' => '', 'nilai_normal' => 'Negatif'],
            ['nama_pemeriksaan' => 'Urinalisis - Bilirubin', 'satuan' => '', 'nilai_normal' => 'Negatif'],
        ];

        foreach ($itemsLengkap as $item) {
            $paketLengkap->items()->create($item);
        }

        $paketEksekutif = McuPackage::create([
            'nama_paket' => 'MCU Eksekutif',
            'deskripsi' => 'Pemeriksaan kesehatan komprehensif dengan tambahan pemeriksaan jantung dan rontgen thorax',
            'harga' => 2500000,
            'is_active' => true,
        ]);

        $itemsEksekutif = [
            ['nama_pemeriksaan' => 'Hemoglobin', 'satuan' => 'g/dL', 'nilai_normal' => 'L: 13-17, P: 12-16'],
            ['nama_pemeriksaan' => 'Leukosit', 'satuan' => '/μL', 'nilai_normal' => '4000-10000'],
            ['nama_pemeriksaan' => 'Trombosit', 'satuan' => '/μL', 'nilai_normal' => '150000-450000'],
            ['nama_pemeriksaan' => 'Eritrosit', 'satuan' => 'juta/μL', 'nilai_normal' => 'L: 4.5-5.5, P: 4.0-5.0'],
            ['nama_pemeriksaan' => 'Hematokrit', 'satuan' => '%', 'nilai_normal' => 'L: 40-50, P: 35-45'],
            ['nama_pemeriksaan' => 'Gula Darah Puasa', 'satuan' => 'mg/dL', 'nilai_normal' => '70-110'],
            ['nama_pemeriksaan' => 'Gula Darah 2 Jam PP', 'satuan' => 'mg/dL', 'nilai_normal' => '<140'],
            ['nama_pemeriksaan' => 'Kolesterol Total', 'satuan' => 'mg/dL', 'nilai_normal' => '<200'],
            ['nama_pemeriksaan' => 'Kolesterol LDL', 'satuan' => 'mg/dL', 'nilai_normal' => '<130'],
            ['nama_pemeriksaan' => 'Kolesterol HDL', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: >40, P: >50'],
            ['nama_pemeriksaan' => 'Trigliserida', 'satuan' => 'mg/dL', 'nilai_normal' => '<150'],
            ['nama_pemeriksaan' => 'Asam Urat', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: 3.4-7.0, P: 2.4-6.0'],
            ['nama_pemeriksaan' => 'SGOT (AST)', 'satuan' => 'U/L', 'nilai_normal' => '<35'],
            ['nama_pemeriksaan' => 'SGPT (ALT)', 'satuan' => 'U/L', 'nilai_normal' => '<45'],
            ['nama_pemeriksaan' => 'Kreatinin', 'satuan' => 'mg/dL', 'nilai_normal' => 'L: 0.7-1.2, P: 0.5-1.0'],
            ['nama_pemeriksaan' => 'Ureum', 'satuan' => 'mg/dL', 'nilai_normal' => '10-50'],
            ['nama_pemeriksaan' => 'Urinalisis - pH', 'satuan' => '', 'nilai_normal' => '4.5-8.0'],
            ['nama_pemeriksaan' => 'Urinalisis - Protein', 'satuan' => '', 'nilai_normal' => 'Negatif'],
            ['nama_pemeriksaan' => 'Urinalisis - Glukosa', 'satuan' => '', 'nilai_normal' => 'Negatif'],
            ['nama_pemeriksaan' => 'Urinalisis - Bilirubin', 'satuan' => '', 'nilai_normal' => 'Negatif'],
            ['nama_pemeriksaan' => 'EKG', 'satuan' => '', 'nilai_normal' => 'Normal'],
        ];

        foreach ($itemsEksekutif as $item) {
            $paketEksekutif->items()->create($item);
        }
    }
}
