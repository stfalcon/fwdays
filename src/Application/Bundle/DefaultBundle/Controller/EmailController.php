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
     * @Route("/unsubscribe/{hash}/{id}/{mailId}", name="unsubscribe")
     *
     * @param string $hash
     * @param User   $subscriber
     * @param int    $mailId
     *
     * @return Response
     */
    public function unsubscribeAction(string $hash, User $subscriber, ?int $mailId = null): Response
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
     * @Route("/subscribe/{hash}/{id}", name="subscribe")
     *
     * @param string $hash
     * @param User   $subscriber
     *
     * @return Response
     */
    public function subscribeAction(string $hash, User $subscriber): Response
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
     * @Route("/trackopenmail/{hash}/{id}/{mailId}", name="trackopenmail")
     *
     * @param string $hash
     * @param User   $subscriber
     * @param int    $mailId
     *
     * @return RedirectResponse
     */
    public function actionTrackOpenMail(string $hash, User $subscriber, int $mailId): RedirectResponse
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
