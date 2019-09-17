<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Repository\TicketRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Prophecy\Prophet;
use Symfony\Component\BrowserKit\Client;

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
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadUserData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadPaymentData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadTicketData',
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
        $userReferral = $this->em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['email' => 'user@fwdays.com']);
        /** @var User $user */
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['email' => 'jack.sparrow@fwdays.com']);
        $user->setBalance($fwdaysAmount);
        $user->setUserReferral($userReferral);

        /** @var Event $event */
        $event = $this->em->getRepository('ApplicationDefaultBundle:Event')->findOneBy(['slug' => 'zend-day-2017']);
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->em->getRepository('ApplicationDefaultBundle:Ticket');
        /** @var Ticket $ticket */
        $ticket = $ticketRepository->findOneByUserAndEventWithPayment($user, $event);
        /** @var Payment $payment */
        $payment = $ticket->getPayment();
        $payment->setAmount($ticket->getAmount() - $fwdaysAmount);
        $payment->setBaseAmount($ticket->getAmount());
        $payment->setFwdaysAmount($fwdaysAmount);
        $payment->markedAsPaid();
        $this->em->flush();
        $referralBalance = $userReferral->getBalance();
        $referralService = $this->getContainer()->get('app.referral.service');
        $referralService->chargingReferral($payment);
        $referralService->utilizeBalance($payment);

        $this->assertEquals($referralBalance + 100, $userReferral->getBalance());
        $this->assertEquals(0, $user->getBalance());
    }
}
