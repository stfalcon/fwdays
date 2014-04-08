<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

class PaymentController extends BaseController {
    
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
     * @ParamConverter("event", options={"mapping": {"event_slug": "slug"}})
     * @Template()
     */
    public function payAction(Event $event)
    {
        if (!$event->getReceivePayments()) {
            throw new \Exception("Оплата за участие в {$event->getName()} не принимается.");
        }

        $em   = $this->getDoctrine()->getManager();
        /* @var $user User */
        $user = $this->container->get('security.context')->getToken()->getUser();
        /* @var $ticket Ticket */
        $ticket = $this->container->get('stfalcon_event.ticket.service')
                ->findTicketForEventByCurrentUser($event);
        
        // если нет привязанного платежа, тогда создаем новый
//        if (!$ticket->hasPayment()) {
//            
//            $payment = new Payment();
//            $payment->setUser($user);
//            $em->persist($payment);
//            $ticket->setPayment($payment);
//            $em->persist($ticket);
//        } else {
//            $payment = $ticket->getPayment();
            // если билет оплачен, тогда форвардим на загрузку билета
//            if ($payment->isPaid()) {
//                $this->forward('StfalconEventBundle:Ticket:download');
//            }
//            
//            // если изменилась цена участия тогды выводим предупреждение и кнопку пересчета
//            // т.к. после пересчета может возникнуть проблема с платежами по старой цене (которые обрабатываются с задержкой)
//            if ($event->getCost() != $ticket->getAmountWithoutDiscount()) {
//                // @todo тут пересчитывать нужно не для одного билета, 
//                // а для всех билетов, которые входят в платеж?
//                // кейс когда кто-то хочет за меня заплатить, а я хочу платить сам не рассматриваем?
//                $ticket->setAmount($event->getCost());
//            }
//        }

        if (!$payment = $ticket->getPayment()) {
            $payment = new Payment();
            $payment->setUser($user);
            $em->persist($payment);
            $ticket->setPayment($payment);
            $em->persist($ticket);
            $em->flush();
        }
        // процент скидки для постоянных участников
        // @todo здесь этого не нужно
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discountAmount = 100 * (float) $paymentsConfig['discount'];

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


        $config = $this->container->getParameter('stfalcon_event.config');

        $description = 'Оплата участия в конференции ' . $event->getName()
                       . '. Плательщик ' . $user->getFullname() . ' (#' . $user->getId() . ')';

        /** @var InterkassaService $interkassa */
        $interkassa = $this->container->get('stfalcon_event.interkassa.service');

        $params['ik_co_id'] = $config['interkassa']['shop_id'];
        $params['ik_am']    = $payment->getAmount();
        $params['ik_pm_no'] = $payment->getId();
        $params['ik_desc']  = $description;
        $params['ik_loc']   = 'ru';

        $data = array(
            'ik_co_id' => $config['interkassa']['shop_id'],
            'ik_desc'  => $description,
            'ik_sign'  => $interkassa->getSignHash($params)
        );

        return array(
            'data'           => $data,
            'event'          => $event,
            'payment'        => $payment,
            'promoCodeForm'  => $promoCodeForm->createView(),
            'promoCode'      => $promoCode,
            'ticketForm'     => $ticketForm->createView(),
            'discountAmount' => $discountAmount
        );
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
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discount = (float) $paymentsConfig['discount'];
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            // получаем оплаченые платежи пользователя
            $paidPayments = $em->getRepository('StfalconEventBundle:Payment')
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
                $ticket = $this->container->get('stfalcon_event.ticket.service')
                    ->createTicket($event, $user);
            }

            if (!$ticket->isPaid()) {
                if ($promoCode = $payment->getPromoCodeFromTickets()) {
                    if (!$ticket->getHasDiscount()) {
                        $ticket->setPromoCode($promoCode);
                    }
                }
                $ticket->setPayment($payment);
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
        $paymentRepository = $em->getRepository('StfalconEventBundle:Payment');
        $payment = $paymentRepository->find($payment_id);
        if (!$payment) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        // а якщо чувак оплатив квиток?
        $payment->removeTicket($ticket);
        $em->remove($ticket);
        $em->flush();

        return $this->redirect($this->generateUrl('event_pay', array('event_slug' => $event->getSlug())));
    }
    
}