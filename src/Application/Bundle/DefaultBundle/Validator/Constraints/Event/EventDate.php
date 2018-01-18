<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\Event;

use Symfony\Component\Validator\Constraint;

/**
 * Class EventDate.
 *
 * @Annotation
 */
class EventDate extends Constraint
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
