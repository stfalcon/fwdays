<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\Event;

use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EventDateValidator.
 */
class EventDateValidator extends ConstraintValidator
{
    /**
     * @param Event|null $event
     * @param Constraint $constraint
     */
    public function validate($event, Constraint $constraint)
    {
        if ($event instanceof Event) {
            if (!$event->getDate() && !$event->getApproximateDate()) {
                $this->context
                    ->addViolation(
                        sprintf(
                            'Повинна бути заповнена хоча б одна дата події у відповідному полі!'
                        )
                    );

                return;
            }
        }
    }
}
