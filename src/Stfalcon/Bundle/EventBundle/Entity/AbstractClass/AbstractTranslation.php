<?php

namespace Stfalcon\Bundle\EventBundle\Entity\AbstractClass;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Stfalcon\Bundle\EventBundle\Entity\Translation\TranslatableEntityInterface;

/**
 * Class AbstractTranslation.
 */
abstract class AbstractTranslation extends AbstractPersonalTranslation implements TranslatableEntityInterface
{
    /**
     * Convenient constructor.
     *
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
