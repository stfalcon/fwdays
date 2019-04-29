<?php

namespace Application\Bundle\DefaultBundle\Service;

use Buzz\Browser;
use Application\Bundle\DefaultBundle\Entity\Event;

/**
 * Class GoogleMapService.
 */
class GoogleMapService
{
    /** @var string */
    private $googleApiKey;

    /** @var Browser */
    private $buzzService;

    /**
     * GoogleMapService constructor.
     *
     * @param string  $googleApiKey
     * @param Browser $buzzService
     */
    public function __construct($googleApiKey, Browser $buzzService)
    {
        $this->googleApiKey = $googleApiKey;
        $this->buzzService = $buzzService;
    }

    /**
     * Set branch map position.
     *
     * @param Event $event
     *
     * @return bool
     */
    public function setEventMapPosition($event)
    {
        if (!$event instanceof Event) {
            return false;
        }

        $lat = null;
        $lng = null;
        $address = $event->getCity().','.$event->getPlace();
        $json = $this->buzzService->get(
            'https://maps.google.com/maps/api/geocode/json?key='.$this->googleApiKey.'&address='.urlencode($address)
        );

        $response = json_decode(
            $json->getContent(),
            true
        );

        if (isset($response['status']) && 'OK' === $response['status']) {
            $lat = isset($response['results'][0]['geometry']['location']['lat']) ? $response['results'][0]['geometry']['location']['lat'] : null;
            $lng = isset($response['results'][0]['geometry']['location']['lng']) ? $response['results'][0]['geometry']['location']['lng'] : null;
        }

        $event
            ->setLng($lng)
            ->setLat($lat)
        ;

        return true;
    }
}
