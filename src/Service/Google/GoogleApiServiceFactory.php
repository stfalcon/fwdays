<?php

declare(strict_types=1);

namespace App\Service\Google;

/**
 * GoogleApiServiceFactory.
 */
class GoogleApiServiceFactory
{
    private $client;

    /**
     * @param \Google_Client $client
     */
    public function __construct(\Google_Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return \Google_Service_Calendar
     */
    public function createCalendar(): \Google_Service_Calendar
    {
        $this->client->setScopes([\Google_Service_Calendar::CALENDAR]);

        return new \Google_Service_Calendar($this->client);
    }
}
