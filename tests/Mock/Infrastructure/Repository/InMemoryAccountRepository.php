<?php

namespace App\Tests\Mock\Infrastructure\Repository;

use App\Application\Exception\NotFoundException;
use App\Application\Repository\AccountRepositoryInterface;
use App\Domain\Account;
use Ramsey\Uuid\UuidInterface;

final class InMemoryAccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        public array $accounts = [],
    ) {
    }

    public function save(Account $account): void
    {
        $this->accounts[$account->id->toString()] = $account;
    }

    public function get(UuidInterface $accountId): Account
    {
        if (array_key_exists($accountId->toString(), $this->accounts)) {
            return $this->accounts[$accountId->toString()];
        }

        throw new NotFoundException();
    }

    public function exists(UuidInterface $accountId): bool
    {
        return array_key_exists($accountId->toString(), $this->accounts);
    }
}