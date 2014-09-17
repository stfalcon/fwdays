<?php

namespace Stfalcon\Bundle\EventBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Stfalcon\Bundle\EventBundle\Repository\EventRepository;


class EventRepositoryFunctionalTest extends WebTestCase
{
    /**
     * @var \Stfalcon\Bundle\EventBundle\Repository\EventRepository
     */
    private $eventRepository;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    public function setUp() {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->_em = $kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $this->eventRepository = $this->_em
            ->getRepository('StfalconEventBundle:Event');
    }


    public function testIsActiveEventForUser()
    {
        // пользователь подписан на PHP Frameworks Day
        $event = $this->_em->getRepository('StfalconEventBundle:Event')->findOneBy(['id' => 6 ]);
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 109]);
        $this->assertTrue($this->eventRepository->isActiveEventForUser($event, $user));

        // пользователь подписан на PHP Frameworks Day
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 110]);
        $this->assertTrue($this->eventRepository->isActiveEventForUser($event, $user));

        // пользователь не подписан на Javascript Frameworks Day
        $event = $this->_em->getRepository('StfalconEventBundle:Event')->findOneBy(['id' => 8 ]);
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 133]);
        $this->assertFalse($this->eventRepository->isActiveEventForUser($event, $user));
    }

}