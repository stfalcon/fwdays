<?php

namespace Stfalcon\Bundle\EventBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Stfalcon\Bundle\EventBundle\Entity\AbstractClass\AbstractTranslation;

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
     * @ORM\ManyToOne(targetEntity="Stfalcon\Bundle\EventBundle\Entity\EventBlock", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
