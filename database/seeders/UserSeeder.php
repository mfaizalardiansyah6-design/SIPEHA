<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@simhak.test'],
            [
                'name' => 'Administrator',
                'nip' => 'admin',
                'jabatan' => 'Kepala Lembaga',
                'role' => UserRole::Admin,
                'password' => 'password',
            ],
        );

        $petugas = [
            ['nip' => '198701012010011001', 'name' => 'Budi Santoso', 'jabatan' => 'Petugas Registrasi'],
            ['nip' => '198702022010011002', 'name' => 'Siti Rahayu', 'jabatan' => 'Petugas Bimkemas'],
            ['nip' => '198703032010011003', 'name' => 'Agus Salim', 'jabatan' => 'Petugas Kamtib'],
            ['nip' => '198704042010011004', 'name' => 'Dewi Lestari', 'jabatan' => 'Petugas Kesehatan'],
            ['nip' => '198705052010011005', 'name' => 'Joko Widodo', 'jabatan' => 'Petugas Registrasi'],
            ['nip' => '198706062010011006', 'name' => 'Rina Marlina', 'jabatan' => 'Petugas Bimkemas'],
            ['nip' => '198707072010011007', 'name' => 'Hendra Gunawan', 'jabatan' => 'Petugas Kamtib'],
            ['nip' => '198708082010011008', 'name' => 'Fitri Handayani', 'jabatan' => 'Petugas Kesehatan'],
            ['nip' => '198709092010011009', 'name' => 'Rudi Hartono', 'jabatan' => 'Petugas Registrasi'],
            ['nip' => '198710102010011010', 'name' => 'Maya Anggraini', 'jabatan' => 'Petugas Bimkemas'],
        ];

        foreach ($petugas as $p) {
            User::updateOrCreate(
                ['nip' => $p['nip']],
                [
                    'name' => $p['name'],
                    'email' => strtolower(str_replace(' ', '', $p['name'])).'@simhak.test',
                    'jabatan' => $p['jabatan'],
                    'role' => UserRole::User,
                    'password' => 'password',
                ],
            );
        }
    }
}
