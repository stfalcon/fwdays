<?php

namespace App\Tests\Listener;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Tests\BaseFunctionalTest\AbstractBaseFunctionalTest;

class PaymentServiceTest extends AbstractBaseFunctionalTest
{
    public function testPayByFwdaysAmount()
    {
        $fwdaysAmount = 3000;

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'jack.sparrow@fwdays.com']);
        self::assertInstanceOf(User::class, $user);
        $user->setBalance($fwdaysAmount);

        /** @var Event $event */
        $event = $this->em->getRepository(Event::class)->findOneBy(['slug' => 'php-day-2017']);
        self::assertInstanceOf(Event::class, $event);

        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->em->getRepository(Ticket::class);
        /** @var Ticket $ticket */
        $ticket = $ticketRepository->findOneByUserAndEventWithPendingPayment($user, $event);
        self::assertInstanceOf(Ticket::class, $ticket);

        /** @var Payment $payment */
        $payment = $ticket->getPayment();
        self::assertInstanceOf(Payment::class, $payment);

        $referralBalance = $user->getBalance();
        $paymentService = $this->getContainer()->get(PaymentService::class);
        $paymentService->checkTicketsPricesInPayment($payment, $event);
        $paymentService->addFwdaysBonusToPayment($payment, $payment->getAmount());
        $paymentService->setPaidByBonusMoney($payment, $event);

        self::assertEquals(0, $payment->getAmount());
        self::assertEquals($payment->getBaseAmount(), $ticket->getAmountWithoutDiscount());
        self::assertEquals($payment->getFwdaysAmount(), $ticket->getAmount());
        self::assertTrue($payment->isPaid());

        self::assertEquals($referralBalance - $ticket->getAmount(), $user->getBalance());
    }
}
