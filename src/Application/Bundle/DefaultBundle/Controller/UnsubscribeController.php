<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Unsubscribe controller
 */
class UnsubscribeController extends Controller
{

    /**
     * Unsubscribe action.
     *
     * @param integer $userId
     * @param string $hash
     *
     * @return RedirectResponse
     *
     * @Route("/unsubscribe/{hash}/{userId}", name="unsubscribe")
     */
    public function actionUnsubscribe($userId, $hash) {

        /**
         * @var User $subscriber
         */
        $subscriber = $this->getDoctrine()
            ->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['id' => $userId, 'salt' => $hash]);

        if (!$subscriber) {
            throw $this->createNotFoundException('Unable to find Subscriber.');
        }

        $em = $this->getDoctrine()->getManager();

        $subscriber->setSubscribe(false);
        $em->persist($subscriber);
        $em->flush();

        return $this->redirect($this->generateUrl("homepage"));
    }
}