<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * SerializerTrait.
 */
trait SerializerTrait
{
    /** @var SerializerInterface|Serializer */
    protected $serializer;

    /**
     * @param SerializerInterface|Serializer $serializer
     *
     * @required
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }
}
