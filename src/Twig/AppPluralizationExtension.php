<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppPluralizationExtension.
 */
class AppPluralizationExtension extends AbstractExtension
{
    /**
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('pluralization', [$this, 'pluralization']),
        ];
    }

    /**
     * @param int $number
     *
     * @return int
     */
    public function pluralization($number)
    {
        return ((1 === $number % 10) && (11 !== $number % 100))
            ? 0
            : ((($number % 10 >= 2)
                && ($number % 10 <= 4)
                && (($number % 100 < 10)
                    || ($number % 100 >= 20)))
                ? 1
                : 2
            );
    }
}
