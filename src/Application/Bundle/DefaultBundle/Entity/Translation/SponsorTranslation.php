<?php

namespace Application\Bundle\DefaultBundle\Entity\Translation;

use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="sponsor_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="sponsor_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class SponsorTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Sponsor", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
