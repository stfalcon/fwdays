<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\PromoCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="promo_code_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="promo_code_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class PromoCodeTranslation extends AbstractTranslation
{
    /**
     * @var PromoCode
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PromoCode", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
