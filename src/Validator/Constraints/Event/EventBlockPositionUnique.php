<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Event;

use Symfony\Component\Validator\Constraint;

/**
 * EventBlockPositionUnique.
 *
 * @Annotation
 */
class EventBlockPositionUnique extends Constraint
{
    /** @var string */
    public $message = 'эта позиция уже используется';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
