<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Account;
use App\Domain\Currency;
use App\Domain\Money;
use App\Infrastructure\Repository\AccountRepositoryInterface;
use App\Infrastructure\Service\AccountServiceInterface;
use Ramsey\Uuid\UuidInterface;

final readonly class AccountService implements AccountServiceInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
    ) {
    }

    public function createAccount(UuidInterface $accountId, Currency $currency): UuidInterface
    {
        $time = floor(microtime(true) * 1000);

        $account = new Account($accountId, $currency, (int) $time);
        $this->accountRepository->saveAccount($account);

        return $account->id;
    }

    public function depositAccount(UuidInterface $accountId, Money $money): Money
    {
        $account = $this->accountRepository->getAccount($accountId);

        $account->deposit($money);

        $this->accountRepository->saveAccount($account);

        return $account->balance;
    }

    public function moneyTransfer(UuidInterface $sourceAccountId, UuidInterface $destinationAccountId, Money $money): void
    {
        $sourceAccount = $this->accountRepository->getAccount($sourceAccountId);
        $destinationAccount = $this->accountRepository->getAccount($destinationAccountId);

        $sourceAccount->transfer($destinationAccount, $money);

        $this->accountRepository->saveAccount($sourceAccount);
        $this->accountRepository->saveAccount($destinationAccount);
    }
}