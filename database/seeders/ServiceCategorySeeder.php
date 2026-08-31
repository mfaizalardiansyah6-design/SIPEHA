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
            ['nama_layanan' => 'Kebutuhan Mandi', 'slug' => 'kebutuhan-mandi', 'tipe_layanan' => 'harian'],
            ['nama_layanan' => 'Alat Ibadah', 'slug' => 'alat-ibadah', 'tipe_layanan' => 'harian'],
            ['nama_layanan' => 'Peminjaman Buku', 'slug' => 'peminjaman-buku', 'tipe_layanan' => 'harian'],
            ['nama_layanan' => 'Senam', 'slug' => 'senam', 'tipe_layanan' => 'mingguan'],
            ['nama_layanan' => 'Pencucian Baju', 'slug' => 'pencucian-baju', 'tipe_layanan' => 'harian'],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
