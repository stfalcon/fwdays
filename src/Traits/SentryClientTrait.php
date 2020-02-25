<?php

declare(strict_types=1);

namespace App\Traits;

use Sentry\SentryBundle\SentrySymfonyClient;

/**
 * SentryClientTrait.
 */
trait SentryClientTrait
{
    /** @var SentrySymfonyClient */
    protected $sentryClient;

    /**
     * @param SentrySymfonyClient $sentryClient
     *
     * @required
     */
    public function setSentryClient(SentrySymfonyClient $sentryClient): void
    {
        $this->sentryClient = $sentryClient;
    }
}
