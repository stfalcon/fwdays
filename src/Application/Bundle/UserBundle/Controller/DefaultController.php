<?php

namespace Application\Bundle\UserBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
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

        // list of events for refferal url
        $allActiveEvents = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true, 'adminOnly' => false]);

        return $this->render('@ApplicationUser/Default/cabinet.html.twig', [
            'user' => $user,
            'user_active_events' => $userActiveEvents,
            'user_past_events' => $userPastEvents,
            'events' => $allActiveEvents,
            'code' => $this->get('stfalcon_event.referral.service')->getReferralCode(),
        ]);
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
    public function updateUserPhoneAction($phoneNumber)
    {
        /** @var User $user */
        $user = $this->getUser();
        $userManager = $this->get('stfalcon.user_manager');
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

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['result' => true, 'error' => $errorsString]);
        }

        $userManager->updateUser($user);

        return new JsonResponse(['result' => true]);
    }
}
