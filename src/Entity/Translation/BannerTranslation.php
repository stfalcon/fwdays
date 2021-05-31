<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\Banner;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="banner_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="banner_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class BannerTranslation extends AbstractTranslation
{
    /**
     * @var Banner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Banner", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
