<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketAdminController.
 */
class TicketAdminController extends CoreController
{
    /**
     * Check that ticket number is valid.
     *
     * @param Request $request
     *
     * @Template()
     *
     * @return array
     */
    public function checkAction(Request $request)
    {
        if (!($ticketId = $request->get('id'))) {
            return [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
            ];
        }

        $ticket = $this->getDoctrine()->getManager()->getRepository('ApplicationDefaultBundle:Ticket')
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

            return [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
                'ticket_url' => $url,
            ];
        }

        return [
            'base_template' => $this->getBaseTemplate(),
            'admin_pool' => $this->container->get('sonata.admin.pool'),
            'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
            'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
            'message' => 'Not Found',
        ];
    }
}
