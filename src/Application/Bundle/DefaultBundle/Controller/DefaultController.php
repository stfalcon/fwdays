<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage", options = {"expose"=true})
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('ApplicationDefaultBundle:Event')
            ->findBy(['active' => true], ['date' => 'ASC']);

        return ['events' => $events];
    }

    /**
     * @Route("/page/{slug}", name="page")
     * @ParamConverter("page", options={"mapping": {"slug": "slug"}})
     * @Template()
     *
     * @param Page $page
     * @return array
     */
    public function pageAction(Page $page)
    {
        return ['page' => $page];
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

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getDoctrine()
            ->getRepository('ApplicationDefaultBundle:Event');

        $userActiveEvents = $eventRepository
            ->getSortedUserWannaVisitEventsByActive($user, true, 'ASC');

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
            'code' => $this->get('application.referral.service')->getReferralCode(),
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

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['result' => true, 'error' => $errorsString]);
        }

        $userManager->updateUser($user);

        return new JsonResponse(['result' => true]);
    }

    /**
     * @todo wtf?
     *
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
