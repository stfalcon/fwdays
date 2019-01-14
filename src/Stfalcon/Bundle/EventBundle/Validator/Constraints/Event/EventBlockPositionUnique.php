<?php

namespace Stfalcon\Bundle\EventBundle\Validator\Constraints\Event;

use Symfony\Component\Validator\Constraint;

/**
 * EventBlockPositionUnique.
 *
 * @Annotation
 */
class EventBlockPositionUnique extends Constraint
{
    public $message = 'эта позиция уже используется';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
