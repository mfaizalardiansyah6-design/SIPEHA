<?php

namespace Database\Factories;

use App\Enums\MonitoringStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitoringLog>
 */
class MonitoringLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MonitoringLog>
     */
    protected $model = MonitoringLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([MonitoringStatus::Selesai, MonitoringStatus::Belum]);

        return [
            'wbp_id' => Wbp::factory(),
            'user_id' => User::factory(),
            'category_id' => ServiceCategory::factory(),
            'status' => $status,
            'tanggal' => fake()->dateTimeBetween('-30 days', 'today'),
            'waktu_mulai' => fake()->time('H:i'),
            'waktu_selesai' => null,
            'keterangan' => null,
        ];
    }

    public function forCategory(ServiceCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function withStatus(MonitoringStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
