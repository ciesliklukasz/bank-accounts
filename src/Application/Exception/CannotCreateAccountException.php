<?php

declare(strict_types=1);

namespace App\Application\Exception;

class CannotCreateAccountException extends \LogicException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Account creation failed.',0, $previous);
    }
}