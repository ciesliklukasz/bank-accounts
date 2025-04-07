<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use JetBrains\PhpStorm\Pure;

class DailyTransactionLimitAchievedException extends \DomainException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Daily transaction limit achieved.',0, $previous);
    }
}