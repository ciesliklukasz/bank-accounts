<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum TransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
}