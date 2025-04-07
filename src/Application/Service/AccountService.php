<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Exception\CannotCreateAccountException;
use App\Application\Repository\AccountRepositoryInterface;
use App\Domain\Account;
use App\Domain\Currency;
use App\Domain\Money;
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
        if ($this->accountRepository->exists($accountId)) {
            throw new CannotCreateAccountException();
        }

        $account = new Account($accountId, $currency);
        $this->accountRepository->save($account);

        return $account->id;
    }

    public function depositAccount(UuidInterface $accountId, Money $money): Money
    {
        $account = $this->accountRepository->get($accountId);

        $account->credit($money);

        $this->accountRepository->save($account);

        return $account->getBalance();
    }

    public function moneyTransfer(
        UuidInterface $sourceAccountId,
        UuidInterface $destinationAccountId,
        Money $money
    ): void
    {
        $sourceAccount = $this->accountRepository->get($sourceAccountId);
        $destinationAccount = $this->accountRepository->get($destinationAccountId);

        $sourceAccount->debit($destinationAccount, $money);

        $this->accountRepository->save($sourceAccount);
        $this->accountRepository->save($destinationAccount);
    }
}