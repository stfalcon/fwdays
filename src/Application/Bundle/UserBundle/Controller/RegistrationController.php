<?php

namespace Application\Bundle\UserBundle\Controller;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

class RegistrationController extends BaseController
{
    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $response = new RedirectResponse($this->container->get('router')->generate('fos_user_registration_confirmed'));
        $this->authenticateUser($user, $response);

        // Убрал согласно тикету #24389
//        $activeEvents = $this->container->get('doctrine')->getManager()
//            ->getRepository('StfalconEventBundle:Event')
//            ->findBy(array('active' => true ));
//
//        // Подписываем пользователя на все активные евенты
//        $em = $this->container->get('doctrine')->getManagerForClass('StfalconEventBundle:Ticket');
//        foreach ($activeEvents as $activeEvent) {
//            $ticket = new Ticket();
//            $ticket->setEvent($activeEvent);
//            $ticket->setUser($user);
//            $ticket->setAmount($activeEvent->getCost());
//            $ticket->setAmountWithoutDiscount($activeEvent->getCost());
//            $em->persist($ticket);
//            $em->flush();
//        }

        return $response;
    }
}
