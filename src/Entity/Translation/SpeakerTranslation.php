<?php

namespace App\Entity\Translation;

use App\Entity\AbstractClass\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventTranslation entity.
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="speaker_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="speaker_lookup_unique_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class SpeakerTranslation extends AbstractTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Speaker", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
