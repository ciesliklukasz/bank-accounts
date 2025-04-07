<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Account;
use Ramsey\Uuid\UuidInterface;

interface AccountRepositoryInterface
{
    public function saveAccount(Account $account): void;
    public function getAccount(UuidInterface $accountId): Account;
}