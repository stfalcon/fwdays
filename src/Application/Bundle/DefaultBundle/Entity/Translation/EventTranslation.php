<?php

namespace Application\Bundle\DefaultBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractTranslation;

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
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Event", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
