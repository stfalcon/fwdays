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
     * @param string $locale
     * @param array  $locales
     */
    public function __construct(string $locale, array $locales)
    {
        $this->locales = $locales;
        $this->defaultLocale = $locale;
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
