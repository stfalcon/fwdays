<?php

namespace App\Controller;

use App\Entity\Ticket;
use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
            ]);
        }

        $ticket = $this->getDoctrine()->getManager()->getRepository(Ticket::class)
            ->findOneBy(['id' => $ticketId]);

        if (null !== $ticket) {
            $url = $this->generateUrl(
                'event_ticket_registration',
                [
                    'ticket' => $ticket->getId(),
                    'hash' => $ticket->getHash(),
                ],
                true
            );

            return $this->render('ticket_admin/check.html.twig', [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
                'ticket_url' => $url,
            ]);
        }

        return $this->render('ticket_admin/check.html.twig', [
            'base_template' => $this->getBaseTemplate(),
            'admin_pool' => $this->container->get('sonata.admin.pool'),
            'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
            'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
            'message' => 'Not Found',
        ]);
    }
}
