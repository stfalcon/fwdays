<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * EmailSubscribe controller
 */
class EmailSubscribeController extends Controller
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
    public function actionUnsubscribe($userId, $hash)
    {
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

        return $this->render('ApplicationDefaultBundle:EmailSubscribe:unsubscribe.html.twig', [
            'hash' => $hash,
            'userId' => $userId
        ]);
    }

    /**
     * Subscribe action.
     *
     * @param integer $userId
     * @param string $hash
     *
     * @return RedirectResponse
     *
     * @Route("/subscribe/{hash}/{userId}", name="subscribe")
     */
    public function actionSubscribe($userId, $hash)
    {
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

        $subscriber->setSubscribe(true);
        $em->persist($subscriber);
        $em->flush();

        return $this->render('ApplicationDefaultBundle:EmailSubscribe:subscribe.html.twig', [
            'hash' => $hash,
            'userId' => $userId
        ]);
    }
}