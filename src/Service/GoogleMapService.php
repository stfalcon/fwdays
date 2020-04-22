<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * GoogleMapService.
 */
class GoogleMapService
{
    /** @var string */
    private $googleApiKey;
    private $httpClient;

    /**
     * @param string              $googleApiKey
     * @param HttpClientInterface $httpClient
     */
    public function __construct(string $googleApiKey, HttpClientInterface $httpClient)
    {
        $this->googleApiKey = $googleApiKey;
        $this->httpClient = $httpClient;
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
        $cityName = $city instanceof City ? $city->getName() : null;
        if (\is_string($cityName)) {
            $place = $event->getPlace();
            $address = \is_string($place) ? \sprintf('%s,%s', $cityName, $place) : $cityName;
            $googlePath = \sprintf('https://maps.google.com/maps/api/geocode/json?key=%s&address=%s', $this->googleApiKey, \urlencode($address));
            $json = $this->httpClient->request(Request::METHOD_GET, $googlePath);

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
