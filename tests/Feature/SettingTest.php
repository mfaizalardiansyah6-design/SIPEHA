<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_settings_and_audit_log_is_recorded(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('pengaturan.update', 'tampilan'), [
                'theme_color' => 'teal',
                'mode' => 'dark',
                'bahasa' => 'id',
                'zona_waktu' => 'Asia/Jakarta',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'tampilan']);

        $value = Setting::where('key', 'tampilan')->value('value');
        $this->assertSame('teal', $value['theme_color']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update',
            'target_table' => 'settings',
            'target_id' => 'tampilan',
        ]);
    }

    public function test_admin_can_reset_settings_to_defaults(): void
    {
        $admin = User::factory()->admin()->create();
        Setting::create(['key' => 'tampilan', 'value' => ['theme_color' => 'teal']]);

        $this->actingAs($admin)
            ->post(route('pengaturan.reset'))
            ->assertRedirect();

        $value = Setting::where('key', 'tampilan')->value('value');
        $this->assertSame('blue', $value['theme_color']);
    }

    public function test_petugas_cannot_update_settings(): void
    {
        $petugas = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($petugas)
            ->put(route('pengaturan.update', 'tampilan'), ['theme_color' => 'teal'])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('settings', ['key' => 'tampilan']);
    }
}
