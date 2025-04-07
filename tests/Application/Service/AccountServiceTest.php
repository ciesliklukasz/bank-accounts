<?php

declare(strict_types=1);

namespace App\tests\Application\Service;

use App\Application\Exception\NotFoundException;
use App\Application\Service\AccountService;
use App\Domain\Account;
use App\Domain\Currency;
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

    public function testDepositAccount(): void
    {
        $accountId = Uuid::uuid4();
        $currency = Currency::Pln;

        $service = new AccountService(
            new InMemoryAccountRepository([
                $accountId->toString() => new Account(
                    $accountId,
                    $currency,
                    (int) floor(microtime(true) * 1000)
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
                    (int) floor(microtime(true) * 1000)
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

    public function testMoneyTransferBetweenAccountsWithSameCurrency(): void
    {
        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();
        $currency = Currency::Eur;

        $sourceAccount = new Account(
            $sourceAccountId,
            $currency,
            (int) floor(microtime(true) * 1000)
        );
        $sourceAccount->deposit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            $currency,
            (int) floor(microtime(true) * 1000)
        );
        $destinationAccount->deposit(new Money(1000, Currency::Eur));

        $accountRepository = new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]);
        $service = new AccountService($accountRepository);

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(300, Currency::Eur));

        $this->assertEquals(700, $accountRepository->getAccount($sourceAccountId)->balance->amount);
        $this->assertEquals(1300, $accountRepository->getAccount($destinationAccountId)->balance->amount);
    }

    public function testMoneyTransferBetweenAccountWithDifferentCurrencies(): void
    {
        $this->expectException(InvalidCurrencyException::class);

        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();

        $sourceAccount = new Account(
            $sourceAccountId,
            Currency::Eur,
            (int) floor(microtime(true) * 1000)
        );
        $sourceAccount->deposit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            Currency::Pln,
            (int) floor(microtime(true) * 1000)
        );
        $destinationAccount->deposit(new Money(1000, Currency::Pln));

        $service = new AccountService(new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]));

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(300, Currency::Eur));
    }

    public function testMoneyTransferBetweenAccountWithInsufficientBalance(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $sourceAccountId = Uuid::uuid4();
        $destinationAccountId = Uuid::uuid4();

        $sourceAccount = new Account(
            $sourceAccountId,
            Currency::Eur,
            (int) floor(microtime(true) * 1000)
        );
        $sourceAccount->deposit(new Money(1000, Currency::Eur));

        $destinationAccount = new Account(
            $destinationAccountId,
            Currency::Eur,
            (int) floor(microtime(true) * 1000)
        );
        $destinationAccount->deposit(new Money(1000, Currency::Eur));

        $service = new AccountService(new InMemoryAccountRepository([
            $sourceAccountId->toString() => $sourceAccount,
            $destinationAccountId->toString() => $destinationAccount,
        ]));

        $service->moneyTransfer($sourceAccountId, $destinationAccountId, new Money(2000, Currency::Eur));
    }
}
