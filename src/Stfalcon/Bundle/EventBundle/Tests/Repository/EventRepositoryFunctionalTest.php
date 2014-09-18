<?php

namespace Stfalcon\Bundle\EventBundle\Tests\Repository;

use Liip\FunctionalTestBundle\Test\WebTestCase;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;


class EventRepositoryFunctionalTest extends WebTestCase
{
    /**
     * @var \Stfalcon\Bundle\EventBundle\Repository\EventRepository
     */
    private $eventRepository;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp() {

        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->eventRepository = $this->em->getRepository('StfalconEventBundle:Event');

        $connection = $this->em->getConnection();

        $connection->beginTransaction();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $connection->commit();
        $purger   = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->purge();
        $connection->beginTransaction();
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
        $connection->commit();

        $this->loadFixtures(
            [
                '\Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
                '\Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData',
                '\Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
                '\Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadMailQueueData'
            ]
        );

    }


    public function testIsActiveEventForUser()
    {
        // пользователь подписан на PHP Frameworks Day
        $event = $this->em->getRepository('StfalconEventBundle:Event')->findOneBy(['id' => 2 ]);
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 3]);
        $this->assertTrue($this->eventRepository->isActiveEventForUser($event, $user));

        // пользователь подписан на PHP Frameworks Day
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 4]);
        $this->assertTrue($this->eventRepository->isActiveEventForUser($event, $user));

        // пользователь не подписан на Javascript Frameworks Day
        $event = $this->em->getRepository('StfalconEventBundle:Event')->findOneBy(['id' => 4 ]);
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 28]);
        $this->assertFalse($this->eventRepository->isActiveEventForUser($event, $user));
    }

}