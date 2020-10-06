<?php

namespace App\Controller;

use App\Entity\Ticket;
use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * TicketAdminController.
 */
class TicketAdminController extends CoreController
{
    /**
     * Check that ticket number is valid.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkAction(Request $request): Response
    {
        if (!($ticketId = $request->get('id'))) {
            return $this->render('ticket_admin/check.html.twig', [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
            ]);
        }

        $ticket = $this->getDoctrine()->getManager()->getRepository(Ticket::class)
            ->find($ticketId);

        if ($ticket instanceof Ticket) {
            $url = $this->generateUrl(
                'event_ticket_registration',
                [
                    'ticket' => $ticket->getId(),
                    'hash' => $ticket->getHash(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->render('ticket_admin/check.html.twig', [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
                'ticket_url' => $url,
            ]);
        }

        return $this->render('ticket_admin/check.html.twig', [
            'base_template' => $this->getBaseTemplate(),
            'admin_pool' => $this->container->get('sonata.admin.pool'),
            'blocks' => $this->getParameter('sonata.admin.configuration.dashboard_blocks'),
            'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
            'message' => 'Not Found',
        ]);
    }
}
