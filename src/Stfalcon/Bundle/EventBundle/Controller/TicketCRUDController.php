<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\AdminBundle\Controller\CRUDController;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketCRUDController.
 */
class TicketCRUDController extends CRUDController
{
    /**
     * @param int $id
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return RedirectResponse
     */
    public function removePaidTicketFromPaymentAction($id)
    {
        /** @var Ticket $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        $em = $this->getDoctrine()->getManager();
        /**
         * @var Ticket
         */
        $ticket = $em->getRepository('StfalconEventBundle:Ticket')->find($id);

        if ($ticket) {
            /** @var Payment $payment */
            $payment = $ticket->getPayment();
            if ($payment && $payment->isPaid()) {
                if ($payment->removePaidTicket($ticket)) {
                    /** @var TicketCost $ticketCost */
                    $ticketCost = $ticket->getTicketCost();
                    if ($ticketCost) {
                        $ticketCost->decSoldCount();
                    }
                }
                $this->get('stfalcon_event.listener.payment')->setRunPaymentPostUpdate(false);
                $em->flush();
                $this->get('stfalcon_event.listener.payment')->setRunPaymentPostUpdate(true);
                $this->addFlash('sonata_flash_success', 'Ticket removed successfully');
            }
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
