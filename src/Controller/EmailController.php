<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Entity\User;
use App\Service\EmailHashValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EmailSubscribe.
 */
class EmailController extends AbstractController
{
    private $emailHashValidation;

    /**
     * @param EmailHashValidationService $emailHashValidation
     */
    public function __construct(EmailHashValidationService $emailHashValidation)
    {
        $this->emailHashValidation = $emailHashValidation;
    }

    /**
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
        if (!$this->emailHashValidation->isHashValid($hash, $subscriber, $mailId)) {
            throw new BadRequestHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        if ($mailId) {
            $mail = $em->getRepository(Mail::class)->find($mailId);
            if ($mail) {
                $mail->addUnsubscribeMessagesCount();
            }
            /** @var MailQueue $mailQueue */
            $mailQueue = $em->getRepository(MailQueue::class)
                ->findOneBy(['user' => $subscriber->getId(), 'mail' => $mailId]);
            if ($mailQueue && $subscriber->isSubscribe()) {
                $mailQueue->setIsUnsubscribe();
            }
        }

        $subscriber->setSubscribe(false);
        $em->flush();

        return $this->render('Email/unsubscribe.html.twig', ['user' => $subscriber]);
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
        if (!$this->emailHashValidation->isHashValid($hash, $subscriber)) {
            throw new BadRequestHttpException();
        }

        $subscriber->setSubscribe(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->render('Email/subscribe.html.twig');
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
        if (!$this->emailHashValidation->isHashValid($hash, $subscriber, $mailId)) {
            throw new BadRequestHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        /** @var MailQueue $mailQueue */
        $mailQueue = $em->getRepository(MailQueue::class)->findOneBy(['user' => $subscriber, 'mail' => $mailId]);
        if ($mailQueue && !$mailQueue->getIsOpen()) {
            /** @var Mail $mail */
            $mail = $em->getRepository(Mail::class)->find($mailId);
            if ($mail) {
                $mail->addOpenMessagesCount();
            }
            $mailQueue->setIsOpen();
            $em->flush();
        }

        return $this->redirect($this->generateUrl('homepage'));
    }
}
