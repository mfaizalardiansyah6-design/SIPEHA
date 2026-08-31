<?php

namespace Database\Seeders;

use App\Models\BorrowBook;
use App\Models\Wbp;
use Illuminate\Database\Seeder;

class BorrowBookSeeder extends Seeder
{
    public function run(): void
    {
        $wbps = Wbp::pluck('id');

        if ($wbps->isEmpty()) {
            return;
        }

        BorrowBook::factory()->count(25)->create(
            fn () => ['wbp_id' => $wbps->random()],
        );
    }
}
