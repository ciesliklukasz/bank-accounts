<?php

namespace App\Tests\Mock\Infrastructure\Repository;

use App\Application\Exception\NotFoundException;
use App\Domain\Account;
use App\Infrastructure\Repository\AccountRepositoryInterface;
use Ramsey\Uuid\UuidInterface;

final class InMemoryAccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        public array $accounts = [],
    ) {
    }

    public function saveAccount(Account $account): void
    {
        $this->accounts[$account->id->toString()] = $account;
    }

    public function getAccount(UuidInterface $accountId): Account
    {
        if (array_key_exists($accountId->toString(), $this->accounts)) {
            return $this->accounts[$accountId->toString()];
        }

        throw new NotFoundException('Account not found ' .

            $accountId);
    }
}