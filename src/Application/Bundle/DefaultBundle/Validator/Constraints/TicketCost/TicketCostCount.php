<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\TicketCost;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketCostCount.
 *
 * @Annotation
 */
class TicketCostCount extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public $message = '';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
