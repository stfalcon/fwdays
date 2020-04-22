<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\Review;
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
     * @var Review
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Review", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
