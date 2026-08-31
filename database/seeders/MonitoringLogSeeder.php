<?php

namespace Database\Seeders;

use App\Enums\MonitoringStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Database\Seeder;

class MonitoringLogSeeder extends Seeder
{
    public function run(): void
    {
        $wbps = Wbp::where('status', 'Aktif')->get();
        $users = User::where('role', 'user')->get();
        $categories = ServiceCategory::all();

        if ($wbps->isEmpty() || $users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        foreach ($categories as $category) {
            $statusOptions = MonitoringStatus::optionsFor($category->slug);

            foreach ($wbps as $wbp) {
                if (fake()->boolean(85)) {
                    $tanggal = $category->tipe_layanan === 'harian'
                        ? today()
                        : fake()->dateTimeBetween('-14 days', 'today');

                    MonitoringLog::factory()
                        ->forCategory($category)
                        ->create([
                            'wbp_id' => $wbp->id,
                            'user_id' => $users->random()->id,
                            'status' => fake()->randomElement($statusOptions),
                            'tanggal' => $tanggal,
                        ]);
                }

                foreach (range(1, fake()->numberBetween(0, 3)) as $ignored) {
                    MonitoringLog::factory()
                        ->forCategory($category)
                        ->create([
                            'wbp_id' => $wbp->id,
                            'user_id' => $users->random()->id,
                            'tanggal' => fake()->dateTimeBetween('-30 days', '-1 day'),
                        ]);
                }
            }
        }
    }
}
