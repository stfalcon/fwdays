<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HttpClientTrait.
 */
trait HttpClientTrait
{
    /** @var HttpClientInterface */
    protected $httpClient;

    /**
     * @param HttpClientInterface $httpClient
     *
     * @required
     */
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
