<?php

declare(strict_types=1);

namespace App\Exception\Console;

/**
 * InvalidParameterException.
 */
class InvalidParameterException extends \InvalidArgumentException implements CustomConsoleExceptionInterface
{
}
