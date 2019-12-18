<?php

namespace App\Service;

/**
 * LocalsRequiredService.
 */
class LocalsRequiredService
{
    public const DEFAULT_EMAIL_LANGUAGE = 'uk';

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
    public function getLocalsRequiredArray($setAllAs = null)
    {
        $result = [];
        foreach ($this->locales as $locale) {
            $result[$locale] = ['required' => (null === $setAllAs) ? $locale === $this->defaultLocale : $setAllAs];
        }

        return $result;
    }
}
