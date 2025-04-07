<?php

declare(strict_types=1);

namespace App\Domain;

final class Money
{
    public function __construct(
        public int $amount,
        public Currency $currency,
    ) {
    }

    public function add(Money $money): Money
    {
        $this->amount += $money->amount;

        return $this;
    }

    public function reduce(Money $money): Money
    {
        $this->amount -= $money->amount;

        return $this;
    }
}