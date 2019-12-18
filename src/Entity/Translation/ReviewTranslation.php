<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="review_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="review_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class ReviewTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Review", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
