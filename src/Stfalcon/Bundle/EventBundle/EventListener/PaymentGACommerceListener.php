<?php

namespace Stfalcon\Bundle\EventBundle\EventListener;

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
     * @var \Stfalcon\Bundle\EventBundle\Service\GACommerce $gacommerce
     */
    private $gacommerce;
    /**
     * @var string
     */
    private $environment;
    /**
     * @param Container                                       $container
     * @param \Stfalcon\Bundle\EventBundle\Service\GACommerce $gacommerce
     * @param string $environment
     */
    public function __construct($container, $gacommerce, $environment)
    {
        $this->container  = $container;
        $this->gacommerce = $gacommerce;
        $this->environment = $environment;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Payment && 'prod' === $this->environment) {
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