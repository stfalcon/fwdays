<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Prophecy\Prophet;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;

/**
 * Class ReferralServiceTest.
 */
class PaymentServiceTest extends WebTestCase
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
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketCostData',
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
     * Check Payment pay fwdays money.
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function testPayByFwdaysAmount()
    {
        $fwdaysAmount = 3000;

        /** @var User $user */
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => 'jack.sparrow@fwdays.com']);
        $user->setBalance($fwdaysAmount);

        /** @var Event $event */
        $event = $this->em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => 'php-day-2017']);
        /** @var Ticket $ticket */
        $ticket = $this->em->getRepository('StfalconEventBundle:Ticket')->findOneByUserAndEvent($user, $event);
        /** @var Payment $payment */
        $payment = $ticket->getPayment();

        $referralBalance = $user->getBalance();
        $paymentService = $this->getContainer()->get('stfalcon_event.payment.service');

        $paymentService->setPaidByReferralMoney($payment, $event);

        $this->assertEquals($payment->getAmount(), 0);
        $this->assertEquals($payment->getBaseAmount(), $ticket->getAmountWithoutDiscount());
        $this->assertEquals($payment->getFwdaysAmount(), $ticket->getAmount());
        $this->assertTrue($payment->isPaid());

        $this->assertEquals($referralBalance - $ticket->getAmount(), $user->getBalance());
    }
}
