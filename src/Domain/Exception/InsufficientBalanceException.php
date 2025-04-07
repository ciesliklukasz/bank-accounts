<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class InsufficientBalanceException extends \DomainException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Insufficient balance to make transaction.',0, $previous);
    }
}