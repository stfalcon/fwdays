<?php

namespace Stfalcon\Bundle\EventBundle\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Stfalcon\Bundle\EventBundle\Entity\Translation\TranslatableEntity;

trait Translate
{
    /**
     * @param TranslatableEntity $translation
     */
    public function addTranslation($translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setObject($this);
        }
    }
    /**
     * @param TranslatableEntity $translation
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