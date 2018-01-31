<?php

namespace Application\Bundle\DefaultBundle\Validator\Constraints\Event;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EventTicketCostCountValidator.
 */
class EventTicketCostCountValidator extends ConstraintValidator
{
    /**
     * @param Event      $event
     * @param Constraint $constraint
     */
    public function validate($event, Constraint $constraint)
    {
        if ($event instanceof Event) {
            $ticketCosts = $event->getTicketsCost();
            /** @var TicketCost $ticketCost */
            foreach ($ticketCosts as $ticketCost) {
                if (!$ticketCost->isUnlimited() && $ticketCost->getCount() < $ticketCost->getSoldCount()) {
                    $this->context
                        ->addViolationAt(
                            'ticketsCost',
                            sprintf(
                                'Блок цен "%s". Количество билетов (%s) должно быть не меньше, чем количество проданых (%s)',
                                $ticketCost->getName(),
                                $ticketCost->getCount(),
                                $ticketCost->getSoldCount()
                            )
                        );
                }
            }
        }
    }
}
