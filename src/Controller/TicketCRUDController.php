<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\EventListener\PaymentListener;
use App\Helper\NewPdfGeneratorHelper;
use App\Helper\PdfGeneratorHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * TicketCRUDController.
 */
class TicketCRUDController extends CRUDController
{
    private $pdfGeneratorHelper;
    private $paymentListener;

    /**
     * @param PdfGeneratorHelper $pdfGeneratorHelper
     * @param PaymentListener    $paymentListener
     */
    public function __construct(PdfGeneratorHelper $pdfGeneratorHelper, PaymentListener $paymentListener)
    {
        $this->pdfGeneratorHelper = $pdfGeneratorHelper;
        $this->paymentListener = $paymentListener;
    }

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

        $html = $this->pdfGeneratorHelper->generateHTML($ticket);

        return new Response(
            $this->pdfGeneratorHelper->generatePdfFile($ticket, $html),
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
        $ticket = $em->getRepository(Ticket::class)->find($id);

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
                $this->paymentListener->setRunPaymentPostUpdate(false);
                $em->flush();
                $this->paymentListener->setRunPaymentPostUpdate(true);

                $this->addFlash('sonata_flash_success', 'Ticket removed successfully');
            }
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
