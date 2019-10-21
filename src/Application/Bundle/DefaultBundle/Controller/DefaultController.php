<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Page;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage", options = {"expose"=true})
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $events = $this->getDoctrine()
            ->getRepository('ApplicationDefaultBundle:Event')
            ->findBy(['active' => true], ['date' => 'ASC']);

        return $this->render('@ApplicationDefault/Default/index.html.twig', ['events' => $events]);
    }

    /**
     * @Route("/page/{slug}", name="page")
     *
     * @param Page $page
     *
     * @return Response
     */
    public function pageAction(Page $page): Response
    {
        if ('contacts' === $page->getSlug()) {
            return $this->render('@ApplicationDefault/Redesign/static_contacts.page.html.twig', ['page' => $page]);
        }

        return $this->render('@ApplicationDefault/Default/page.html.twig', ['page' => $page]);
    }

    /**
     * @Route(path="/cabinet", name="cabinet")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function cabinetAction(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getDoctrine()
            ->getRepository('ApplicationDefaultBundle:Event');

        $userActiveEvents = $eventRepository
            ->getSortedUserWannaVisitEventsByActive($user);

        $userPastEvents = $eventRepository
            ->getSortedUserWannaVisitEventsByActive($user, false, 'DESC');

        // list of events for refferal url
        $allActiveEvents = $eventRepository
            ->findBy(['active' => true, 'adminOnly' => false]);

        return $this->render('@ApplicationDefault/Default/cabinet.html.twig', [
            'user' => $user,
            'user_active_events' => $userActiveEvents,
            'user_past_events' => $userPastEvents,
            'events' => $allActiveEvents,
            'code' => $this->get('app.referral.service')->getReferralCode(),
        ]);
    }

    /**
     * @Route("/password-already-requested", name="password_already_requested")
     *
     * @return Response
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function passwordAlreadyRequestedAction(): Response
    {
        return $this->render('FOSUserBundle:Resetting:password_already_requested.html.twig');
    }

    /**
     * @Route(path="/update-user-phone/{phoneNumber}", name="update_user_phone",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $phoneNumber
     *
     * @return JsonResponse
     */
    public function updateUserPhoneAction(string $phoneNumber): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $userManager = $this->get('application.user_manager');
        $validator = $this->get('validator');
        $user->setPhone($phoneNumber);
        $errors = $validator->validate($user);
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            if ('name' === $error->getPropertyPath()) {
                $user->getName();
            } elseif ('surname' === $error->getPropertyPath()) {
                $user->getSurname();
            }
        }

        $errors = $validator->validate($user);

        if (\count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['result' => true, 'error' => $errorsString]);
        }

        $userManager->updateUser($user);

        return new JsonResponse(['result' => true]);
    }

    /**
     * @return Response
     */
    public function renderMicrolayoutAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('ApplicationDefaultBundle:Event')
            ->findClosesActiveEvents(3);

        return $this->render('ApplicationDefaultBundle::microlayout.html.twig', ['events' => $events]);
    }
}
