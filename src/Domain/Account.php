<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exception\InvalidCurrencyException;
use App\Domain\Exception\InsufficientBalanceException;
use Ramsey\Uuid\UuidInterface;

final readonly class Account
{
    public Money $balance;

    public function __construct(
        public UuidInterface $id,
        private Currency $currency,
        public int $timestamp,
    ) {
        $this->balance = new Money(0, $this->currency);
    }

    public function deposit(Money $money): Money
    {
        $this->assertCurrency($money->currency);

        return $this->balance->add($money);
    }

    public function transfer(Account $account, Money $amount): void
    {
        $this->assertCurrency($account->currency);
        $this->assertSufficientBalance($amount);

        $this->balance->reduce($amount);
        $account->balance->add($amount);
    }

    private function assertCurrency(Currency $currency): void
    {
        if ($currency !== $this->currency) {
            throw new InvalidCurrencyException('Currency does not match');
        }
    }

    private function assertSufficientBalance(Money $money): void
    {
        if ($money->amount > $this->balance->amount) {
            throw new InsufficientBalanceException('Balance is to low to make a transfer');
        }
    }
}