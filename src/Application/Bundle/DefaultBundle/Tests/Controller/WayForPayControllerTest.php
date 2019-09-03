<?php

namespace Application\Bundle\DefaultBundle\Tests\Controller;

use Application\Bundle\DefaultBundle\Controller\WayForPayController;
use Prophecy\Argument;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WayForPayControllerTest extends WebTestCase
{
    /**
     * @var \Prophecy\Prophet
     */
    protected $prophet;

    protected function setup()
    {
        $this->prophet = new \Prophecy\Prophet();
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testPaymentSetPaid()
    {
        $request = $this->prophet->prophesize('Symfony\Component\HttpFoundatio\Request');
        $request->willExtend('Symfony\Component\HttpFoundation\Request');

        $wayforpayService = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Service\WayForPayService');
        $paymentService = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Service\PaymentService');

        $container = $this->prophet->prophesize('Symfony\Component\DependencyInjection\Container');
        $doctrine = $this->prophet->prophesize('Doctrine\Bundle\DoctrineBundle\Registry');
        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $paymentRepository = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Repository\PaymentRepository');
        $payment = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Entity\Payment');
        $logger = $this->prophet->prophesize('Symfony\Bridge\Monolog\Logger');
        $referralService = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Service\ReferralService');
        $session = $this->prophet->prophesize('Symfony\Component\HttpFoundation\Session\Session');

        $request->getContent()->shouldBeCalled()
            ->willReturn('{"orderNo":"1"}');

        $doctrine->getRepository('ApplicationDefaultBundle:Payment')
            ->shouldBeCalled()
            ->willReturn($paymentRepository);

        $doctrine->getManager()->willReturn($em);

        $payment->isPending()->shouldBeCalled()->willReturn(true);
        $payment->setPaidWithGate(Payment::WAYFORPAY_GATE)->shouldBeCalled();

        $session->set('way_for_pay_payment', 1)->shouldBeCalled()->willReturn(null);

        $referralService->chargingReferral($payment)->shouldBeCalled()->willReturn(null);
        $referralService->utilizeBalance($payment)->shouldBeCalled()->willReturn(null);

        $container->has('doctrine')->willReturn(true);
        $container->get('doctrine')->willReturn($doctrine);

        $paymentRepository->findOneBy(Argument::any())->willReturn($payment);

        $container->get('app.way_for_pay.service')->willReturn($wayforpayService);
        $container->get('app.referral.service')->willReturn($referralService);
        $container->get('app.payment.service')->willReturn($paymentService);
        $container->get('logger')->willReturn($logger);
        $container->get('session')->willReturn($session);

        $wayforpayService->checkPayment(Argument::any(), Argument::any())
            ->willReturn(true)
            ->shouldBeCalled();
        $wayforpayService->saveResponseLog($payment, Argument::any(), 'set paid')->willReturn(null)
            ->shouldBeCalled();
        $wayforpayService->getResponseOnServiceUrl(Argument::any())->willReturn([])
            ->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $wayforpayController = new WayForPayController();
        $wayforpayController->setContainer($container->reveal());
        $result = $wayforpayController->serviceInteractionAction($request->reveal());

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testPaymentNotFound()
    {
        $request = $this->prophet->prophesize('Symfony\Component\HttpFoundatio\Request');
        $request->willExtend('Symfony\Component\HttpFoundation\Request');

        $container = $this->prophet->prophesize('Symfony\Component\DependencyInjection\Container');
        $doctrine = $this->prophet->prophesize('Doctrine\Bundle\DoctrineBundle\Registry');
        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $paymentRepository = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Repository\PaymentRepository');
        $logger = $this->prophet->prophesize('Symfony\Bridge\Monolog\Logger');
        $wayforpayService = $this->prophet->prophesize('Application\Bundle\DefaultBundle\Service\WayForPayService');

        $wayforpayService->saveResponseLog(null, Argument::any(), Argument::any())->shouldBeCalled();

        $request->getContent()->shouldBeCalled()
            ->willReturn('{"orderNo":"1"}');

        $doctrine->getManager()->willReturn($em);
        $doctrine->getRepository('ApplicationDefaultBundle:Payment')
            ->shouldBeCalled()
            ->willReturn($paymentRepository);
        $logger->addCritical(Argument::any())->shouldBeCalled();

        $container->get('app.way_for_pay.service')->willReturn($wayforpayService);
        $container->has('doctrine')->willReturn(true);
        $container->get('doctrine')->willReturn($doctrine);
        $container->get('logger')->willReturn($logger);

        $paymentRepository->findOneBy(Argument::any())->willReturn(null);

        $wayforpayController = new WayForPayController();
        $wayforpayController->setContainer($container->reveal());
        $result = $wayforpayController->serviceInteractionAction($request->reveal());

        $this->assertEquals(400, $result->getStatusCode());
    }
}
