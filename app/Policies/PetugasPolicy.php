<?php

namespace App\Policies;

use App\Models\User;

class PetugasPolicy
{
    /**
     * Menentukan apakah user dapat membuat petugas baru.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah user dapat mengubah petugas.
     */
    public function update(User $user, User $petugas): bool
    {
        return $user->isAdmin() && $petugas->id !== $user->id;
    }

    /**
     * Menentukan apakah user dapat menghapus petugas.
     */
    public function delete(User $user, User $petugas): bool
    {
        return $user->isAdmin()
            && $petugas->id !== $user->id
            && ! $petugas->isAdmin();
    }
}
