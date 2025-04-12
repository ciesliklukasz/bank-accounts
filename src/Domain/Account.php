<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Enum\Currency;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\DailyTransactionLimitAchievedException;
use App\Domain\Exception\InsufficientBalanceException;
use App\Domain\Exception\InvalidCurrencyException;
use Ramsey\Uuid\UuidInterface;

final class Account
{
    private const float DEBIT_COMMISSION = 0.005;
    private const int DAILY_DEBIT_LIMIT = 3;

    private Money $balance;
    /** @var AccountLog[]  */
    private array $logs;

    public function __construct(
        public readonly UuidInterface $id,
        private readonly Currency $currency,
    ) {
        $this->balance = new Money(0, $this->currency);
    }

    public function credit(Money $money): Money
    {
        $this->assertCurrency($money->currency);

        $this->registerLog(TransactionType::Credit, new \DateTimeImmutable());

        $this->balance = $this->balance->add($money);

        return $this->balance;
    }

    public function debit(Account $account, Money $amount): void
    {
        $time = new \DateTimeImmutable();

        $commission = (int) round($amount->amount * self::DEBIT_COMMISSION);
        $amount = $amount->add(new Money($commission, $amount->currency));

        $this->assertCurrency($account->currency);
        $this->assertSufficientBalance($amount);
        $this->assertDailyDebitLimit($time);

        $this->registerLog(TransactionType::Debit, $time);

        $this->balance = $this->balance->reduce($amount);
        $account->credit($amount);
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    private function assertCurrency(Currency $currency): void
    {
        if ($currency !== $this->currency) {
            throw new InvalidCurrencyException();
        }
    }

    private function assertSufficientBalance(Money $money): void
    {
        if ($money->amount > $this->balance->amount) {
            throw new InsufficientBalanceException();
        }
    }

    private function assertDailyDebitLimit(\DateTimeImmutable $dateTimeImmutable): void
    {
        $result = array_filter($this->logs, static fn (AccountLog $log) =>
            $log->transactionType === TransactionType::Debit &&
            $log->createdAt->format('Y-m-d') === $dateTimeImmutable->format('Y-m-d')
        );

        if (count($result) >= self::DAILY_DEBIT_LIMIT) {
            throw new DailyTransactionLimitAchievedException();
        }
    }

    private function registerLog(TransactionType $transactionType, \DateTimeImmutable $dateTimeImmutable): void
    {
        $this->logs[] = new AccountLog(
            $this->id,
            $transactionType,
            $dateTimeImmutable
        );
    }
}