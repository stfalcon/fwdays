<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stfalcon\Bundle\EventBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultRedesignController extends Controller
{
    /**
     * @Route("/", name="homepage_redesign")
     * @Template("ApplicationDefaultBundle:Redesign:index.html.twig")
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true ]);

        return [ 'events' => $events];
    }

    /**
     * @Route("/cabinet", name="cabinet")
     * @Secure("ROLE_USER")
     * @Template("ApplicationDefaultBundle:Redesign:cabinet.html.twig")
     */
    public function cabinetAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Ticket');
        $tickets = $ticketRepository->findTicketsOfActiveEventsForUser($user);

        $referralService = $this->get('stfalcon_event.referral.service');

        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true ]);

        return [
            'user' => $user,
            'tickets' => $tickets,
            'events' => $events,
            'code' => $referralService->getReferralCode(),
        ];
    }
    /**
     * @Route("/contacts", name="contacts")
     * @Template("ApplicationDefaultBundle:Redesign:contacts.html.twig")
     */
    public function contactsAction()
    {
        return [];
    }
    /**
     * @Route("/page/{slug}", name="show_page")
     * @Template("@ApplicationDefault/Redesign/static.page.html.twig")
     * @return array
     */
    public function pageAction(Page $staticPage)
    {
        return ['text' => $staticPage->getText()];
    }
}