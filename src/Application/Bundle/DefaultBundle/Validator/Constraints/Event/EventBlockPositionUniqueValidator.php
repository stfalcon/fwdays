<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\Event;

use Application\Bundle\DefaultBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * EventBlockPositionUniqueValidator.
 */
class EventBlockPositionUniqueValidator extends ConstraintValidator
{
    /**
     * @param Event      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EventBlockPositionUnique) {
            throw new \RuntimeException(\sprintf(
                'Object of class %s is not instance of %s',
                \get_class($constraint),
                'EventBlockTypeText'
            ));
        }

        if ($value instanceof Event) {
            $blockPositions = [];
            foreach ($value->getBlocks() as $index => $block) {
                if (in_array($block->getPosition(), $blockPositions)) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->atPath("blocks[$index].position")
                        ->addViolation();
                } else {
                    $blockPositions[] = $block->getPosition();
                }
            }
        }
    }
}
