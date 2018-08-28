<?php

namespace Application\Bundle\DefaultBundle\Service;

/**
 * Class GetSonataLocalsRequiredService.
 */
class GetSonataLocalsRequiredService
{
    private $defaultLocale;
    private $locales;

    /**
     * GetSonataLocalsRequiredService constructor.
     *
     * @param string $defaultLocale
     * @param array  $locales
     */
    public function __construct($defaultLocale, array $locales)
    {
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param bool|null $setAllAs
     *
     * @return array
     */
    public function getLocalsRequredArray($setAllAs = null)
    {
        $result = [];
        foreach ($this->locales as $locale) {
            $result[$locale] = ['required' => (null === $setAllAs) ? $locale === $this->defaultLocale : $setAllAs];
        }

        return $result;
    }
}
