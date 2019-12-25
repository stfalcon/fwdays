<?php

namespace Application\Bundle\DefaultBundle\Entity\Translation;

use Application\Bundle\DefaultBundle\Entity\AbstractClass\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;

/**
 * EmailTranslation.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="email_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="email_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class EmailTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\Bundle\DefaultBundle\Entity\Mail", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
