<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Entity\Mail;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Helper\MailerHelper;
use App\Helper\PdfGeneratorHelper;
use App\Repository\TicketRepository;
use App\Service\PaymentService;
use App\Service\TranslatedMailService;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * PaymentListener.
 */
class PaymentListener
{
    private $mailerHelper;
    private $pdfGeneratorHelper;
    private $mailer;
    private $paymentService;
    private $translatedMailService;
    private $ticketRepository;
    private $locale;

    /** @var bool */
    private $runPaymentPostUpdate = true;

    /**
     * @param string                $locale
     * @param MailerHelper          $mailerHelper
     * @param PdfGeneratorHelper    $pdfGeneratorHelper
     * @param \Swift_Mailer         $mailer
     * @param PaymentService        $paymentService
     * @param TranslatedMailService $translatedMailService
     * @param TicketRepository      $ticketRepository
     */
    public function __construct(string $locale, MailerHelper $mailerHelper, PdfGeneratorHelper $pdfGeneratorHelper, \Swift_Mailer $mailer, PaymentService $paymentService, TranslatedMailService $translatedMailService, TicketRepository $ticketRepository)
    {
        $this->locale = $locale;
        $this->mailerHelper = $mailerHelper;
        $this->pdfGeneratorHelper = $pdfGeneratorHelper;
        $this->mailer = $mailer;
        $this->paymentService = $paymentService;
        $this->translatedMailService = $translatedMailService;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @param bool $runPaymentPostUpdate
     */
    public function setRunPaymentPostUpdate(bool $runPaymentPostUpdate): void
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
        $payment = $args->getEntity();
        if ($payment instanceof Payment && Payment::STATUS_PAID === $payment->getStatus() && $this->runPaymentPostUpdate) {
            $this->paymentService->setTicketsCostAsSold($payment);
            $this->paymentService->calculateTicketsPromocode($payment);

            $tickets = $this->ticketRepository->getAllTicketsByPayment($payment);

            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                /** @var $user User */
                $user = $ticket->getUser();

                /** @var Event $event */
                $event = $ticket->getEvent();

                $mail = new Mail();
                $mail->addEvent($event);

                $translatedMails = $this->translatedMailService->getTranslatedMailArray($mail);

                $html = $this->pdfGeneratorHelper->generateHTML($ticket);
                $message = $this->mailerHelper->formatMessage($user, $translatedMails[$this->locale], false, true);

                $message->setSubject($event->getName());
                $message->attach(
                    new \Swift_Attachment(
                        $this->pdfGeneratorHelper->generatePdfFile($ticket, $html),
                        $ticket->generatePdfFilename()
                    )
                );

                $this->mailer->send($message);
            }
        }
    }
}
