<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     * @param integer $mailId
     * @Template()
     * @return array
     *
     * @Route("/unsubscribe/{hash}/{userId}/{mailId}", name="unsubscribe")
     */
    public function unsubscribeAction($hash, $userId, $mailId = null)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $subscriber */
        $subscriber = $em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['id' => $userId, 'salt' => $hash]);

        if (!$subscriber) {
            throw $this->createNotFoundException('Unable to find Subscriber.');
        }

        if ($mailId) {
            /** @var MailQueue $mailQueue */
            $mailQueue = $em->getRepository('StfalconEventBundle:MailQueue')
                ->findOneBy(['user' => $userId, 'mail' => $mailId]);
            if ($mailQueue && $subscriber->isSubscribe()) {
                $mailQueue->setIsUnsubscribe();
            }
        }

        $subscriber->setSubscribe(false);
        $em->flush();

        return ['hash' => $hash, 'userId' => $userId];
    }

    /**
     * Subscribe action.
     *
     * @param integer $userId
     * @param string $hash
     * @Template()
     * @return array
     *
     * @Route("/subscribe/{hash}/{userId}", name="subscribe")
     */
    public function subscribeAction($userId, $hash)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $subscriber */
        $subscriber = $em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['id' => $userId, 'salt' => $hash]);

        if (!$subscriber) {
            throw $this->createNotFoundException('Unable to find Subscriber.');
        }

        $subscriber->setSubscribe(true);
        $em->flush();

        return ['hash' => $hash, 'userId' => $userId];
    }
}
