<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\EventBlock;
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
     * @var EventBlock
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventBlock", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
