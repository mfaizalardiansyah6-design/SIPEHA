<?php

namespace Tests\Feature;

use App\Enums\WbpStatus;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
