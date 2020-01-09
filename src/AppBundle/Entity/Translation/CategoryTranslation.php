<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="category_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="category_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}