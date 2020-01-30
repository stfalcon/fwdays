<?php

namespace App\Controller;

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
 * UserController.
 */
class UserController extends AbstractController
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
        $userActiveEvents = $this->eventRepository->getSortedUserWannaVisitEventsByActive($user);
        $userPastEvents = $this->eventRepository->getSortedUserWannaVisitEventsByActive($user, false, Criteria::DESC);

        // list of events for refferal url
        $allActiveEvents = $this->eventRepository->findBy(['active' => true, 'adminOnly' => false]);

        return $this->render('User/cabinet.html.twig', [
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
        return $this->render('FOSUserBundle/views/Resetting:passwordAlreadyRequested.html.twig');
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
