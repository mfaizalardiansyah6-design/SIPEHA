<?php

namespace Database\Seeders;

use App\Models\Wbp;
use Illuminate\Database\Seeder;

class WbpSeeder extends Seeder
{
    public function run(): void
    {
        Wbp::factory()->count(50)->create();
    }
}
