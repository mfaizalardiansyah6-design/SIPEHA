<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama_layanan' => 'Video Call', 'slug' => 'video-call', 'tipe_layanan' => 'harian'],
            ['nama_layanan' => 'Potong Rambut', 'slug' => 'potong-rambut', 'tipe_layanan' => 'mingguan'],
            ['nama_layanan' => 'Potong Kuku', 'slug' => 'potong-kuku', 'tipe_layanan' => 'mingguan'],
            ['nama_layanan' => 'Pemeriksaan Kesehatan', 'slug' => 'pemeriksaan-kesehatan', 'tipe_layanan' => 'harian'],
            ['nama_layanan' => 'Peminjaman Buku', 'slug' => 'peminjaman-buku', 'tipe_layanan' => 'harian'],
            ['nama_layanan' => 'Layanan Laundry', 'slug' => 'layanan-laundry', 'tipe_layanan' => 'harian'],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
