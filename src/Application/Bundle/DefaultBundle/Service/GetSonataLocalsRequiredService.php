<?php

namespace Application\Bundle\DefaultBundle\Service;

class GetSonataLocalsRequiredService
{

    private $defaultLocale;
    private $locales;

    public function __construct($defaultLocale, array $locales)
    {
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    public function getLocalsRequredArray ($setAllAs = null)
    {
        $result = [];
        foreach ($this->locales as $locale) {
            $result[$locale] = ['required' => (null === $setAllAs) ? $locale === $this->defaultLocale : $setAllAs];
        }

        return $result;
    }
}