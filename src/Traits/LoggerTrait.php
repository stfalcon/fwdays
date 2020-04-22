<?php

declare(strict_types=1);

namespace App\Traits;

use Monolog\Logger;

/**
 * LoggerTrait.
 */
trait LoggerTrait
{
    /** @var Logger */
    protected $logger;

    /**
     * @param Logger $logger
     *
     * @required
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }
}
