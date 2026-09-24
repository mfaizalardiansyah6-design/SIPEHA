<?php

namespace Tests\Feature;

use App\Enums\MonitoringStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaundryTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->create();
        $this->category = ServiceCategory::factory()->create(['slug' => 'layanan-laundry']);
    }

    public function test_laundry_page_renders_with_blok_filter_and_form(): void
    {
        Wbp::factory()->create(['nama' => 'ADI SAPUTRA', 'blok_kamar' => 'A1', 'no_register' => 'REG-001']);
        Wbp::factory()->create(['nama' => 'BUDI LAIN', 'blok_kamar' => 'B1', 'no_register' => 'REG-002']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.laundry'))
            ->assertOk()
            ->assertSee('Catat Laundry Baru')
            ->assertSee('Cari blok...')
            ->assertSee('Cari WBP / No. Reg...');
    }

    public function test_laundry_page_preserves_blok_filter_across_date_change(): void
    {
        Wbp::factory()->create(['nama' => 'ADI SAPUTRA', 'blok_kamar' => 'A1', 'no_register' => 'REG-001']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.laundry', ['blok' => 'A1', 'q' => 'ADI']))
            ->assertOk()
            ->assertSee('A1');
    }

    public function test_wbp_search_can_be_scoped_by_blok(): void
    {
        Wbp::factory()->create(['nama' => 'AHMAD YANI', 'blok_kamar' => 'A-01', 'no_register' => 'REG-001']);
        Wbp::factory()->create(['nama' => 'CAHYO WIBOWO', 'blok_kamar' => 'B-09', 'no_register' => 'REG-002']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.wbp-search', ['q' => 'AHM', 'blok' => 'A-01']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.nama', 'AHMAD YANI');

        $this->actingAs($this->petugas)
            ->get(route('monitoring.wbp-search', ['q' => 'AHM', 'blok' => 'B-09']))
            ->assertOk()
            ->assertJsonCount(0, 'results');

        $this->actingAs($this->petugas)
            ->get(route('monitoring.wbp-search', ['q' => 'CAH']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.nama', 'CAHYO WIBOWO');
    }

    public function test_laundry_set_status_still_saves_for_wbp(): void
    {
        $wbp = Wbp::factory()->create();

        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'wbp_id' => $wbp->id,
                'category_id' => $this->category->id,
                'status' => MonitoringStatus::Proses->value,
                'tanggal' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('monitoring_logs', [
            'wbp_id' => $wbp->id,
            'category_id' => $this->category->id,
            'status' => MonitoringStatus::Proses->value,
        ]);
    }

    public function test_laundry_set_status_requires_wbp(): void
    {
        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'category_id' => $this->category->id,
                'status' => MonitoringStatus::Proses->value,
                'tanggal' => today()->toDateString(),
            ])
            ->assertSessionHasErrors('wbp_id');

        $this->assertSame(0, MonitoringLog::count());
    }
}
