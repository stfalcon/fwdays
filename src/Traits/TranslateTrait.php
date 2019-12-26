<?php

namespace App\Traits;

use App\Entity\AbstractClass\AbstractTranslation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait Translate.
 */
trait TranslateTrait
{
    /**
     * @param AbstractTranslation $translation
     */
    public function addTranslation($translation): void
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setObject($this);
        }
    }

    /**
     * @param AbstractTranslation $translation
     */
    public function removeTranslation($translation): void
    {
        $this->translations->removeElement($translation);
    }

    /**
     * @param ArrayCollection $translations
     */
    public function setTranslations($translations): void
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
