<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class NotFoundException extends \RuntimeException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Account with given id not found.',0, $previous);
    }
}