<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Currency;
use App\Domain\Money;
use Ramsey\Uuid\UuidInterface;

interface AccountServiceInterface
{
    public function createAccount(UuidInterface $accountId, Currency $currency): UuidInterface;

    public function depositAccount(UuidInterface $accountId, Money $money): Money;

    public function moneyTransfer(
        UuidInterface $sourceAccountId,
        UuidInterface $destinationAccountId,
        Money $money
    ): void

    ;
}