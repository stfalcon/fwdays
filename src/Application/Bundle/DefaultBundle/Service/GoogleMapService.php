<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\Event;
use Buzz\Browser;

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
    public function setEventMapPosition($event): bool
    {
        if (!$event instanceof Event) {
            return false;
        }

        $lat = null;
        $lng = null;

        $city = $event->getCity();
        if (\is_string($city)) {
            $place = $event->getPlace();
            $address = \is_string($place) ? \sprintf('%s,%s', $city, $place) : $city;
            $googlePath = \sprintf('https://maps.google.com/maps/api/geocode/json?key=%s&address=%s', $this->googleApiKey, \urlencode($address));
            $json = $this->buzzService->get($googlePath);

            $response = \json_decode(
                $json->getContent(),
                true
            );

            if (isset($response['status']) && 'OK' === $response['status']) {
                $location = isset($response['results'][0]['geometry']['location']) ? $response['results'][0]['geometry']['location'] : null;
                if (\is_array($location)) {
                    $lat = isset($location['lat']) ? $location['lat'] : null;
                    $lng = isset($location['lng']) ? $location['lng'] : null;
                }
            }
        }

        $event
            ->setLng($lng)
            ->setLat($lat)
        ;

        return true;
    }
}
