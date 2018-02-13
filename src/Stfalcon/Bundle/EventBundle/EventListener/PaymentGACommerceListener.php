<?php

namespace Stfalcon\Bundle\EventBundle\EventListener;

use Application\Bundle\DefaultBundle\Service\GACommerce;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Application\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class PaymentGACommerceListener.
 */
class PaymentGACommerceListener
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var GACommerce
     */
    private $gacommerce;

    /** @var string */
    private $environment;

    /** @var array */
    protected $itemVariants = ['javascript', 'php', 'frontend', 'highload', 'net.'];

    /**
     * @param Container  $container
     * @param GACommerce $gacommerce
     * @param string     $environment
     */
    public function __construct($container, $gacommerce, $environment)
    {
        $this->container = $container;
        $this->gacommerce = $gacommerce;
        $this->environment = $environment;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Payment && 'prod' === $this->environment) {
            if (Payment::STATUS_PAID === $entity->getStatus()) {
                $tickets = $this->container->get('doctrine')
                    ->getManager()
                    ->getRepository('StfalconEventBundle:Ticket')
                    ->getAllTicketsByPayment($entity);

                $eventName = count($tickets) > 0 ? $tickets[0]->getEvent()->getName() : '';
                //send GA transaction
                $this->gacommerce->sendTransaction(
                    $entity->getUser()->getId(),
                    $entity->getId(),
                    $entity->getAmount(),
                    $this->getItemVariant($eventName)
                );

                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    /** @var $user User */
                    $user = $ticket->getUser();

                    /** @var Event $event */
                    $event = $ticket->getEvent();

                    $itemName = 'Оплата участия в конференции '
                        .$event->getName()
                        .'. Плательщик '
                        .$entity->getUser()->getFullname()
                        .' (#'.$entity->getUser()->getId()
                        .')'
                        .', участник '
                        .$user->getFullname()
                        .' (#'.$user->getId()
                        .');'
                    ;

                        $priceBlockName = null === $ticket->getTicketCost() ? 'admin' : $ticket->getTicketCost()->getName();

                    //send GA item
                    $this->gacommerce->sendItem(
                        $entity->getUser()->getId(),
                        $entity->getId(),
                        $itemName,
                        $ticket->getAmount(),
                        $this->getItemVariant($event->getName()),
                        $priceBlockName
                    );
                }
            }
        }
    }

    /**
     * @param string $eventName
     *
     * @return string
     */
    private function getItemVariant($eventName)
    {
        foreach ($this->itemVariants as $itemVariant) {
            $pattern = '/'.$itemVariant.'/';
            if (preg_match($pattern, strtolower($eventName))) {
                return $itemVariant;
            }
        }

        return $eventName;
    }
}
