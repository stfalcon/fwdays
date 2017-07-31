<?php

namespace Stfalcon\Bundle\EventBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractTranslation;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="speaker_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="speaker_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class SpeakerTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\Speaker", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
