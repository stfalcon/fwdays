<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\PaymentBundle\Entity\Payment;
use Symfony\Component\Form\FormError;

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
            $this->createTicket($event, $user);
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
        // @todo WTF? був маленький акуратний екшн
        // https://github.com/stfalcon/fwdays/blob/7f1be58c4c7d33d8fe6dd4765a35a0733a55dd5a/src/Stfalcon/Bundle/EventBundle/Controller/TicketController.php#L85

        $event = $this->getEventBySlug($event_slug);
        $paymentsConfig = $this->container->getParameter('stfalcon_payment.config');
        $discountAmount = 100 * (float) $paymentsConfig['discount'];

        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em   = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $ticket = $this->_findTicketForEventByCurrentUser($event);

        // создаем проплату или апдейтим стоимость уже существующей
        /** @var $payment \Stfalcon\Bundle\PaymentBundle\Entity\Payment */
        if (!$payment = $ticket->getPayment()) {
            $payment = new Payment();
            $payment->setUser($user);
            $payment->addTicket($ticket);
            $em->persist($payment);
            $em->persist($ticket);
            $em->flush();
        }

        if (!$payment->isPaid()) {
            $this->checkTicketsPricesInPayment($payment, $event->getCost());
        }

        $promoCodeForm = $this->createForm('stfalcon_event_promo_code');
        $promoCode = $payment->getPromoCodeFromTickets();
        $request = $this->getRequest();
        if ($request->isMethod('post')) {
            $promoCodeForm->bind($request);
            $code = $promoCodeForm->get('code')->getData();
            $promoCode = $em->getRepository('StfalconEventBundle:PromoCode')->findActivePromoCodeByCodeAndEvent($code, $event);
            if ($promoCode) {
                $notUsedPromoCode = $payment->addPromoCodeForTickets($promoCode, $discountAmount);
                $em->flush();
                if (!empty($notUsedPromoCode)) {
                    $this->get('session')->getFlashBag()->add('not_used_promocode', implode(', ', $notUsedPromoCode));
                }
            } else {
                $promoCodeForm->get('code')->addError(new FormError('Такой промокод не найден'));
            }
        }

        $ticketForm = $this->createForm('stfalcon_event_ticket');


        return $this->forward(
            'StfalconPaymentBundle:Interkassa:pay',
            array(
                'event' => $event,
                'user' => $user,
                'payment' => $payment,
                'promoCodeFormView' => $promoCodeForm->createView(),
                'promoCode' => $promoCode,
                'ticketFormView' => $ticketForm->createView(),
                'discountAmount' => $discountAmount
            )
        );
    }

    /**
     * @param string  $slug
     * @param Payment $payment
     *
     * @return RedirectResponse
     *
     * @Route("/event/{slug}/payment/{id}/participants/add", name="add_participants_to_payment")
     */
    public function addParticipantsToPaymentAction($slug, Payment $payment)
    {
        // @todo це мало порефакторитись а не тупо перенести кусок гавнокоду з одного місця в інше
        $event = $this->getEventBySlug($slug);
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ticketForm = $this->createForm('stfalcon_event_ticket');
        $ticketForm->bind($request);

        $participants = $ticketForm->get('participants')->getData();
        $alreadyPaidTickets = array();

        foreach ($participants as $participant) {
            $user = $this->get('fos_user.user_manager')->findUserBy(array('email' => $participant['email']));

            // создаем нового пользователя
            if (!$user) {
                $user = $this->get('fos_user.user_manager')->createUser();
                $user->setEmail($participant['email']);
                $user->setFullname($participant['name']);

                // генерация временного пароля
                $password = substr(str_shuffle(md5(time())), 5, 8);
                $user->setPlainPassword($password);
                $user->setEnabled(true);

                $this->get('fos_user.user_manager')->updateUser($user);

                // отправляем сообщение о регистрации
                $text = "Приветствуем " . $user->getFullname() ."!

Вы были автоматически зарегистрированы на сайте Frameworks Days.

Ваш временный пароль: " . $password . "
Его можно сменить на странице " . $this->generateUrl('fos_user_change_password', array(), true) . "


---
С уважением,
Команда Frameworks Days";

                $message = \Swift_Message::newInstance()
                    ->setSubject("Регистрация на сайте Frameworks Days")
                    // @todo refact
                    ->setFrom('orgs@fwdays.com', 'Frameworks Days')
                    ->setTo($user->getEmail())
                    ->setBody($text);

                // @todo каждый вызов отнимает память
                $this->get('mailer')->send($message);
            }
            // проверяем или у него нет билетов на этот ивент
            /** @var Ticket $ticket */
            $ticket = $em->getRepository('StfalconEventBundle:Ticket')
                ->findOneBy(array('event' => $event->getId(), 'user' => $user->getId()));

            if (!$ticket) {
                $ticket = $this->createTicket($event, $user);
            }

            if (!$ticket->isPaid()) {
                if ($promoCode = $payment->getPromoCodeFromTickets()) {
                    if (!$ticket->getHasDiscount()) {
                        $ticket->setPromoCode($promoCode);
                    }
                }
                $payment->addTicket($ticket);
            } else {
                $alreadyPaidTickets[] = $user->getFullname();
            }
            $em->persist($payment);
            $em->persist($ticket);
        }
        $em->flush();
        if (!empty($alreadyPaidTickets)) {
            $this->get('session')->getFlashBag()->add('already_paid_tickets', implode(', ', $alreadyPaidTickets));
        }

        return $this->redirect($this->generateUrl('event_pay', array('event_slug' => $event->getSlug())));
    }

    /**
     * @param string $event_slug
     * @param int    $payment_id
     * @param Ticket $ticket
     *
     * @return array
     * @throws NotFoundHttpException
     *
     * @Route("/event/{event_slug}/payment/{payment_id}/ticket/remove/{id}", name="remove_ticket_from_payment")
     */
    public function removeTicketFromPaymentAction($event_slug, $payment_id, Ticket $ticket)
    {
        // @todo що за метод і нафіга? чому нема коментарів?
        $event = $this->getEventBySlug($event_slug);

        $em = $this->getDoctrine()->getManager();
        $paymentRepository = $em->getRepository('StfalconPaymentBundle:Payment');
        $payment = $paymentRepository->find($payment_id);
        if (!$payment) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $payment->removeTicket($ticket);
        $em->remove($ticket);
        $em->flush();

        return $this->redirect($this->generateUrl('event_pay', array('event_slug' => $event->getSlug())));
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
        // @todo в сервіс
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

        /** @var $pdfGen \Stfalcon\Bundle\EventBundle\Helper\PdfGeneratorHelper */
        $pdfGen = $this->get('stfalcon_event.pdf_generator.helper');
        // @todo чому зразу не передати ticket в generatePdfFile? і не генерувати html в екшені
        $html = $pdfGen->generateHTML($ticket);
        $fileName = 'ticket-' . $event->getSlug() . '.pdf';

        return new Response(
            $pdfGen->generatePdfFile($html, $fileName),
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
     * @Route("/ticket/{ticket}/check/{hash}", name="event_ticket_check")
     */
    public function checkAction(Ticket $ticket, $hash)
    {
        // @todo ця вся (майже вся) логіка чудно виноситься в вьюшку
        // проверяем хеш
        if ($ticket->getHash() != $hash) {
            // не совпадает хеш билета и хеш в урле
            return new Response('<h1 style="color:red">Невалидный хеш для билета №' . $ticket->getId() .'</h1>', 403);
        }

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('event_show', array('event_slug' => $ticket->getEvent()->getSlug())));
        }

        // проверяем существует ли оплата
        if ($ticket->getPayment() instanceof Payment) {
            // проверяем оплачен ли билет
            if ($ticket->getPayment()->isPaid()) {
                // проверяем или билет ещё не отмечен как использованный
                if ($ticket->isUsed()) {
                    $timeNow = new \DateTime();
                    $timeDiff = $timeNow->diff($ticket->getUpdatedAt());

                    return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' был использован ' . $timeDiff->format('%i мин. назад') . '</h1>', 409);
                }
            } else {
                return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' не оплачен' . '</h1>');
            }
        } else {
            return new Response('<h1 style="color:orange">Билет №' . $ticket->getId() . ' оплата не существует' . '</h1>');
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
        // @todo це було тимчасове рішення для адміна. треба винести в адмінку
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
     * @param Event $event
     * @param User  $user
     *
     * @return Ticket
     */
    private function createTicket($event, $user)
    {
        // @todo це в сервісі мало б бути
        $em = $this->getDoctrine()->getManager();
        // Вытягиваем скидку из конфига
        $paymentsConfig = $this->container->getParameter('stfalcon_payment.config');
        $discount = (float) $paymentsConfig['discount'];

        $ticket = new Ticket();
        $ticket->setEvent($event);
        $ticket->setUser($user);
        $ticket->setAmountWithoutDiscount($event->getCost());
        $paidPayments = $em->getRepository('StfalconPaymentBundle:Payment')
            ->findPaidPaymentsForUser($user);

        // Если пользователь имеет оплаченные события, то он получает скидку
        if (count($paidPayments) > 0) {
            $cost = $event->getCost() - $event->getCost() * $discount;
            $hasDiscount = true;
        } else {
            $cost = $event->getCost();
            $hasDiscount = false;
        }
        $ticket->setAmount($cost);
        $ticket->setHasDiscount($hasDiscount);

        $em->persist($ticket);
        $em->flush();

        return $ticket;
    }

    /**
     * @param Payment $payment
     * @param float   $newPrice
     */
    private function checkTicketsPricesInPayment($payment, $newPrice)
    {
        // @todo це що за хрєнь? де коментарі з яких все має бути зрозуміло?
        $em = $this->getDoctrine()->getManager();
        // Вытягиваем скидку из конфига
        $paymentsConfig = $this->container->getParameter('stfalcon_payment.config');
        $discount = (float) $paymentsConfig['discount'];
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            // получаем оплаченые платежи пользователя
            $paidPayments = $em->getRepository('StfalconPaymentBundle:Payment')
                ->findPaidPaymentsForUser($ticket->getUser());
            // если цена билета без скидки не ровна новой цене на ивент
            // или неверно указан флаг наличия скидки
            if ($ticket->getAmountWithoutDiscount() != $newPrice ||
                ($ticket->getHasDiscount() != ((count($paidPayments) > 0) || $ticket->hasPromoCode()))
            ) {
                // если не правильно установлен флаг наличия скидки, тогда устанавливаем его заново
                if ($ticket->getHasDiscount() != ((count($paidPayments) > 0) || $ticket->hasPromoCode())) {
                    $ticket->setHasDiscount(((count($paidPayments) > 0) || $ticket->hasPromoCode()));
                }
                $ticket->setAmountWithoutDiscount($newPrice);
                if ($ticket->getHasDiscount()) {
                    if ($promoCode = $ticket->getPromoCode()) {
                        $cost = $newPrice - ($newPrice * ($promoCode->getDiscountAmount() / 100));
                    } else {
                        $cost = $newPrice - ($newPrice * $discount);
                    }
                    $ticket->setAmount($cost);
                } else {
                    $ticket->setAmount($newPrice);
                }
                $em->merge($ticket);
            }
        }
        $payment->recalculateAmount();
        $em->merge($payment);
        $em->flush();
    }
}
