<?php

namespace Stfalcon\Bundle\EventBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Stfalcon\Bundle\EventBundle\Entity\Payment,
    Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\EventBundle\Entity\Mail,
    Application\Bundle\UserBundle\Entity\User;

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
            if ($entity->getStatus() === Payment::STATUS_PAID) {

                /** @var Ticket $ticket */
                foreach ($entity->getTickets() as $ticket) {
                    /** @var $user User */
                    $user = $ticket->getUser();

                    /** @var Event $event */
                    $event = $ticket->getEvent();

                    $successPaymentTemplateContent = $this->mailerHelper->renderTwigTemplate(
                        'StfalconEventBundle:Interkassa:_mail.html.twig',
                        [
                            'user' => $user,
                            'event' => $event,
                        ]
                    );

                    $mail = new Mail();
                    $mail->addEvent($event);
                    $mail->setText($successPaymentTemplateContent);

                    $html = $this->pdfGeneratorHelper->generateHTML($ticket);
                    $message = $this->mailerHelper->formatMessage($user, $mail, true);
                    $message->setSubject($event->getName())
                        ->attach(
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
