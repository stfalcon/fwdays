<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

/**
 * CityTranslation.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="city_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="city_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class CityTranslation extends AbstractTranslation
{
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
