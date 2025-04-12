<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Enum\TransactionType;
use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;

final readonly class AccountLog
{
    public function __construct(
        public UuidInterface $accountId,
        public TransactionType $transactionType,
        public DateTimeInterface $createdAt,
    ) {
    }
}