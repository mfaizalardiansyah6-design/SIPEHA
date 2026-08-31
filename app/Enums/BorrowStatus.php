<?php

namespace App\Enums;

enum BorrowStatus: string
{
    case Dipinjam = 'Dipinjam';
    case Dikembalikan = 'Dikembalikan';

    public function label(): string
    {
        return $this->value;
    }
}
