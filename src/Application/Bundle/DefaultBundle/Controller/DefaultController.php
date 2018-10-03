<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stfalcon\Bundle\EventBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage",
     *     options = {"expose"=true})
     *
     * @return Response
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findAllByActiveSorted(true, 'ASC');

        return $this->render('ApplicationDefaultBundle:Redesign:index.html.twig', ['events' => $events]);
    }

    /**
     * @return Response
     */
    public function footerPagesAction()
    {
        $pages = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->getPagesForFooter();

        return $this->render('ApplicationDefaultBundle:Redesign:_footer_pages.html.twig', ['pages' => $pages]);
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

        $userActiveEvents = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->getSortedUserWannaVisitEventsByActive($user, true, 'ASC');

        $userPastEvents = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->getSortedUserWannaVisitEventsByActive($user, false, 'DESC');

        $allActiveEvents = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true]);

        return $this->render('ApplicationDefaultBundle:Redesign:cabinet.html.twig', [
            'user' => $user,
            'user_active_events' => $userActiveEvents,
            'user_past_events' => $userPastEvents,
            'events' => $allActiveEvents,
            'code' => $this->get('stfalcon_event.referral.service')->getReferralCode(),
        ]);
    }

    /**
     * @Route("/contacts", name="contacts")
     *
     * @return Response
     */
    public function contactsAction()
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBySlug('contacts');
        if (!$staticPage) {
            throw $this->createNotFoundException('Page not found! about');
        }

        return $this->render('@ApplicationDefault/Redesign/static_contacts.page.html.twig', [
                'text' => $staticPage->getText(),
        ]);
    }

    /**
     * @Route("/about", name="about")
     *
     * @return Response
     */
    public function aboutAction()
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBySlug('about');
        if (!$staticPage) {
            throw $this->createNotFoundException('Page not found! about');
        }

        return $this->render('@ApplicationDefault/Redesign/static.page.html.twig', ['text' => $staticPage->getText()]);
    }

    /**
     * @Route("/page/{slug}", name="show_page")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function pageAction($slug)
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBySlug($slug);
        if (!$staticPage) {
            throw $this->createNotFoundException(sprintf('Page not found! %s', $slug));
        }

        return $this->render('@ApplicationDefault/Redesign/static.page.html.twig', ['text' => $staticPage->getText()]);
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

        $url = $this->get('request_stack')->getCurrentRequest()->headers->get('referer');

        return new RedirectResponse($url);
    }
}
