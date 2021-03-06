<?php

namespace App\Entity\AbstractClass;

use App\Entity\Translation\TranslatableEntityInterface;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * Class AbstractTranslation.
 */
abstract class AbstractTranslation extends AbstractPersonalTranslation implements TranslatableEntityInterface
{
    /**
     * @param string $locale  locale
     * @param string $field   field
     * @param string $content content
     */
    public function __construct($locale = null, $field = null, $content = null)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($content);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLocale();
    }
}
