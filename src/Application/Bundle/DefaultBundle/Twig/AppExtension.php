<?php

namespace Application\Bundle\DefaultBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('pluralization', array($this, 'pluralization')),
        );
    }

    public function pluralization($number)
    {
        return (($number % 10 == 1) && ($number % 100 != 11))
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