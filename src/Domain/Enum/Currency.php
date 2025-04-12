<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum Currency: string
{
    case Pln = 'PLN';
    case Eur = 'EUR';
}
