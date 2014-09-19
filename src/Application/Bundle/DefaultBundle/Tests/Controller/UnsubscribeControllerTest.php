<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

class CategoryControllerTest extends WebTestCase {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp() {

        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadNewsData());
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());

        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $em \Doctrine\ORM\EntityManager */
        $connection = $em->getConnection();

        $connection->beginTransaction();

        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $connection->commit();

        $purger   = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();

        $connection->beginTransaction();
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
        $connection->commit();

        $executor->execute($loader->getFixtures(), true);
    }

    public function testUnsubscribe() {
        $client = static::createClient();

        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 5]);
        $url  = static::$kernel->getContainer()->get('router')->generate('unsubscribe',
            [
                'hash' => $user->getSalt(),
                'userId' => $user->getId()
            ]);

        $client->request('GET', $url);

        $this->em->refresh($user);
        $this->assertFalse($user->isSubscribe());
    }
}