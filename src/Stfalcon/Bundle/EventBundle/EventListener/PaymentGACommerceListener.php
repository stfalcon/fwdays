<?php

namespace Stfalcon\Bundle\EventBundle\EventListener;

use Application\Bundle\DefaultBundle\Service\GACommerce;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Stfalcon\Bundle\EventBundle\Entity\Payment,
    Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Application\Bundle\UserBundle\Entity\User;

use Symfony\Component\DependencyInjection\Container;

class PaymentGACommerceListener
{
    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var GACommerce $gacommerce
     */
    private $gacommerce;

    /**
     * @param Container  $container
     * @param GACommerce $gacommerce
     */
    public function __construct($container, $gacommerce)
    {
        $this->container  = $container;
        $this->gacommerce = $gacommerce;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Payment) {
            if ($entity->getStatus() === Payment::STATUS_PAID) {

                $tickets = $this->container->get('doctrine')
                    ->getManager()
                    ->getRepository('StfalconEventBundle:Ticket')
                    ->getAllTicketsByPayment($entity);

                //send GA transaction
                $this->gacommerce->sendTransaction(
                    $entity->getUser()->getId(),
                    $entity->getId(),
                    $entity->getAmount()
                );


                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    /** @var $user User */
                    $user = $ticket->getUser();

                    /** @var Event $event */
                    $event = $ticket->getEvent();

                    $itemName = 'Оплата участия в конференции '
                        . $event->getName()
                        . '. Плательщик '
                        . $entity->getUser()->getFullname()
                        . ' (#' . $entity->getUser()->getId()
                        . ')'
                        . ', участник '
                        . $user->getFullname()
                        . ' (#' . $user->getId()
                        . ');'
                    ;

                    //send GA item
                    $this->gacommerce->sendItem(
                        $entity->getUser()->getId(),
                        $entity->getId(),
                        $itemName,
                        $ticket->getAmount()
                    );
                }
            }
        }
    }
}