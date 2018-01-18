<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\TicketCost;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EventDateValidator.
 */
class TicketCostCountValidator extends ConstraintValidator
{
    /**
     * @param TicketCost|null $ticketCost
     * @param Constraint      $constraint
     */
    public function validate($ticketCost, Constraint $constraint)
    {
        if ($ticketCost instanceof TicketCost) {
            if (!$ticketCost->isUnlimited() && $ticketCost->getCount() < $ticketCost->getSoldCount()) {
                $this->context
                    ->addViolationAt(
                        'count',
                        sprintf(
                            'Количество билетов (%s) должно быть не меньше, чем количество проданых (%s)',
                            $ticketCost->getCount(),
                            $ticketCost->getSoldCount()
                        )
                    );

                return;
            }
        }
    }
}
