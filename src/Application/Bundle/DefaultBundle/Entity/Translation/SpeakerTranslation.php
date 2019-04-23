<?php

namespace Application\Bundle\DefaultBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractTranslation;

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
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Speaker", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
