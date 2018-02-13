<?php

namespace Application\Bundle\DefaultBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use Buzz\Message\RequestInterface;

/**
 * Class GACommerce
 */
class GACommerce
{
    /**
     * @var string $url URL
     */
    private $url = 'http://www.google-analytics.com/collect';

    /**
     * @var int $version Version
     */
    private $version = 1;

    /**
     * @var string $currencyCode Currency code
     */
    private $currencyCode = 'UAH';

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param string $cid
     * @param string $trnId
     * @param int    $trnRevenue
     * @param string $evenName
     *
     * @return \Buzz\Message\MessageInterface
     *
     * @throws \Exception
     */
    public function sendTransaction($cid, $trnId, $trnRevenue, $evenName)
    {
        /*
         * Documentation by params:
         *
         * https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=ru
         */

        $params = [
            'v'   => $this->version,                                    // Version.
            'tid' => $this->container->getParameter('tracking_id'),     // Tracking ID / Web property / Property ID.
            'cid' => $cid,                                              // Anonymous Client ID.
            't'   => 'transaction',                                     // Transaction hit type.
            'ti'  => $trnId,                                            // transaction ID. Required.
            'ta'  => 'Frameworks Days',                                 // Transaction affiliation.
            'tr'  => $trnRevenue,                                       // Transaction revenue.
            'cu'  => $this->currencyCode,                               // Currency code.
            'iv'  => $evenName,                                         // Item variation / category.
        ];

        return $this->container->get('buzz')->submit(
            $this->url,
            $params,
            RequestInterface::METHOD_POST
        );
    }

    /**
     * @param string  $cid
     * @param string  $trnId
     * @param string  $iName
     * @param int     $iPrice
     * @param string  $priceBlockName
     * @param string  $evenName
     *
     * @return \Buzz\Message\MessageInterface
     *
     * @throws \Exception
     */
    public function sendItem($cid, $trnId, $iName, $iPrice, $evenName, $priceBlockName)
    {
        /*
         * Documentation by params:
         *
         * https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=ru
         */

        $params = [
            'v'   => $this->version,                                    // Version.
            'tid' => $this->container->getParameter('tracking_id'),     // Tracking ID / Web property / Property ID.
            'cid' => $cid,                                              // Anonymous Client ID.
            't'   => 'item',
            'ti'  => $trnId,                                            // transaction ID. Required.
            'in'  => $iName,                                            // Item name. Required.
            'ip'  => $iPrice,                                           // Item price.
            'iq'  => 1,                                                 // Item quantity.
            'cu'  => $this->currencyCode,                               // Currency code.
            'iv'  => $evenName,                                         // Item variation / category.
        ];

        return $this->container->get('buzz')->submit(
            $this->url,
            $params,
            RequestInterface::METHOD_POST
        );
    }
}
