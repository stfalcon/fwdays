<?php

namespace Application\Bundle\DefaultBundle\Entity\Translation;

use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="block_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="block_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class BlockTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\EventBlock", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
