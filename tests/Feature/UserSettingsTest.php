<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('pengaturan-saya.edit'))->assertRedirect(route('login'));
    }

    public function test_regular_user_can_open_user_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pengaturan-saya.edit'))
            ->assertOk()
            ->assertSee('Pengaturan Akun')
            ->assertSee('Edit Profil')
            ->assertSee('Foto Profil');
    }

    public function test_user_can_update_appearance(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pengaturan-saya.edit'))
            ->put(route('pengaturan-saya.update-appearance'), [
                'theme_color' => 'teal',
                'mode' => 'dark',
            ])
            ->assertRedirect(route('pengaturan-saya.edit'))
            ->assertSessionHas('success');

        $this->assertSame('teal', $user->fresh()->theme_color);
        $this->assertSame('dark', $user->fresh()->mode);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'update',
            'target_table' => 'users',
            'target_id' => $user->id,
        ]);
    }

    public function test_user_appearance_overrides_global_appearance(): void
    {
        $user = User::factory()->create([
            'theme_color' => 'teal',
            'mode' => 'dark',
        ]);

        app(Settings::class)->set('tampilan', [
            'theme_color' => 'blue',
            'mode' => 'light',
            'bahasa' => 'id',
            'zona_waktu' => 'Asia/Jakarta',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="teal"', false)
            ->assertSee('data-mode="dark"', false);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pengaturan-saya.edit'))
            ->put(route('pengaturan-saya.update-profile'), [
                'name' => 'Nama Baru',
                'nip' => $user->nip,
                'email' => 'baru@simhak.test',
                'jabatan' => 'Petugas Kamtib',
            ])
            ->assertRedirect(route('pengaturan-saya.edit'))
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@simhak.test', $user->email);
        $this->assertSame('Petugas Kamtib', $user->jabatan);
    }

    public function test_profile_nip_must_be_unique(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pengaturan-saya.edit'))
            ->put(route('pengaturan-saya.update-profile'), [
                'name' => 'Nama Baru',
                'nip' => $other->nip,
                'email' => $user->email,
            ])
            ->assertSessionHasErrors('nip');

        $this->assertNotSame($other->nip, $user->fresh()->nip);
    }

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('uploads');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pengaturan-saya.edit'))
            ->put(route('pengaturan-saya.update-photo'), [
                'photo' => UploadedFile::fake()->image('foto.jpg', 200, 200),
            ])
            ->assertRedirect(route('pengaturan-saya.edit'))
            ->assertSessionHas('success');

        $this->assertNotNull($user->fresh()->profile_photo_path);
        Storage::disk('uploads')->assertExists($user->fresh()->profile_photo_path);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'update',
            'target_table' => 'users',
            'target_id' => $user->id,
        ]);
    }

    public function test_photo_upload_requires_valid_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pengaturan-saya.edit'))
            ->put(route('pengaturan-saya.update-photo'), [
                'photo' => UploadedFile::fake()->create('dokumen.txt', 10),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->fresh()->profile_photo_path);
    }
}
