<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Page;
use App\Entity\User;
use App\Model\UserManager;
use App\Repository\EventRepository;
use App\Service\ReferralService;
use App\Traits\ValidatorTrait;
use Doctrine\Common\Collections\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class DefaultController.
 */
class DefaultController extends AbstractController
{
    use ValidatorTrait;

    private $referralService;
    private $userManager;
    private $eventRepository;

    /**
     * @param ReferralService $referralService
     * @param UserManager     $userManager
     * @param EventRepository $eventRepository
     */
    public function __construct(ReferralService $referralService, UserManager $userManager, EventRepository $eventRepository)
    {
        $this->referralService = $referralService;
        $this->userManager = $userManager;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/", name="homepage", options = {"expose"=true})
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $events = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBy(['active' => true], ['date' => Criteria::ASC]);

        return $this->render('Default/index.html.twig', ['events' => $events]);
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
            return $this->render('Redesign/static_contacts.page.html.twig', ['page' => $page]);
        }

        if ('about' === $page->getSlug()) {
            return $this->render('Page/about.html.twig', ['page' => $page]);
        }

        return $this->render('Default/page.html.twig', ['page' => $page]);
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
            ->getRepository(Event::class);

        $userActiveEvents = $eventRepository
            ->getSortedUserWannaVisitEventsByActive($user);

        $userPastEvents = $eventRepository
            ->getSortedUserWannaVisitEventsByActive($user, false, Criteria::DESC);

        // list of events for refferal url
        $allActiveEvents = $eventRepository
            ->findBy(['active' => true, 'adminOnly' => false]);

        return $this->render('Default/cabinet.html.twig', [
            'user' => $user,
            'user_active_events' => $userActiveEvents,
            'user_past_events' => $userPastEvents,
            'events' => $allActiveEvents,
            'code' => $this->referralService->getReferralCode(),
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
        return $this->render(':FOSUserBundle/views/Resetting:passwordAlreadyRequested.html.twig');
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
        $user->setPhone($phoneNumber);
        $errors = $this->validator->validate($user);
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            if ('name' === $error->getPropertyPath()) {
                $user->getName();
            } elseif ('surname' === $error->getPropertyPath()) {
                $user->getSurname();
            }
        }

        $errors = $this->validator->validate($user);
        if (\count($errors) > 0) {
            return new JsonResponse(['result' => false, 'error' => 'update user error']);
        }

        $this->userManager->updateUser($user);

        return new JsonResponse(['result' => true]);
    }
}
