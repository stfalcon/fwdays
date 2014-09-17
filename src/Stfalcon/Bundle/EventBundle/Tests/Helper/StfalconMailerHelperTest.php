<?php

namespace Stfalcon\Bundle\EventBundle\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Stfalcon\Bundle\EventBundle\Repository\EventRepository;


class StfalconMailerHelperTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * @var $mailerHelper \Stfalcon\Bundle\EventBundle\Helper\StfalconMailerHelper
     */
    private $mailerHelper;

    public function setUp() {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->_em = $kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $this->mailerHelper = $kernel->getContainer()->get('stfalcon_event.mailer_helper');
    }

    public function testAllowSendMailForUser() {

        //пользователь подписан на рассылку и участвует в событии
        $mail = $this->_em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 2 ]);
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 109]);
        $this->assertTrue($this->mailerHelper->allowSendMailForUser($mail, $user));

        //пользователь не подписан на рассылку и участвует в событии
        $mail = $this->_em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 2 ]);
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 109]);
        //отписать пользователя от рассылки
        $user->setSubscribe(false);
        $this->assertTrue($this->mailerHelper->allowSendMailForUser($mail, $user));

        //пользователь не подписан на рассылку и  не участвует в событии
        $mail = $this->_em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 2 ]);
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 107]);
        //отписать пользователя от рассылки
        $user->setSubscribe(false);
        $this->assertFalse($this->mailerHelper->allowSendMailForUser($mail, $user));

        //несуществующие mail, user
        $mail = $this->_em->getRepository('StfalconEventBundle:Mail')->findOneBy(['id' => 999 ]);
        $user = $this->_em->getRepository('ApplicationUserBundle:User')->findOneBy(['id' => 999]);
        $this->assertFalse($this->mailerHelper->allowSendMailForUser($mail, $user));
    }

}