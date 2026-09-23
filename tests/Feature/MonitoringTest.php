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

    public function test_feature_pages_survive_deleted_wbp(): void
    {
        $videoCategory = ServiceCategory::factory()->create(['slug' => 'video-call']);
        $wbp = Wbp::factory()->create();
        $wbp->monitoringLogs()->create([
            'user_id' => $this->petugas->id,
            'category_id' => $videoCategory->id,
            'status' => MonitoringStatus::Selesai,
            'tanggal' => today(),
            'waktu_mulai' => '10:00',
        ]);

        $wbp->delete();

        $this->actingAs($this->petugas)
            ->get(route('monitoring.video-call'))
            ->assertOk()
            ->assertDontSee($wbp->nama);

        $this->actingAs($this->petugas)
            ->get(route('jadwal'))
            ->assertOk()
            ->assertDontSee($wbp->nama);
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

    public function test_video_call_page_shows_kunjungan_label(): void
    {
        ServiceCategory::factory()->create(['slug' => 'video-call']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.video-call'))
            ->assertOk()
            ->assertSee('Kunjungan')
            ->assertDontSee('Video Call');
    }

    public function test_wbp_search_returns_matching_active_wbp(): void
    {
        Wbp::factory()->create(['nama' => 'ADI SAPUTRA', 'blok_kamar' => 'A-01', 'no_register' => 'REG-001']);
        Wbp::factory()->create(['nama' => 'BUDI LAIN', 'blok_kamar' => 'B-09', 'no_register' => 'REG-002']);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.wbp-search', ['q' => 'ADI']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.nama', 'ADI SAPUTRA');

        $this->actingAs($this->petugas)
            ->get(route('monitoring.wbp-search', ['q' => 'B-09']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.nama', 'BUDI LAIN');

        $this->actingAs($this->petugas)
            ->get(route('monitoring.wbp-search', ['q' => 'A']))
            ->assertOk()
            ->assertJsonCount(0, 'results');
    }

    public function test_video_call_page_filters_by_name_and_blok(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'video-call']);
        $ahmad = Wbp::factory()->create(['nama' => 'AHMAD YANI', 'blok_kamar' => 'A-01']);
        $cahyo = Wbp::factory()->create(['nama' => 'CAHYO WIBOWO', 'blok_kamar' => 'B-09']);

        foreach ([$ahmad, $cahyo] as $wbp) {
            $wbp->monitoringLogs()->create([
                'user_id' => $this->petugas->id,
                'category_id' => $category->id,
                'status' => MonitoringStatus::Selesai,
                'tanggal' => today(),
            ]);
        }

        $this->actingAs($this->petugas)
            ->get(route('monitoring.video-call', ['q' => 'AHMAD']))
            ->assertOk()
            ->assertSee('AHMAD YANI')
            ->assertDontSee('CAHYO WIBOWO');

        $this->actingAs($this->petugas)
            ->get(route('monitoring.video-call', ['blok' => 'B-09']))
            ->assertOk()
            ->assertSee('CAHYO WIBOWO')
            ->assertDontSee('AHMAD YANI');
    }

    public function test_update_session_changes_tanggal_and_waktu(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'video-call']);
        $wbp = Wbp::factory()->create();
        $log = $wbp->monitoringLogs()->create([
            'user_id' => $this->petugas->id,
            'category_id' => $category->id,
            'status' => MonitoringStatus::Selesai,
            'tanggal' => today(),
            'waktu_mulai' => '10:00',
        ]);

        $newDate = today()->addDays(2)->toDateString();

        $this->actingAs($this->petugas)
            ->patch(route('monitoring.update', $log), [
                'status' => MonitoringStatus::Belum->value,
                'tanggal' => $newDate,
                'waktu_mulai' => '11:30',
                'keterangan' => 'dijadwalkan ulang',
            ])
            ->assertRedirect();

        $log->refresh();

        $this->assertSame($newDate, $log->tanggal->toDateString());
        $this->assertSame(MonitoringStatus::Belum->value, $log->status->value);
        $this->assertSame('11:30', $log->waktu_mulai);
        $this->assertSame('dijadwalkan ulang', $log->keterangan);
    }

    public function test_perawatan_status_can_be_set_for_selected_date(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'potong-rambut']);
        $wbp = Wbp::factory()->create();
        $tanggal = today()->subDays(3)->toDateString();

        $this->actingAs($this->petugas)
            ->post(route('monitoring.set-status'), [
                'wbp_id' => $wbp->id,
                'category_id' => $category->id,
                'status' => MonitoringStatus::Selesai->value,
                'tanggal' => $tanggal,
            ])
            ->assertRedirect();

        $log = MonitoringLog::where('wbp_id', $wbp->id)
            ->where('category_id', $category->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($tanggal, $log->tanggal->toDateString());
        $this->assertSame(MonitoringStatus::Selesai->value, $log->status->value);

        $this->actingAs($this->petugas)
            ->get(route('monitoring.perawatan', ['tab' => 'potong-rambut', 'tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('Selesai');
    }
}
