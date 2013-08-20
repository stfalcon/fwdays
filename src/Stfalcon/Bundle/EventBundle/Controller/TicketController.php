<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Ticket controller
 */
class TicketController extends BaseController
{
    /**
     * Take part in the event. Create new ticket for user
     *
     * @param string $event_slug
     *
     * @return RedirectResponse
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/take-part", name="event_takePart")
     * @Template()
     */
    public function takePartAction($event_slug)
    {
        $em     = $this->getDoctrine()->getManager();
        $event  = $this->getEventBySlug($event_slug);
        $user = $this->get('security.context')->getToken()->getUser();

        // проверяем или у него нет билетов на этот ивент
        $ticket = $this->_findTicketForEventByCurrentUser($event);

        // если нет, тогда создаем билет
        if (is_null($ticket)) {
            $ticket = new Ticket($event, $user);
            $em->persist($ticket);
            $em->flush();
        }

        // переносим на страницу билетов пользователя к хешу /evenets/my#zend-framework-day-2011
        return new RedirectResponse($this->generateUrl('events_my') . '#' . $event->getSlug());
    }

    /**
     * Show only active events of user
     *
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/events/my", name="events_my")
     * @Template()
     */
    public function myAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        /** @var $ticketRepository \Stfalcon\Bundle\EventBundle\Repository\TicketRepository */
        $ticketRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconEventBundle:Ticket');
        $tickets = $ticketRepository->findTicketsOfActiveEventsForUser($user);

        return array('tickets' => $tickets);
    }

    /**
     * Event pay
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @throws \Exception
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/pay", name="event_pay")
     * @Template()
     */
    public function payAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em   = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = $this->_findTicketForEventByCurrentUser($event);

        // Вытягиваем скидку из конфига
        $paymentsConfig = $this->container->getParameter('stfalcon_payment.config');
        $discount = (float) $paymentsConfig['discount'];

        // создаем проплату или апдейтим стоимость уже существующей
        /** @var $payment \Stfalcon\Bundle\PaymentBundle\Entity\Payment */
        if ($payment = $ticket->getPayment()) {
            // здесь может быть проблема. например клиент проплатил через банк и платеж идет к
            // шлюзу несколько дней. если обновить цену в этот момент, то сума платежа
            // может не соответствовать цене

            // @fixme После дискуссии решили раскомментировать следующий код. Так как не нашли простого решения с
            // обновлением цены pending платежа. Все возникшые проблемные ситуации с платежами придется обрабатывать вручную
            $payment->setAmountWithoutDiscount($event->getCost());
            if ($payment->getHasDiscount()) {
                $payment->setAmount($payment->getAmountWithoutDiscount() - $payment->getAmountWithoutDiscount() * $discount);
            } else {
                $payment->setAmount($payment->getAmountWithoutDiscount());
            }

            $em->persist($payment);
        } else {
            // Find paid payments for current user
            $paidPayments = $this->getDoctrine()->getManager()
                ->getRepository('StfalconPaymentBundle:Payment')
                ->findPaidPaymentsForUser($user);

            // Если пользователь имеет оплаченные события, то он получает скидку
            if (count($paidPayments) > 0) {
                $cost = $event->getCost() - $event->getCost() * $discount;
                $hasDiscount = true;
            } else {
                $cost = $event->getCost();
                $hasDiscount = false;
            }

            $payment = new Payment($user, $cost, $hasDiscount);
            $payment->getUser();
            $payment->setAmountWithoutDiscount($event->getCost());
            $em->persist($payment);
            $ticket->setPayment($payment);
            $em->persist($ticket);
        }

        $em->flush();

        return $this->forward(
            'StfalconPaymentBundle:Interkassa:pay',
            array(
                'event' => $event,
                'user' => $user,
                'payment' => $payment
            )
        );
    }

    /**
     * Show event ticket status (for current user)
     *
     * @param Event $event
     *
     * @return array
     *
     * @Template()
     */
    public function statusAction(Event $event)
    {
        $ticket = $this->_findTicketForEventByCurrentUser($event);

        return array(
            'event'  => $event,
            'ticket' => $ticket
        );
    }

    /**
     * Find ticket for event by current user
     *
     * @param Event $event
     *
     * @return Ticket|null
     */
    private function _findTicketForEventByCurrentUser(Event $event)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = null;
        if (is_object($user) && $user instanceof \FOS\UserBundle\Model\UserInterface) {
            // проверяем или у пользователя есть билеты на этот ивент
            $ticket = $this->getDoctrine()->getManager()
                ->getRepository('StfalconEventBundle:Ticket')
                ->findOneBy(
                    array(
                        'event' => $event->getId(),
                        'user'  => $user->getId()
                    )
                );
        }

        return $ticket;
    }

    /**
     * Generating ticket with QR-code to event
     *
     * @param string $event_slug
     *
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/event/{event_slug}/ticket", name="event_ticket_show")
     * @Template()
     */
    public function showAction($event_slug)
    {
        $event  = $this->getEventBySlug($event_slug);
        $ticket = $this->_findTicketForEventByCurrentUser($event);

        if (!$ticket || !$ticket->isPaid()) {
            return new Response('Вы не оплачивали участие в "' . $event->getName() . '"', 402);
        }

        $html = $this->_ticketTemplate($ticket);
        $fileName = 'ticket-' . $event->getSlug() . '.pdf';

        return new Response(
            $this->generatePdfFile($html, $fileName),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attach; filename="' . $fileName . '"'
            )
        );
    }

    /**
     * Check that QR-code is valid, and register ticket
     *
     * @param Ticket $ticket Ticket
     * @param string $hash   Hash
     *
     * @return Response
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/ticket/{ticket}/check/{hash}", name="event_ticket_check")
     */
    public function checkAction(Ticket $ticket, $hash)
    {
        // проверяем хеш
        if ($ticket->getHash() != $hash) {
            // не совпадает хеш билета и хеш в урле
            return new Response('<h1 style="color:red">Невалидный хеш для билета №' . $ticket->getId() .'</h1>', 403);
        }

        // проверяем или билет ещё не отмечен как использованный
        if ($ticket->isUsed()) {
            $timeNow = new \DateTime();
            $timeDiff = $timeNow->diff($ticket->getUpdatedAt());

            return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' был использован ' . $timeDiff->format('%i мин. назад') . '</h1>', 409);
        }

        $em = $this->getDoctrine()->getManager();
        // отмечаем билет как использованный
        $ticket->setUsed(true);
        $em->flush();

        return new Response('<h1 style="color:green">Все ок. Билет №' . $ticket->getId() .' отмечаем как использованный</h1>');
    }

    /**
     * Check that ticket number is valid
     *
     * @return array
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/check/", name="check")
     * @Template()
     */
    public function checkByNumAction()
    {
        $ticketId = $this->getRequest()->get('id');

        if (!$ticketId) {
            return array(
                'action' => $this->generateUrl('check')
            );
        }

        $ticket = $this->getDoctrine()->getManager()->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(array('id' => $ticketId));

        if (is_object($ticket)) {
            $url = $this->generateUrl(
                'event_ticket_check',
                array(
                    'ticket' => $ticket->getId(),
                    'hash'   => $ticket->getHash()
                ),
                true
            );

            return array(
                'action'    => $this->generateUrl('check'),
                'ticketUrl' => $url
            );
        } else {
            return array(
                'message' => 'Not Found',
                'action'  => $this->generateUrl('check')
            );
        }
    }

    /**
     * Create template for ticket invitation
     *
     * @param Ticket $ticket
     *
     * @return string
     */
    private function _ticketTemplate($ticket)
    {
        $twig = $this->get('twig');

        $url = $this->generateUrl(
            'event_ticket_check',
            array(
                'ticket' => $ticket->getId(),
                'hash'   => $ticket->getHash()
            ),
            true
        );

        $qrCode = $this->get('stfalcon_event.qr_code');
        $qrCode->setText($url);
        $qrCode->setSize(105);
        $qrCode->setPadding(0);
        $qrCodeBase64 = base64_encode($qrCode->get());
        $templateContent = $twig->loadTemplate('StfalconEventBundle:Ticket:show_pdf.html.twig');
        $body = $templateContent->render(array(
            'ticket'       => $ticket,
            'qrCodeBase64' => $qrCodeBase64,
            'path'         => realpath($this->container->get('kernel')->getRootDir() . '/../web') . '/'
        ));

        return $body;
    }

    /**
     * Generate PDF-file of ticket
     *
     * @param string $html       HTML to generate pdf
     * @param string $outputFile Name of output file
     *
     * @return mixed
     */
    private function generatePdfFile($html, $outputFile)
    {
        // Override default fonts directory for mPDF
        define('_MPDF_SYSTEM_TTFONTS', realpath($this->container->get('kernel')->getRootDir() . '/../web/fonts/open-sans/') . '/');

        /** @var \TFox\MpdfPortBundle\Service\MpdfService $mPDFService */
        $mPDFService = $this->get('tfox.mpdfport');
        $mPDFService->setAddDefaultConstructorArgs(false);

        $constructorArgs = array(
            'mode'          => 'BLANK',
            'format'        => 'A5-L',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0
        );

        $mPDF = $mPDFService->getMpdf($constructorArgs);

        // Open Sans font settings
        $mPDF->fontdata['opensans'] = array(
            'R'  => 'OpenSans-Regular.ttf',
            'B'  => 'OpenSans-Bold.ttf',
            'I'  => 'OpenSans-Italic.ttf',
            'BI' => 'OpenSans-BoldItalic.ttf',
        );
        $mPDF->sans_fonts[]              = 'opensans';
        $mPDF->available_unifonts[]      = 'opensans';
        $mPDF->available_unifonts[]      = 'opensansI';
        $mPDF->available_unifonts[]      = 'opensansB';
        $mPDF->available_unifonts[]      = 'opensansBI';
        $mPDF->default_available_fonts[] = 'opensans';
        $mPDF->default_available_fonts[] = 'opensansI';
        $mPDF->default_available_fonts[] = 'opensansB';
        $mPDF->default_available_fonts[] = 'opensansBI';

        $mPDF->SetDisplayMode('fullpage');
        $mPDF->WriteHTML($html);
        $pdfFile = $mPDF->Output($outputFile, 'S');

        return $pdfFile;
    }
}
