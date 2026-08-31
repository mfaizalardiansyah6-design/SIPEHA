<?php

namespace Database\Factories;

use App\Enums\WbpStatus;
use App\Models\Wbp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wbp>
 */
class WbpFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Wbp>
     */
    protected $model = Wbp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nik = fake('id_ID')->nik();

        return [
            'nama' => fake('id_ID')->name(),
            'nik' => $nik,
            'nik_hash' => Wbp::hashNik($nik),
            'no_register' => 'REG-'.fake()->unique()->numberBetween(1000, 99999),
            'blok_kamar' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2']),
            'no_hp' => '08'.fake()->numerify('##########'),
            'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'status' => WbpStatus::Aktif,
            'foto_url' => null,
        ];
    }

    public function nonAktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WbpStatus::NonAktif,
        ]);
    }
}
