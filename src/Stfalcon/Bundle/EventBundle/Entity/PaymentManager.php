<?php

namespace Stfalcon\Bundle\EventBundle\Entity;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

class PaymentManager {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @param EntityManager $entityManager
     * @param string $class
     * @param $container
     */
    public function __construct(EntityManager $entityManager, $class, $container)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository($class);
        $this->container = $container;
    }

    /**
     * Flush
     */
    public function flush()
    {
        $this->entityManager->flush();
    }


    /**
     * Пересчитываем итоговую сумму платежа по всем билетам
     * с учетом скидки
     *
     * @param Payment $payment
     * @param $eventCost
     */
    public function checkTicketsPricesInPayment($payment, $eventCost)
    {
        // Вытягиваем скидку из конфига
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discount = (float)$paymentsConfig['discount'];

        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            // получаем оплаченые платежи пользователя
            $paidPayments = $this->repository->findPaidPaymentsForUser($ticket->getUser());

            //правильно ли установлен флаг наличия скидки
            $isCorrectDiscount = $ticket->getHasDiscount() == ((count($paidPayments) > 0) || $ticket->hasPromoCode());

            // если цена билета без скидки не ровна новой цене на ивент
            // или неверно указан флаг наличия скидки
            if (($ticket->getAmountWithoutDiscount() != $eventCost) || !$isCorrectDiscount) {
                // если не правильно установлен флаг наличия скидки, тогда устанавливаем его заново
                if (!$isCorrectDiscount) {
                    $ticket->setHasDiscount(((count($paidPayments) > 0) || $ticket->hasPromoCode()));
                }

                $ticket->setAmountWithoutDiscount($eventCost);
                if ($ticket->getHasDiscount()) {
                    $ticket->setAmountWithDiscount($discount);
                } else {
                    $ticket->setAmount($eventCost);
                }
                $this->entityManager->merge($ticket);
            }
        }

        $payment->recalculateAmount();
        //set base price
        $payment->setBaseAmount($payment->getAmount());

        $this->entityManager->merge($payment);
        $this->flush();
    }

} 