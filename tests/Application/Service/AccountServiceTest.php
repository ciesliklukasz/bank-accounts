<?php

declare(strict_types=1);

namespace App\Tests\Application\Service;

use App\Application\Exception\CannotCreateAccountException;
use App\Application\Exception\NotFoundException;
use App\Application\Service\AccountService;
use App\Domain\Account;
use App\Domain\Currency;
use App\Domain\Exception\DailyTransactionLimitAchievedException;
use App\Domain\Exception\InvalidCurrencyException;
use App\Domain\Exception\InsufficientBalanceException;
use App\Domain\Money;
use App\Tests\Mock\Infrastructure\Repository\InMemoryAccountRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AccountServiceTest extends TestCase
{
    public function testCreateAccount(): void
    {
        $accountId = Uuid::uuid4();
        $currency = Currency::Pln;

        $service = new AccountService(
            new InMemoryAccountRepository()
        );

        $this->assertEquals($accountId, $service->createAccount($accountId, $currency));
    }

    public function testCreateAccountFailedBecauseOfExistingSame(): void
    {
        $this->expectException(CannotCreateAccountException::class);

        $accountId = Uuid::uuid4();
        $currency = Currency::Pln;

        $service = new AccountService(
            new InMemoryAccountRepository([
                $accountId->toString() => new Account($accountId, $currency)
            ])
        );

        $service->createAccount($accountId, $currency);
    }

    public function testDepositAccount(): void
    {
        $accountId = Uuid::uuid4();
        $currency = Currency::Pln;

        $service = new AccountService(
            new InMemoryAccountRepository([
                $accountId->toString() => new Account(
                    $accountId,
                    $currency,
                )
            ])
        );

        $this->assertEquals(
            1000,
            $service->depositAccount($accountId, new Money(1000, Currency::Pln))->amount
        );
    }

    public function testDepositFailedWithNotMatchingCurrency(): void
    {
        $this->expectException(InvalidCurrencyException::class);

        $accountId = Uuid::uuid4();
        $currency = Currency::Eur;

        $service = new AccountService(
            new InMemoryAccountRepository([
                $accountId->toString() => new Account(
                    $accountId,
                    $currency,
                )
            ])
        );

        $service->depositAccount($accountId, new Money(1000, Currency::Pln));
    }

    public function testDepositNoExistingAccount(): void
    {
        $this->expectException(NotFoundException::class);

        $accountId = Uuid::uuid4();

        $service = new AccountService(
            new InMemoryAccountRepository()
        );

        $service->depositAccount($accountId, new Money(1000, Currency::Pln));
    }

    public function testMoneyTransferWithSameCurrency(): void
    {
        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();
        $currency = Currency::Eur;

        $sourceAccount = new Account(
            $sourceAccountId,
            $currency,
        );
        $sourceAccount->credit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            $currency,
        );
        $destinationAccount->credit(new Money(1000, Currency::Eur));

        $accountRepository = new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]);
        $service = new AccountService($accountRepository);

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(300, Currency::Eur));

        $this->assertEquals(698, $accountRepository->get($sourceAccountId)->getBalance()->amount);
        $this->assertEquals(1302, $accountRepository->get($destinationAccountId)->getBalance()->amount);
    }

    public function testMoneyTransferFailedBecauseOfDifferentCurrencies(): void
    {
        $this->expectException(InvalidCurrencyException::class);

        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();

        $sourceAccount = new Account(
            $sourceAccountId,
            Currency::Eur,
        );
        $sourceAccount->credit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            Currency::Pln,
        );
        $destinationAccount->credit(new Money(1000, Currency::Pln));

        $service = new AccountService(new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]));

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(300, Currency::Eur));
    }

    public function testMoneyTransferFailedBecauseOfInsufficientBalance(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();

        $sourceAccount = new Account(
            $sourceAccountId,
            Currency::Eur,
        );
        $sourceAccount->credit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            Currency::Eur,
        );
        $destinationAccount->credit(new Money(1000, Currency::Eur));

        $service = new AccountService(new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]));

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(1000, Currency::Eur));
    }

    public function testMoneyTransferFailedBecauseOfAchievedDailyTransactionsLimit(): void
    {
        $this->expectException(DailyTransactionLimitAchievedException::class);

        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();

        $sourceAccount = new Account(
            $sourceAccountId,
            Currency::Eur,
        );
        $sourceAccount->credit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            Currency::Eur,
        );
        $destinationAccount->credit(new Money(1000, Currency::Eur));

        $service = new AccountService(new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]));

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(100, Currency::Eur));
        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(100, Currency::Eur));
        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(100, Currency::Eur));
        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(100, Currency::Eur));
    }
}
