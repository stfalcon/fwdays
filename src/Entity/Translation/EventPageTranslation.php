<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use App\Entity\EventPage;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventPageTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="event_page_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="event_page_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class EventPageTranslation extends AbstractTranslation
{
    /**
     * @var EventPage
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventPage", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
