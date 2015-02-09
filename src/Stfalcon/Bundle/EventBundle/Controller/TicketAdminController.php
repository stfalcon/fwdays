<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CoreController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Class TicketAdminController
 * @package Stfalcon\Bundle\EventBundle\Controller
 */
class TicketAdminController extends CoreController
{

    /**
     * Check that ticket number is valid
     *
     * @param Request $request
     * @return array
     * @Template()
     */
    public function checkAction(Request $request)
    {
        if (!($ticketId = $request->get('id'))) {
            return [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check')
            ];
        }

        $ticket = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(['id' => $ticketId]);

        if (!is_null($ticket)) {
            $url = $this->generateUrl(
                'event_ticket_registration',
                [
                    'ticket' => $ticket->getId(),
                    'hash' => $ticket->getHash()
                ],
                true
            );

            return [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
                'ticket_url' => $url
            ];

        } else {
            return [
                'base_template' => $this->getBaseTemplate(),
                'admin_pool' => $this->container->get('sonata.admin.pool'),
                'blocks' => $this->container->getParameter('sonata.admin.configuration.dashboard_blocks'),
                'form_action' => $this->generateUrl('sonata_admin_ticket_check'),
                'message' => 'Not Found',
            ];
        }
    }
} 