<?php

namespace Tests\Feature;

use App\Enums\WbpStatus;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WbpManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->petugas = User::factory()->create();
    }

    private function validPayload(string $nik = '3201012000100110'): array
    {
        return [
            'nama' => 'Budi Santoso',
            'nik' => $nik,
            'no_register' => 'REG-99901',
            'blok_kamar' => 'A1',
            'agama' => 'Islam',
            'status' => WbpStatus::Aktif->value,
        ];
    }

    public function test_admin_can_open_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('wbp.create'))
            ->assertOk()
            ->assertSee('Tambah Data WBP');
    }

    public function test_admin_can_create_wbp(): void
    {
        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertRedirect(route('wbp.index'));

        $this->assertDatabaseHas('wbp', [
            'no_register' => 'REG-99901',
            'nik_hash' => Wbp::hashNik('3201012000100110'),
        ]);

        $wbp = Wbp::where('no_register', 'REG-99901')->first();
        $this->assertSame('3201012000100110', $wbp->nik);
    }

    public function test_duplicate_nik_is_rejected_even_when_encrypted(): void
    {
        Wbp::factory()->create([
            'nik' => '3201012000100110',
            'nik_hash' => Wbp::hashNik('3201012000100110'),
        ]);

        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertSessionHasErrors('nik');

        $this->assertSame(1, Wbp::count());
    }

    public function test_petugas_cannot_create_wbp(): void
    {
        $this->actingAs($this->petugas)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->assertSame(0, Wbp::count());
    }

    public function test_duplicate_nik_from_active_wbp_shows_friendly_message(): void
    {
        Wbp::factory()->create([
            'nik' => '3201012000100110',
            'nik_hash' => Wbp::hashNik('3201012000100110'),
        ]);

        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertSessionHasErrors(['nik' => 'NIK sudah terdaftar. Silakan gunakan NIK yang berbeda.'])
            ->assertStatus(302);
    }

    public function test_nik_from_soft_deleted_wbp_is_rejected_with_clear_message(): void
    {
        $wbp = Wbp::factory()->create([
            'nik' => '3201012000100110',
            'nik_hash' => Wbp::hashNik('3201012000100110'),
        ]);
        $wbp->delete();

        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertSessionHasErrors([
                'nik' => 'NIK tersebut pernah digunakan pada data WBP yang telah dihapus. Silakan periksa data WBP terlebih dahulu.',
            ]);
    }

    public function test_duplicate_no_register_is_rejected_with_message(): void
    {
        Wbp::factory()->create(['no_register' => 'REG-99901']);

        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertSessionHasErrors([
                'no_register' => 'Nomor register sudah digunakan. Silakan gunakan nomor register yang berbeda.',
            ]);
    }

    public function test_no_register_of_soft_deleted_wbp_does_not_cause_500(): void
    {
        $wbp = Wbp::factory()->create(['no_register' => 'REG-99901']);
        $wbp->delete();

        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload())
            ->assertSessionHasErrors([
                'no_register' => 'Nomor register sudah digunakan. Silakan gunakan nomor register yang berbeda.',
            ])
            ->assertStatus(302);

        $this->assertNotNull(Wbp::withTrashed()->find($wbp->id));
    }

    public function test_update_to_no_register_of_soft_deleted_wbp_does_not_cause_500(): void
    {
        $trashed = Wbp::factory()->create(['no_register' => 'REG-99901']);
        $trashed->delete();

        $wbp = Wbp::factory()->create(['no_register' => 'REG-99902']);

        $this->actingAs($this->admin)
            ->put(route('wbp.update', $wbp), $this->validPayload('3201012000100110'))
            ->assertSessionHasErrors([
                'no_register' => 'Nomor register sudah digunakan. Silakan gunakan nomor register yang berbeda.',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('wbp', ['id' => $wbp->id, 'no_register' => 'REG-99902']);
    }

    public function test_required_fields_show_indonesian_messages(): void
    {
        $this->actingAs($this->admin)
            ->from(route('wbp.create'))
            ->post(route('wbp.store'), [
                'nama' => '',
                'nik' => '',
                'no_register' => '',
                'blok_kamar' => '',
                'agama' => '',
                'status' => '',
            ])
            ->assertSessionHasErrors([
                'nama' => 'Nama WBP wajib diisi.',
                'nik' => 'NIK wajib diisi.',
                'no_register' => 'Nomor register wajib diisi.',
                'blok_kamar' => 'Blok/kamar wajib diisi.',
                'agama' => 'Agama wajib dipilih.',
                'status' => 'Status WBP wajib dipilih.',
            ]);
    }

    public function test_invalid_nik_format_shows_clear_message(): void
    {
        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload('12345'))
            ->assertSessionHasErrors([
                'nik' => 'NIK harus berupa angka dan memiliki format yang valid.',
            ]);
    }

    public function test_invalid_photo_format_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload() + [
                'foto' => UploadedFile::fake()->create('foto.txt', 100),
            ])
            ->assertSessionHasErrors([
                'foto' => 'Format foto tidak didukung. Gunakan JPG, JPEG, atau PNG.',
            ]);
    }

    public function test_photo_too_large_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('wbp.store'), $this->validPayload() + [
                'foto' => UploadedFile::fake()->image('foto.jpg')->size(3000),
            ])
            ->assertSessionHasErrors([
                'foto' => 'Ukuran foto terlalu besar. Silakan gunakan foto dengan ukuran yang lebih kecil.',
            ]);
    }

    public function test_admin_can_update_wbp_and_rehash_nik(): void
    {
        $wbp = Wbp::factory()->create(['nik' => '3201012000100110']);

        $this->actingAs($this->admin)
            ->put(route('wbp.update', $wbp), $this->validPayload('3201012000100199'))
            ->assertRedirect(route('wbp.index'));

        $wbp->refresh();

        $this->assertSame('3201012000100199', $wbp->nik);
        $this->assertSame(Wbp::hashNik('3201012000100199'), $wbp->nik_hash);
    }

    public function test_admin_can_soft_delete_wbp(): void
    {
        $wbp = Wbp::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('wbp.destroy', $wbp))
            ->assertRedirect(route('wbp.index'));

        $this->assertSoftDeleted('wbp', ['id' => $wbp->id]);
    }

    public function test_petugas_cannot_delete_wbp(): void
    {
        $wbp = Wbp::factory()->create();

        $this->actingAs($this->petugas)
            ->delete(route('wbp.destroy', $wbp))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('wbp', ['id' => $wbp->id]);
    }
}
