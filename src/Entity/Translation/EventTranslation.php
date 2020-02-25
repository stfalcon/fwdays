<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="event_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="event_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class EventTranslation extends AbstractTranslation
{
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
