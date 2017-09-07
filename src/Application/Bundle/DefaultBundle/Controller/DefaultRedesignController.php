<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stfalcon\Bundle\EventBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultRedesignController extends Controller
{
    /**
     * @Route("/", name="homepage_redesign",
     *     options = {"expose"=true})
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
     * @Route(path="/cabinet", name="cabinet")
     * @Security("has_role('ROLE_USER')")
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

    public function payAction()
    {

    }
}