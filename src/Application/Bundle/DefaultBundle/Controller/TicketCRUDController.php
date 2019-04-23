<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\AdminBundle\Controller\CRUDController;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function downloadAction($id)
    {
        /** @var Ticket $ticket */
        $ticket = $this->admin->getSubject();

        if (!$ticket instanceof Ticket) {
            throw new NotFoundHttpException(sprintf('unable to find the ticket with id : %s', $id));
        }

        /** @var $pdfGen \Application\Bundle\DefaultBundle\Helper\NewPdfGeneratorHelper */
        $pdfGen = $this->get('app.helper.new_pdf_generator');

        $html = $pdfGen->generateHTML($ticket);

        return new Response(
            $pdfGen->generatePdfFile($ticket, $html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attach; filename="%s"', $ticket->generatePdfFilename()),
            ]
        );
    }

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
        $ticket = $em->getRepository('ApplicationDefaultBundle:Ticket')->find($id);

        if ($ticket) {
            /** @var Payment $payment */
            $payment = $ticket->getPayment();
            if ($payment && $payment->isPaid()) {
                if ($payment->removePaidTicket($ticket)) {
                    /** @var TicketCost $ticketCost */
                    $ticketCost = $ticket->getTicketCost();
                    if ($ticketCost) {
                        $ticketCost->recalculateSoldCount();
                    }
                }
                $this->get('application.listener.payment')->setRunPaymentPostUpdate(false);
                $em->flush();
                $this->get('application.listener.payment')->setRunPaymentPostUpdate(true);
                $this->addFlash('sonata_flash_success', 'Ticket removed successfully');
            }
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
