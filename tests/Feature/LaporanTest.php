<?php

namespace Tests\Feature;

use App\Enums\MonitoringStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->create();

        $category = ServiceCategory::factory()->create(['slug' => 'video-call']);
        $wbp = Wbp::factory()->create();

        MonitoringLog::create([
            'wbp_id' => $wbp->id,
            'user_id' => $this->petugas->id,
            'category_id' => $category->id,
            'status' => MonitoringStatus::Selesai,
            'tanggal' => today(),
        ]);
    }

    public function test_laporan_page_renders_aggregates(): void
    {
        $this->actingAs($this->petugas)
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Video Call');
    }

    public function test_excel_export_downloads_file(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('laporan.excel'));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_pdf_export_downloads_file(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('laporan.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
    }
}
