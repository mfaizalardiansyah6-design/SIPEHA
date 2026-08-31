<?php

namespace Database\Factories;

use App\Enums\BorrowStatus;
use App\Models\BorrowBook;
use App\Models\Wbp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BorrowBook>
 */
class BorrowBookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<BorrowBook>
     */
    protected $model = BorrowBook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalPinjam = fake()->dateTimeBetween('-45 days', 'today');

        return [
            'wbp_id' => Wbp::factory(),
            'judul_buku' => fake()->randomElement([
                'Ensiklopedia Hukum Pidana',
                'Fikih dan Kehidupan',
                'Sejarah Peradaban Islam',
                'Novel Laskar Pelangi',
                'Kamus Bahasa Indonesia',
                'Tafsir Al-Quran Juz 1-30',
                'Biografi Soekarno',
                'Panduan Budidaya Tanaman',
                'Kumpulan Cerita Rakyat Nusantara',
                'Buku Petunjuk Kewirausahaan',
            ]),
            'tanggal_pinjam' => $tanggalPinjam,
            'tanggal_kembali' => fake()->dateTimeBetween($tanggalPinjam->format('Y-m-d'), '+14 days'),
            'status' => fake()->randomElement([BorrowStatus::Dipinjam, BorrowStatus::Dikembalikan]),
        ];
    }
}
