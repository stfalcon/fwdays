<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Model\UserManager;
use App\Repository\EventRepository;
use App\Service\ReferralService;
use Doctrine\Common\Collections\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class UserController.
 */
class UserController extends Controller
{
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

        return $this->render('@App/User/cabinet.html.twig', [
            'user' => $user,
            'user_active_events' => $userActiveEvents,
            'user_past_events' => $userPastEvents,
            'events' => $allActiveEvents,
            'code' => $this->get(ReferralService::class)->getReferralCode(),
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
        return $this->render('FOSUserBundle:Resetting:passwordAlreadyRequested.html.twig');
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
        $userManager = $this->get(UserManager::class);
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
}
