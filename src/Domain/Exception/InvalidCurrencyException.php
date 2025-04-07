<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class InvalidCurrencyException extends \DomainException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Currency not matched.',0, $previous);
    }
}