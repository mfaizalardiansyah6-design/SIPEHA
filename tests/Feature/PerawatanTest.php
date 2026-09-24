<?php

namespace Tests\Feature;

use App\Enums\MonitoringStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerawatanTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->create();
        $this->category = ServiceCategory::factory()->create(['slug' => 'potong-rambut']);
    }

    public function test_perawatan_page_renders_with_blok_filter_and_form(): void
    {
        Wbp::factory()->create(['nama' => 'ADI SAPUTRA', 'blok_kamar' => 'A1', 'no_register' => 'REG-001']);
        Wbp::factory()->create(['nama' => 'BUDI LAIN', 'blok_kamar' => 'B1', 'no_register' => 'REG-002']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.perawatan', ['tab' => 'potong-rambut']))
            ->assertOk()
            ->assertSee('Catat Perawatan Baru')
            ->assertSee('Cari blok...')
            ->assertSee('Cari WBP / No. Reg...');
    }

    public function test_perawatan_page_preserves_blok_filter_across_tabs(): void
    {
        ServiceCategory::factory()->create(['slug' => 'potong-kuku']);
        Wbp::factory()->create(['nama' => 'ADI SAPUTRA', 'blok_kamar' => 'A1', 'no_register' => 'REG-001']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.perawatan', ['tab' => 'potong-kuku', 'blok' => 'A1', 'q' => 'ADI']))
            ->assertOk()
            ->assertSee('A1');
    }

    public function test_perawatan_set_status_still_saves_for_wbp(): void
    {
        $wbp = Wbp::factory()->create();

        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'wbp_id' => $wbp->id,
                'category_id' => $this->category->id,
                'status' => MonitoringStatus::Selesai->value,
                'tanggal' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('monitoring_logs', [
            'wbp_id' => $wbp->id,
            'category_id' => $this->category->id,
            'status' => MonitoringStatus::Selesai->value,
        ]);
    }

    public function test_perawatan_set_status_requires_wbp(): void
    {
        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'category_id' => $this->category->id,
                'status' => MonitoringStatus::Selesai->value,
                'tanggal' => today()->toDateString(),
            ])
            ->assertSessionHasErrors('wbp_id');

        $this->assertSame(0, MonitoringLog::count());
    }
}
