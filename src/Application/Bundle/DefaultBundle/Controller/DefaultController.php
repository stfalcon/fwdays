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
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
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
     *
     * @param string $slug
     * @Template("@ApplicationDefault/Redesign/static.page.html.twig")
     *
     * @return array
     */
    public function pageAction($slug)
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBy(['slug' => $slug]);
        if (!$staticPage) {
            $this->createNotFoundException('Page not found! '.$slug);
        }

        return ['text' => $staticPage->getText()];
    }

    /**
     * @Route("/share-contacts/{reply}", name="share_contacts")
     *
     * @param string $reply
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return RedirectResponse
     */
    public function shareContactsAction($reply = 'no')
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ('yes' == $reply) {
            $user->setAllowShareContacts(true);
        } else {
            $user->setAllowShareContacts(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $url = $this->getRequest()->headers->get("referer");

        return new RedirectResponse($url);
    }
}