<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\Serializer\Serializer;

/**
 * SerializerTrait.
 */
trait SerializerTrait
{
    /** @var Serializer */
    protected $serializer;

    /**
     * @param Serializer $serializer
     *
     * @required
     */
    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }
}
