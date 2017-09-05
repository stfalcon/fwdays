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
     * Юзер бажає відвідати подію
     *
     * @Route(path="/addwantstovisitevent", name="add_wants_to_visit_event",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $slug
     *
     * @return JsonResponse
     */
    public function userAddWantsToVisitEventAction($slug)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $message = '';
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);

        if (!$event) {
            return new JsonResponse(['result' => $result, 'message' => 'Unable to find Event by slug: '.$slug]);
        }

        $result = false;

        if ($event->isActiveAndFuture()) {
            $result = $user->addWantsToVisitEvents($event);
        } else {
            $message = 'Event not active!';
        }

        $em->flush();

        return new JsonResponse(['result' => $result, 'message' => $message]);
    }

    /**
     * Юзер вже не бажає відвідати подію
     *
     * @Route("/subwantstovisitevent", name="sub_wants_to_visit_event",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $slug
     *
     * @return JsonResponse
     */
    public function userSubWantsToVisitEventAction($slug)
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = false;
        $message = '';
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => $slug]);

        if (!$event) {
            return new JsonResponse(['result' => $result, 'message' => 'Unable to find Event by slug: '.$slug]);
        }

        if ($event->isActiveAndFuture()) {
            $result = $user->subtractWantsToVisitEvents($event);
        } else {
            $message = 'Event not active!';
        }
        $em->flush();

        return new JsonResponse(['result' => $result, 'message' => $message]);
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