<?php

namespace Application\Bundle\DefaultBundle\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Bundle\DefaultBundle\Entity\Translation\TranslatableEntityInterface;

/**
 * Trait Translate.
 */
trait TranslateTrait
{
    /**
     * @param TranslatableEntityInterface $translation
     */
    public function addTranslation($translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setObject($this);
        }
    }

    /**
     * @param TranslatableEntityInterface $translation
     */
    public function addTranslations($translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setObject($this);
        }
    }

    /**
     * @param TranslatableEntity $translation
     */
    public function removeTranslation($translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * @param ArrayCollection $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
