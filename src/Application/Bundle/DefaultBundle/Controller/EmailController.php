<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Mail;
use Application\Bundle\DefaultBundle\Entity\MailQueue;
use Application\Bundle\DefaultBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @param string   $hash
     * @param int      $userId
     * @param int|null $mailId
     *
     * @return Response
     */
    public function unsubscribeAction($hash, $userId, ?int $mailId = null): Response
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
     * @Route("/new-unsubscribe/{hash}/{id}/{mailId}", name="new-unsubscribe")
     *
     * @param string $hash
     * @param User   $subscriber
     * @param int    $mailId
     *
     * @return Response
     */
    public function newUnsubscribeAction(string $hash, User $subscriber, ?int $mailId = null): Response
    {
        $emailHashValidService = $this->get('app.email_hash_validation.service');

        if (!$emailHashValidService->isHashValid($hash, $subscriber, $mailId)) {
            throw new BadRequestHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        if ($mailId) {
            $mail = $em->getRepository('ApplicationDefaultBundle:Mail')->find($mailId);
            if ($mail) {
                $mail->addUnsubscribeMessagesCount();
            }
            /** @var MailQueue $mailQueue */
            $mailQueue = $em->getRepository('ApplicationDefaultBundle:MailQueue')
                ->findOneBy(['user' => $subscriber->getId(), 'mail' => $mailId]);
            if ($mailQueue && $subscriber->isSubscribe()) {
                $mailQueue->setIsUnsubscribe();
            }
        }

        $subscriber->setSubscribe(false);
        $em->flush();

        return $this->render('@ApplicationDefault/Email/unsubscribe.html.twig', ['user' => $subscriber]);
    }

    /**
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
     * @Route("/new-subscribe/{hash}/{id}", name="new-subscribe")
     *
     * @param string $hash
     * @param User   $subscriber
     *
     * @return Response
     */
    public function newSubscribeAction(string $hash, User $subscriber): Response
    {
        $emailHashValidService = $this->get('app.email_hash_validation.service');

        if (!$emailHashValidService->isHashValid($hash, $subscriber)) {
            throw new BadRequestHttpException();
        }

        $subscriber->setSubscribe(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->render('@ApplicationDefault/Email/subscribe.html.twig');
    }

    /**
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

    /**
     * @Route("/new-trackopenmail/{hash}/{id}/{mailId}", name="new-trackopenmail")
     *
     * @param string $hash
     * @param User   $subscriber
     * @param int    $mailId
     *
     * @return RedirectResponse
     */
    public function newTrackOpenMailAction(string $hash, User $subscriber, int $mailId): RedirectResponse
    {
        $emailHashValidService = $this->get('app.email_hash_validation.service');

        if (!$emailHashValidService->isHashValid($hash, $subscriber, $mailId)) {
            throw new BadRequestHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        /** @var MailQueue $mailQueue */
        $mailQueue = $em->getRepository('ApplicationDefaultBundle:MailQueue')->findOneBy(['user' => $subscriber, 'mail' => $mailId]);
        if ($mailQueue && !$mailQueue->getIsOpen()) {
            /** @var Mail $mail */
            $mail = $em->getRepository('ApplicationDefaultBundle:Mail')->find($mailId);
            if ($mail) {
                $mail->addOpenMessagesCount();
            }
            $mailQueue->setIsOpen();
            $em->flush();
        }

        return $this->redirect($this->generateUrl('homepage'));
    }
}
