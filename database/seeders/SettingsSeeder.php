<?php

namespace Database\Seeders;

use App\Services\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(Settings::class);

        foreach (Settings::defaults() as $key => $value) {
            $settings->set($key, $value);
        }
    }
}
