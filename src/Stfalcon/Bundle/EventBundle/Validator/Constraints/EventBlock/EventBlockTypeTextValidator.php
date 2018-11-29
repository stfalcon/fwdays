<?php

namespace Stfalcon\Bundle\EventBundle\Validator\Constraints\EventBlock;

use Stfalcon\Bundle\EventBundle\Entity\EventBlock;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * ExchangeCurrenciesPairValidator.
 */
class EventBlockTypeTextValidator extends ConstraintValidator
{
    /**
     * @param EventBlock $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof EventBlockTypeText) {
            throw new \RuntimeException(\sprintf(
                'Object of class %s is not instance of %s',
                \get_class($constraint),
                'EventBlockTypeText'
            ));
        }

        if ($entity instanceof EventBlock) {
            if ($entity->getType() === EventBlock::HTML_TEXT && (null === $entity->getText() || '' === $entity->getText())) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath('text')
                    ->setCode(EventBlockTypeText::TEXT_REQUIRED)
                    ->addViolation();
            }
        }
    }
}
