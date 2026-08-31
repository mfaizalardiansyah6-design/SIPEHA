<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetugasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_index_menampilkan_semua_role_dengan_badge(): void
    {
        User::factory()->admin()->create(['name' => 'Admin Kedua']);
        User::factory()->create(['name' => 'Petugas Satu']);

        $response = $this->actingAs($this->admin)->get(route('petugas.index'));

        $response->assertOk()
            ->assertSee('Admin Kedua')
            ->assertSee('Petugas Satu')
            ->assertSee('Administrator')
            ->assertSee('Petugas');
    }

    public function test_index_dapat_difilter_per_role(): void
    {
        User::factory()->admin()->create(['name' => 'Admin Kedua']);
        User::factory()->create(['name' => 'Petugas Satu']);

        $this->actingAs($this->admin)
            ->get(route('petugas.index', ['role' => UserRole::Admin->value]))
            ->assertOk()
            ->assertSee('Admin Kedua')
            ->assertDontSee('Petugas Satu');
    }

    public function test_admin_dapat_membuat_petugas_berperan_user(): void
    {
        $this->actingAs($this->admin)->post(route('petugas.store'), [
            'name' => 'Petugas Baru',
            'nip' => '199001012024011001',
            'email' => 'petugas.baru@simhak.test',
            'jabatan' => 'Sipir',
            'role' => UserRole::User->value,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('petugas.index'));

        $this->assertDatabaseHas('users', [
            'nip' => '199001012024011001',
            'role' => UserRole::User->value,
        ]);
    }

    public function test_admin_dapat_membuat_akun_admin_baru(): void
    {
        $this->actingAs($this->admin)->post(route('petugas.store'), [
            'name' => 'Admin Baru',
            'nip' => '199002012024011001',
            'email' => 'admin.baru@simhak.test',
            'jabatan' => 'Kepala',
            'role' => UserRole::Admin->value,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('petugas.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin.baru@simhak.test',
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_role_wajib_diisi_saat_membuat(): void
    {
        $payload = [
            'name' => 'Tanpa Peran',
            'nip' => '199003012024011001',
            'email' => 'tanpa.peran@simhak.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->actingAs($this->admin)
            ->post(route('petugas.store'), $payload)
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'tanpa.peran@simhak.test']);
    }

    public function test_admin_dapat_mengubah_role_petugas_menjadi_admin(): void
    {
        $petugas = User::factory()->create();

        $this->actingAs($this->admin)->put(route('petugas.update', $petugas), [
            'name' => $petugas->name,
            'nip' => $petugas->nip,
            'email' => $petugas->email,
            'jabatan' => $petugas->jabatan,
            'role' => UserRole::Admin->value,
        ])->assertRedirect(route('petugas.index'));

        $this->assertSame(UserRole::Admin, $petugas->refresh()->role);
    }
}
