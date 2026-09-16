<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wbp;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['email' => 'admin@simhak.test']);
        $this->petugas = User::factory()->create(['email' => 'petugas@simhak.test']);

        $this->seed(ServiceCategorySeeder::class);
        Wbp::factory()->count(3)->create();
    }

    public function test_admin_can_access_all_pages(): void
    {
        $pages = [
            'dashboard',
            'wbp.index',
            'monitoring.video-call',
            'monitoring.perawatan',
            'monitoring.pemeriksaan-kesehatan',
            'monitoring.buku',
            'monitoring.laundry',
            'jadwal',
            'laporan.index',
            'petugas.index',
            'pengaturan.index',
            'audit-logs',
        ];

        foreach ($pages as $page) {
            $this->actingAs($this->admin)->get(route($page))->assertOk();
        }
    }

    public function test_petugas_can_access_shared_pages(): void
    {
        $pages = [
            'dashboard',
            'wbp.index',
            'monitoring.video-call',
            'monitoring.perawatan',
            'monitoring.pemeriksaan-kesehatan',
            'monitoring.buku',
            'monitoring.laundry',
            'jadwal',
            'laporan.index',
        ];

        foreach ($pages as $page) {
            $this->actingAs($this->petugas)->get(route($page))->assertOk();
        }
    }

    public function test_petugas_is_redirected_from_admin_only_pages(): void
    {
        $pages = ['petugas.index', 'pengaturan.index', 'audit-logs'];

        foreach ($pages as $page) {
            $this->actingAs($this->petugas)
                ->get(route($page))
                ->assertRedirect(route('dashboard'));
        }
    }
}
