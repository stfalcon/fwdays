<?php

namespace Stfalcon\Bundle\EventBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Stfalcon\Bundle\EventBundle\Entity\Payment,
    Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\EventBundle\Entity\Mail,
    Application\Bundle\UserBundle\Entity\User;
use Application\Bundle\DefaultBundle\Service\PaymentService;

class PaymentListener
{

    /** @var \Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper $mailerHelper */
    private $mailerHelper;

    /** @var \Stfalcon\Bundle\EventBundle\Helper\PdfGeneratorHelper $pdfGeneratorHelper */
    private $pdfGeneratorHelper;

    /** @var \Swift_Mailer $mailer */
    private $mailer;

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->mailer = $this->container->get('mailer');
        $this->mailerHelper = $this->container->get('stfalcon_event.mailer_helper');
        $this->pdfGeneratorHelper = $this->container->get('stfalcon_event.pdf_generator.helper');

        if ($entity instanceof Payment) {
            if (Payment::STATUS_PAID === $entity->getStatus()) {
                /** @var PaymentService $paymentService */
                $paymentService = $this->container->get('stfalcon_event.payment.service');
                $paymentService->setTicketsCostAsSold($entity);
                $paymentService->calculateTicketsPromocode($entity);
                $em = $this->container->get('doctrine.orm.entity_manager');

                $tickets = $em->getRepository('StfalconEventBundle:Ticket')
                    ->getAllTicketsByPayment($entity);

                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    /** @var $user User */
                    $user = $ticket->getUser();

                    /** @var Event $event */
                    $event = $ticket->getEvent();

                    $successPaymentTemplateContent = $this->mailerHelper->renderTwigTemplate(
                        'ApplicationDefaultBundle:Interkassa:_mail.html.twig',
                        [
                            'user' => $user,
                            'event' => $event,
                        ]
                    );

                    $mail = new Mail();
                    $mail->addEvent($event);
                    $mail->setText($successPaymentTemplateContent);

                    $html = $this->pdfGeneratorHelper->generateHTML($ticket);
                    $message = $this->mailerHelper->formatMessage($user, $mail);

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
