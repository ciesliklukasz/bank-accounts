<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Application\Exception\NotFoundException;
use App\Domain\Account;
use Ramsey\Uuid\UuidInterface;

interface AccountRepositoryInterface
{
    public function save(Account $account): void;

    /**
     * @throws NotFoundException
     */
    public function get(UuidInterface $accountId): Account;

    public function exists(UuidInterface $accountId): bool;
}