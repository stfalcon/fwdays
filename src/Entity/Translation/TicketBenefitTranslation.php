<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\TicketBenefit;
use Doctrine\ORM\Mapping as ORM;

/**
 * TicketBenefitTranslation.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="ticket_benefit_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="benefit_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class TicketBenefitTranslation extends AbstractTranslation
{
    /**
     * @var TicketBenefit
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TicketBenefit", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
