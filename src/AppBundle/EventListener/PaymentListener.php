<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Entity\Mail;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Helper\NewPdfGeneratorHelper;
use App\Helper\StfalconMailerHelper;
use App\Service\PaymentService;
use App\Service\TranslatedMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class PaymentListener.
 */
class PaymentListener
{
    /** @var StfalconMailerHelper $mailerHelper */
    private $mailerHelper;

    /** @var NewPdfGeneratorHelper $pdfGeneratorHelper */
    private $pdfGeneratorHelper;

    /** @var \Swift_Mailer $mailer */
    private $mailer;
    /**
     * @var Container
     */
    private $container;

    /** @var bool */
    private $runPaymentPostUpdate = true;

    /**
     * PaymentListener constructor.
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param bool $runPaymentPostUpdate
     */
    public function setRunPaymentPostUpdate($runPaymentPostUpdate)
    {
        $this->runPaymentPostUpdate = $runPaymentPostUpdate;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Payment) {
            if (Payment::STATUS_PAID === $entity->getStatus() && $this->runPaymentPostUpdate) {
                $this->mailer = $this->container->get('mailer');
                $this->mailerHelper = $this->container->get(StfalconMailerHelper::class);
                $this->pdfGeneratorHelper = $this->container->get(NewPdfGeneratorHelper::class);

                /** @var PaymentService $paymentService */
                $paymentService = $this->container->get(PaymentService::class);
                $paymentService->setTicketsCostAsSold($entity);
                $paymentService->calculateTicketsPromocode($entity);
                /** @var EntityManager $em */
                $em = $this->container->get('doctrine.orm.entity_manager');
                $tickets = $em->getRepository(Ticket::class)
                    ->getAllTicketsByPayment($entity);

                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    /** @var $user User */
                    $user = $ticket->getUser();

                    /** @var Event $event */
                    $event = $ticket->getEvent();

                    $mail = new Mail();
                    $mail->addEvent($event);

                    $translatedMailService = $this->container->get(TranslatedMailService::class);
                    $translatedMails = $translatedMailService->getTranslatedMailArray($mail);

                    $html = $this->pdfGeneratorHelper->generateHTML($ticket);
                    $defaultLocal = $this->container->getParameter('locale');
                    $message = $this->mailerHelper->formatMessage($user, $translatedMails[$defaultLocal], false, true);

                    $message->setSubject($event->getName());
                    $message->attach(
                        \Swift_Attachment::newInstance(
                            $this->pdfGeneratorHelper->generatePdfFile($ticket, $html),
                            $ticket->generatePdfFilename()
                        )
                    );

                    $this->mailer->send($message);
                }
            }
        }
    }
}
