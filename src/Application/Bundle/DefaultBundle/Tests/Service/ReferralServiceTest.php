<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Prophecy\Prophet;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;

/**
 * Class ReferralServiceTest.
 */
class ReferralServiceTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    /** @var EntityManager */
    protected $em;

    /** @var Prophet */
    protected $prophet;

    /** set up fixtures */
    public function setUp()
    {
        $this->prophet = new Prophet();
        $connection = $this->getContainer()->get('doctrine')->getConnection();

        $connection->exec('SET FOREIGN_KEY_CHECKS=0;');
        $connection->exec('DELETE FROM users;');
        $connection->exec('SET FOREIGN_KEY_CHECKS=1;');

        $this->loadFixtures(
            [
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPaymentData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData',
            ],
            null,
            'doctrine',
            ORMPurger::PURGE_MODE_DELETE
        );
        $this->client = self::createClient();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /** destroy */
    public function tearDown()
    {
        $this->prophet->checkPredictions();
        parent::tearDown();
    }

    /**
     * Check referral got bonus and payer lost fwdays_amount.
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function testReferralGetAmount()
    {
        $fwdaysAmount = 300;

        /** @var User $userReferral */
        $userReferral = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => 'user@fwdays.com']);
        /** @var User $user */
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => 'jack.sparrow@fwdays.com']);
        $user->setBalance($fwdaysAmount);

        $this->assertEquals($user->getUserReferral(), $userReferral);

        /** @var Event $event */
        $event = $this->em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => 'php-day-2017']);
        /** @var Ticket $ticket */
        $ticket = $this->em->getRepository('StfalconEventBundle:Ticket')->findOneByUserAndEvent($user, $event);
        /** @var Payment $payment */
        $payment = $ticket->getPayment();
        $payment->setAmount($ticket->getAmount() - $fwdaysAmount);
        $payment->setBaseAmount($ticket->getAmount());
        $payment->setFwdaysAmount($fwdaysAmount);
        $payment->markedAsPaid();
        $this->em->flush();
        $referralBalance = $userReferral->getBalance();
        $referralService = $this->getContainer()->get('stfalcon_event.referral.service');
        $referralService->chargingReferral($payment);
        $referralService->utilizeBalance($payment);

        $this->assertEquals($referralBalance + 100, $userReferral->getBalance());
        $this->assertEquals(0, $user->getBalance());
    }
}
