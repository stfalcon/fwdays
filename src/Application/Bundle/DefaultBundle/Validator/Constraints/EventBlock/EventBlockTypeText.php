<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\EventBlock;

use Symfony\Component\Validator\Constraint;

/**
 * Class EventBlockTypeText.
 *
 * @Annotation
 */
class EventBlockTypeText extends Constraint
{
    const TEXT_REQUIRED = 'TEXT_REQUIRED';

    /**
     * {@inheritdoc}
     */
    protected static $errorNames = [
        self::TEXT_REQUIRED => self::TEXT_REQUIRED,
    ];

    public $message = 'введите текст';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
