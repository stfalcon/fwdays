<?php

namespace Stfalcon\Bundle\EventBundle\Twig;

/**
 * Class MonthNameExtension.
 */
class MonthNameExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('month_name', array($this, 'monthNameFilter')),
        );
    }

    /**
     * @param int $value
     *
     * @return string|int
     */
    public function monthNameFilter($value)
    {
        switch ($value) {
            case 1: return 'январь';
            break;
            case 2: return 'февраль';
            break;
            case 3: return 'март';
            break;
            case 4: return 'апрель';
            break;
            case 5: return 'май';
            break;
            case 6: return 'июнь';
            break;
            case 7: return 'июль';
            break;
            case 8: return 'август';
            break;
            case 9: return 'сентябрь';
            break;
            case 10: return 'октябрь';
            break;
            case 11: return 'ноябрь';
            break;
            case 12: return 'декабрь';
            break;
            default: return $value;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'month_name_extension';
    }
}
