<?php

namespace Tests\Feature;

use App\Enums\MonitoringStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->create();
        $this->category = ServiceCategory::factory()->create(['slug' => 'pemeriksaan-kesehatan']);
    }

    public function test_set_status_creates_log_for_wbp_and_category(): void
    {
        $wbp = Wbp::factory()->create();

        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'wbp_id' => $wbp->id,
                'category_id' => $this->category->id,
                'status' => MonitoringStatus::Hadir->value,
                'tanggal' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('monitoring_logs', [
            'wbp_id' => $wbp->id,
            'category_id' => $this->category->id,
            'status' => MonitoringStatus::Hadir->value,
        ]);
    }

    public function test_set_status_updates_existing_log_instead_of_duplicating(): void
    {
        $wbp = Wbp::factory()->create();
        $wbp->monitoringLogs()->create([
            'user_id' => $this->petugas->id,
            'category_id' => $this->category->id,
            'status' => MonitoringStatus::Hadir,
            'tanggal' => today(),
        ]);

        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'wbp_id' => $wbp->id,
                'category_id' => $this->category->id,
                'status' => MonitoringStatus::Izin->value,
                'tanggal' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertSame(1, MonitoringLog::where('wbp_id', $wbp->id)->count());
        $this->assertDatabaseHas('monitoring_logs', [
            'wbp_id' => $wbp->id,
            'status' => MonitoringStatus::Izin->value,
        ]);
    }

    public function test_video_call_page_lists_logs_for_selected_date(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'video-call']);
        $wbp = Wbp::factory()->create();
        $wbp->monitoringLogs()->create([
            'user_id' => $this->petugas->id,
            'category_id' => $category->id,
            'status' => MonitoringStatus::Selesai,
            'tanggal' => today(),
            'waktu_mulai' => '10:00',
        ]);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.video-call'))
            ->assertOk()
            ->assertSee($wbp->nama);
    }

    public function test_pemeriksaan_kesehatan_shows_sudah_belum_status(): void
    {
        $done = Wbp::factory()->create();
        $done->monitoringLogs()->create([
            'user_id' => $this->petugas->id,
            'category_id' => $this->category->id,
            'status' => MonitoringStatus::Selesai,
            'tanggal' => today(),
        ]);

        $pending = Wbp::factory()->create();

        $this->actingAs($this->petugas)
            ->get(route('monitoring.pemeriksaan-kesehatan'))
            ->assertOk()
            ->assertSee('Sudah: 1')
            ->assertSee('Belum: 1');
    }

    public function test_video_call_export_returns_xlsx(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'video-call']);
        Wbp::factory()->create()->monitoringLogs()->create([
            'user_id' => $this->petugas->id,
            'category_id' => $category->id,
            'status' => MonitoringStatus::Selesai,
            'tanggal' => today(),
        ]);

        $response = $this->actingAs($this->petugas)
            ->get(route('monitoring.video-call.export'));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }
}
