<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stfalcon\Bundle\EventBundle\Entity\Page;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage",
     *     options = {"expose"=true})
     * @Template("ApplicationDefaultBundle:Redesign:index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true], ['date' => 'ASC']);

        return ['events' => $events];
    }

    /**
     * @Route(path="/cabinet", name="cabinet")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function cabinetAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Ticket');
        $tickets = $ticketRepository->findTicketsOfActiveEventsForUser($user);
        $wannaVisit = $user->getWantsToVisitEvents();

        $userEvents = new ArrayCollection();
        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            $userEvents->add($ticket->getEvent());
        }

        foreach ($wannaVisit as $event) {
            if (!$userEvents->contains($event)) {
                $userEvents->add($event);
            }
        }

        $referralService = $this->get('stfalcon_event.referral.service');

        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true]);

        return $this->render(
            "ApplicationDefaultBundle:Redesign:cabinet.html.twig",
            [
                'user' => $user,
                'user_events' => $userEvents,
                'events' => $events,
                'code' => $referralService->getReferralCode(),
            ]
        );
    }

    /**
     * @Route("/contacts", name="contacts")
     *
     * @return Response
     */
    public function contactsAction()
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBy(['slug' => 'contacts']);
        if (!$staticPage) {
            throw $this->createNotFoundException('Page not found! about');
        }

        return $this->render(
            '@ApplicationDefault/Redesign/static_contacts.page.html.twig',
            [
                'text' => $staticPage->getText(),
            ]
        );
    }

    /**
     * @Route("/about", name="about")
     *
     * @Template("@ApplicationDefault/Redesign/static.page.html.twig")
     *
     * @return array
     */
    public function aboutAction()
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBy(['slug' => 'about']);
        if (!$staticPage) {
            throw $this->createNotFoundException('Page not found! about');
        }

        return ['text' => $staticPage->getText()];
    }

    /**
     * @Route("/page/{slug}", name="show_page")
     *
     * @param string $slug
     *
     * @Template("@ApplicationDefault/Redesign/static.page.html.twig")
     *
     * @return array
     */
    public function pageAction($slug)
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBy(['slug' => $slug]);
        if (!$staticPage) {
            throw $this->createNotFoundException(sprintf('Page not found! %s', $slug));
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
        /** @var User */
        $user = $this->getUser();

        if ('yes' === $reply) {
            $user->setAllowShareContacts(true);
        } else {
            $user->setAllowShareContacts(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $url = $this->getRequest()->headers->get('referer');

        return new RedirectResponse($url);
    }
}
