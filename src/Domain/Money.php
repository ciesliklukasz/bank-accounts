<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Enum\Currency;
use App\Domain\Exception\InvalidCurrencyException;

final readonly class Money
{
    public function __construct(
        public int $amount,
        public Currency $currency,
    ) {
    }

    public function add(Money $money): Money
    {
        $this->assertCurrency($money);

        return new Money(
            $this->amount + $money->amount,
            $money->currency
        );
    }

    public function reduce(Money $money): Money
    {
        $this->assertCurrency($money);

        return new Money(
            $this->amount - $money->amount,
            $money->currency
        );
    }

    private function assertCurrency(Money $money): void
    {
        if ($money->currency !== $this->currency) {
            throw new InvalidCurrencyException();
        }
    }
}