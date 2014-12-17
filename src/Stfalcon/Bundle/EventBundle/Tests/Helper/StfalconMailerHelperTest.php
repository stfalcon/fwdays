<?php

namespace Stfalcon\Bundle\EventBundle\Tests\Helper;

use Liip\FunctionalTestBundle\Test\WebTestCase;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

class StfalconMailerHelperTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var $mailerHelper \Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper
     */
    private $mailerHelper;


    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $this->mailerHelper = static::$kernel->getContainer()->get('stfalcon_event.mailer_helper');

        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $em \Doctrine\ORM\EntityManager */
        $connection = $em->getConnection();

        $connection->beginTransaction();

        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $connection->commit();

        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);
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

    public function testAllowSendMailForUser() {

        //пользователь подписан на рассылку и участвует в событии
        $mail = $this->em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 1 ]);
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 3]);
        $this->assertTrue($this->mailerHelper->allowSendMailForUser($mail, $user));

        //пользователь не подписан на рассылку и участвует в событии
        $mail = $this->em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 1 ]);
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 3]);
        //отписать пользователя от рассылки
        $user->setSubscribe(false);
        $this->assertTrue($this->mailerHelper->allowSendMailForUser($mail, $user));

        //пользователь не подписан на рассылку и  не участвует в событии
        $mail = $this->em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 1 ]);
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 4]);
        //отписать пользователя от рассылки
        $user->setSubscribe(false);
        $this->assertFalse($this->mailerHelper->allowSendMailForUser($mail, $user));

        //несуществующие mail, user
        $mail = $this->em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 999 ]);
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 999]);
        $this->assertFalse($this->mailerHelper->allowSendMailForUser($mail, $user));
    }

}