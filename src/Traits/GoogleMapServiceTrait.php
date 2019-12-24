<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\GoogleMapService;

/**
 * LocalsRequiredTrait.
 */
trait GoogleMapServiceTrait
{
    /** @var GoogleMapService */
    protected $googleMap;

    /**
     * @param GoogleMapService $googleMap
     *
     * @required
     */
    public function setGoogleMap(GoogleMapService $googleMap): void
    {
        $this->googleMap = $googleMap;
    }
}
