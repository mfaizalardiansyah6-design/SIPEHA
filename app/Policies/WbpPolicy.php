<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wbp;

class WbpPolicy
{
    /**
     * Menentukan apakah user dapat membuat data WBP baru.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah user dapat mengubah data WBP.
     */
    public function update(User $user, Wbp $wbp): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah user dapat menghapus data WBP.
     */
    public function delete(User $user, Wbp $wbp): bool
    {
        return $user->isAdmin();
    }
}
