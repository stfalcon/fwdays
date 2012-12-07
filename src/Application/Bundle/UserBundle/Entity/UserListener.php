<?php
namespace Application\Bundle\UserBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * Doctrine ORM listener
 */
class UserListener
{
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

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->handleEvent($args);
    }

    private function handleEvent(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof UserInterface) {
            $session = $this->container->get('session');
            //$session->setFlash($session->getFlash());

            if ($args->hasChangedField('confirmationToken') &&
                $args->hasChangedField('enabled') &&
                $args->getNewValue('confirmationToken') == null &&
                $args->getNewValue('enabled') == 1 &&
                $session->get('activ') != 1
            ) {
                $session->set('activ', 1);
                $activeEvents = $this->container->get('doctrine')->getEntityManager()
                    ->getRepository('StfalconEventBundle:Event')
                    ->findBy(array('active' => true));

                $em = $this->container->get('doctrine')->getEntityManager();
                // Подписуем пользователя на все активные евенты
                foreach ($activeEvents as $activeEvent) {
                    $ticket = new Ticket($activeEvent, $entity);
                    $em->persist($ticket);
                    $em->flush();
                }
            }
        }
    }
}