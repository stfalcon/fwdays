<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;

/**
 * EmailSubscribe controller
 */
class EmailSubscribeController extends Controller
{
    /**
     * Unsubscribe action.
     *
     * @Route("/unsubscribe/{hash}/{userId}/{mailId}", name="unsubscribe")
     *
     * @param string  $hash
     * @param integer $userId
     * @param integer $mailId
     *
     * @Template()
     *
     * @return array
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
     * @Route("/subscribe/{hash}/{userId}", name="subscribe")
     *
     * @param integer $userId
     * @param string  $hash
     *
     * @Template()
     *
     * @return array
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

    /**
     * Open mail action.
     *
     * @Route("/trackopenmail/{hash}/{userId}/{mailId}", name="trackopenmail")
     *
     * @param integer $userId
     * @param string  $hash
     * @param integer $mailId
     *
     * @return RedirectResponse
     */
    public function actionTrackOpenMail($userId, $hash, $mailId = null)
    {
        if ($mailId) {
            $em = $this->getDoctrine()->getManager();
            /** @var User $user */
            $user = $em->getRepository('ApplicationUserBundle:User')
                ->findOneBy(['id' => $userId, 'salt' => $hash]);

            if ($user) {
                /** @var MailQueue $mailQueue */
                $mailQueue = $em->getRepository('StfalconEventBundle:MailQueue')->findOneBy(['user' => $userId, 'mail' => $mailId]);
                if ($mailQueue && !$mailQueue->getIsOpen()) {
                    $mailQueue->setIsOpen();
                    $em->flush();
                }

                if (!$user->isEmailConfirmedByPixel()) {
                    $user->setEmailConfirmedByPixel(true);
                    $em->flush();
                }
            }
        }

        return $this->redirect($this->generateUrl("homepage"));
    }
}
