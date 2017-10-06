<?php

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Application\Bundle\UserBundle\Entity\User;
use Application\Bundle\UserBundle\Services\UserBalanceService;

class UserBalanceServiceTest extends WebTestCase
{

    public function setUp()
    {
        $this->loadFixtures(
            [
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPaymentData'
            ]
        );
    }

    public function testAddIncomeBalance()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user  = $em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => 'user@fwdays.com']);
        $user2 = $em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => 'jack.sparrow@fwdays.com']);

        $payment = $em->getRepository('StfalconEventBundle:Payment')->findOneBy(['user' => $user]);
        $payment2 = $em->getRepository('StfalconEventBundle:Payment')->findOneBy(['user' => $user2]);

        $user2->setUserReferral($user);
        $payment->setAmount(1000);
        $payment->setFwdaysAmount(50);
        $payment2->setAmount(500);
        $payment2->setFwdaysAmount(50);
        $user->setBalance(200);
        $user2->setBalance(100);
        $em->flush();

        $userBalance = $this->getContainer()->get('stfalcon.user_balance.service');
        $userBalance->setUserBalance($payment, UserBalanceService::INCOME);
        $userBalance->setUserBalance($payment, UserBalanceService::OUTCOME);

        $userBalance->setUserBalance($payment2, UserBalanceService::INCOME);
        $userBalance->setUserBalance($payment2, UserBalanceService::OUTCOME);
        $user2->setUserReferral(null);
        $em->flush();
        $em->refresh($user);
        $em->refresh($user2);

        $this->assertEquals(250, $user->getBalance());
        $this->assertEquals(50, $user2->getBalance());
    }
}
