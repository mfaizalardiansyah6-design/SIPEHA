<?php

namespace App\Enums;

enum WbpStatus: string
{
    case Aktif = 'Aktif';
    case NonAktif = 'Non-Aktif';

    public function label(): string
    {
        return $this->value;
    }
}
