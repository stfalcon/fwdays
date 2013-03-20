<?php


namespace Application\Bundle\UserBundle\Entity;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * Doctrine ORM listener subscribe user to event
 *
 *
 */
class UserListener implements EventSubscriber
{
    /**
     * @var \FOS\UserBundle\Model\UserManagerInterface
     */
    private $userManager;

    private $eventRepository;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleEvent($args);
    }

    private function handleEvent(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof UserInterface) {
            $activeEvents = $this->container->get('doctrine')->getManager()
                ->getRepository('StfalconEventBundle:Event')
                ->findBy(array('active' => true ));

            $em = $this->container->get('doctrine')->getManagerForClass('StfalconEventBundle:Ticket');
            // Подписуем пользователя на все активные евенты
            foreach ($activeEvents as $activeEvent) {
                $ticket = new Ticket($activeEvent, $entity);
                $em->persist($ticket);
                $em->flush();
            }

        }
    }
}