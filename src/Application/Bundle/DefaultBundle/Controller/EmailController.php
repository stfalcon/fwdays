<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\MailQueue;
use Application\Bundle\DefaultBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * EmailSubscribe controller.
 */
class EmailController extends Controller
{
    /**
     * Unsubscribe action.
     *
     * @Route("/unsubscribe/{hash}/{userId}/{mailId}", name="unsubscribe")
     *
     * @param string $hash
     * @param int    $userId
     * @param int    $mailId
     *
     * @return Response
     */
    public function unsubscribeAction($hash, $userId, $mailId = null): Response
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $subscriber */
        $subscriber = $em->getRepository('ApplicationDefaultBundle:User')
            ->findOneBy(['id' => $userId, 'salt' => $hash]);

        if (!$subscriber) {
            throw $this->createNotFoundException('Unable to find Subscriber.');
        }

        if ($mailId) {
            $mail = $em->getRepository('ApplicationDefaultBundle:Mail')->find($mailId);
            if ($mail) {
                $mail->addUnsubscribeMessagesCount();
            }
            /** @var MailQueue $mailQueue */
            $mailQueue = $em->getRepository('ApplicationDefaultBundle:MailQueue')
                ->findOneBy(['user' => $userId, 'mail' => $mailId]);
            if ($mailQueue && $subscriber->isSubscribe()) {
                $mailQueue->setIsUnsubscribe();
            }
        }

        $subscriber->setSubscribe(false);
        $em->flush();

        return $this->render('@ApplicationDefault/Email/unsubscribe.html.twig', ['hash' => $hash, 'userId' => $userId]);
    }

    /**
     * Subscribe action.
     *
     * @Route("/subscribe/{hash}/{userId}", name="subscribe")
     *
     * @param int    $userId
     * @param string $hash
     *
     * @return Response
     */
    public function subscribeAction($userId, $hash): Response
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('ApplicationDefaultBundle:User')
            ->findOneBy(['id' => $userId, 'salt' => $hash]);

        if (!$user) {
            throw $this->createNotFoundException("Unable to find User #{$user->getId()}.");
        }

        $user->setSubscribe(true);
        $em->flush();

        return $this->render('@ApplicationDefault/Email/subscribe.html.twig', ['hash' => $hash, 'userId' => $userId]);
    }

    /**
     * Open mail action.
     *
     * @Route("/trackopenmail/{hash}/{userId}/{mailId}", name="trackopenmail")
     *
     * @param int    $userId
     * @param string $hash
     * @param int    $mailId
     *
     * @return RedirectResponse
     */
    public function actionTrackOpenMail($userId, $hash, $mailId = null): RedirectResponse
    {
        if ($mailId) {
            $em = $this->getDoctrine()->getManager();
            /** @var User $user */
            $user = $em->getRepository('ApplicationDefaultBundle:User')
                ->findOneBy(['id' => $userId, 'salt' => $hash]);

            if ($user) {
                /** @var MailQueue $mailQueue */
                $mailQueue = $em->getRepository('ApplicationDefaultBundle:MailQueue')->findOneBy(['user' => $userId, 'mail' => $mailId]);
                if ($mailQueue && !$mailQueue->getIsOpen()) {
                    /** @var Mail $mail */
                    $mail = $em->getRepository('ApplicationDefaultBundle:Mail')->find($mailId);
                    if ($mail) {
                        $mail->addOpenMessagesCount();
                    }
                    $mailQueue->setIsOpen();
                    $em->flush();
                }
            }
        }

        return $this->redirect($this->generateUrl('homepage'));
    }
}
