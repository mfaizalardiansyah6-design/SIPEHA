<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsApplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_applies_theme_mode_locale_and_timezone_from_settings(): void
    {
        $admin = User::factory()->admin()->create();

        app(Settings::class)->set('tampilan', [
            'theme_color' => 'teal',
            'mode' => 'dark',
            'bahasa' => 'id',
            'zona_waktu' => 'Asia/Makassar',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="teal"', false)
            ->assertSee('data-mode="dark"', false)
            ->assertSee('<html lang="id"', false);

        $this->assertSame('Asia/Makassar', config('app.timezone'));
        $this->assertSame('id', config('app.locale'));
    }

    public function test_login_rate_limiter_respects_login_attempts_setting(): void
    {
        User::factory()->create([
            'nip' => '198708070010',
            'email' => 'petugas10@simhak.test',
            'password' => 'secret-password',
        ]);

        app(Settings::class)->set('sistem', [
            'audit_log' => true,
            'auto_lock' => true,
            'session_timeout' => 15,
            'login_attempts' => 1,
        ]);

        $this->post('/login', [
            'identity' => '198708070010',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('identity');

        $this->post('/login', [
            'identity' => '198708070010',
            'password' => 'secret-password',
        ])->assertSessionHasErrors('identity');

        $this->assertGuest();
    }

    public function test_disabling_audit_log_prevents_logging(): void
    {
        $admin = User::factory()->admin()->create();

        app(Settings::class)->set('sistem', [
            'audit_log' => false,
            'auto_lock' => true,
            'session_timeout' => 15,
            'login_attempts' => 5,
        ]);

        $this->actingAs($admin)
            ->put(route('pengaturan.update', 'tampilan'), [
                'theme_color' => 'teal',
                'mode' => 'light',
                'bahasa' => 'id',
                'zona_waktu' => 'Asia/Jakarta',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_backup_command_creates_sql_backup_file(): void
    {
        Storage::fake('local');

        $this->artisan('backup:database')->assertSuccessful();

        $files = Storage::disk('local')->files('backups');

        $this->assertNotEmpty($files);
        $this->assertNotEmpty(Storage::disk('local')->get($files[0]));
    }

    public function test_backup_command_pushes_backup_to_remote_api(): void
    {
        Storage::fake('local');

        app(Settings::class)->set('backup', [
            'jadwal' => 'daily',
            'waktu' => '02:00',
            'api_url' => 'https://example.com/backup',
            'api_key' => 'secret-key',
        ]);

        Http::fake([
            'https://example.com/backup' => Http::response('ok', 200),
        ]);

        $this->artisan('backup:database')->assertSuccessful();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://example.com/backup'
                && $request->hasHeader('Authorization', 'Bearer secret-key');
        });
    }

    public function test_instansi_branding_appears_on_login_and_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        app(Settings::class)->set('instansi', [
            'nama' => 'Rutan Cipinang',
            'kode' => 'LP-77',
            'alamat' => '',
            'telepon' => '',
            'email' => '',
            'logo' => null,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Rutan Cipinang');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rutan Cipinang')
            ->assertSee('LP-77');
    }
}
