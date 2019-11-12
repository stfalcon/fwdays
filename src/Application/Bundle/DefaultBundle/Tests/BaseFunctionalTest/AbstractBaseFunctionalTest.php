<?php

namespace Application\Bundle\DefaultBundle\Tests\BaseFunctionalTest;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractBaseFunctionalTest extends WebTestCase
{
    private const BASE_HOST = '127.0.0.1:8000';

    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;
    /** @var Container */
    protected $container;

    /** set up fixtures */
    protected function setUp(): void
    {
        $this->client = static::makeClient(['HTTP_HOST' => self::BASE_HOST, 'HTTP_ACCEPT_LANGUAGE' => 'uk,en-us,en;q=0.5']);
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /** destroy */
    protected function tearDown(): void
    {
        unset(
            $this->client,
            $this->em,
            $this->container,
        );
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return Crawler
     */
    protected function requestGet(string $url, array $params = []): Crawler
    {
        $this->client->followRedirects();

        return $this->client->request(
            Request::METHOD_GET,
            $url,
            $params
        );
    }
}
