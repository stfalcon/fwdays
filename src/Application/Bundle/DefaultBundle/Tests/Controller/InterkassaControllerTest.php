<?php

namespace Stfalcon\Bundle\EventBundle\Tests\Controller;

use Application\Bundle\DefaultBundle\Controller\InterkassaController;
use Symfony\Component\HttpFoundation\Request;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class InterkassaControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Prophecy\Prophet
     */
    protected  $prophet;

    protected function setup()
    {
        $this->prophet = new \Prophecy\Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testIteractionActionIfPaymentFound()
    {
        $request = $this->prophet->prophesize('Symfony\Component\HttpFoundatio\Request');
        $request->willExtend('Symfony\Component\HttpFoundation\Request');

        $interkassaService = $this->prophet->prophesize('Stfalcon\Bundle\EventBundle\Service\InterkassaService');
        $paymentService = $this->prophet->prophesize('Stfalcon\Bundle\EventBundle\Service\PaymentService');

        $container = $this->prophet->prophesize('Symfony\Component\DependencyInjection\Container');
        $doctrine = $this->prophet->prophesize('Doctrine\Bundle\DoctrineBundle\Registry');
        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $paymentRepository = $this->prophet->prophesize('Stfalcon\Bundle\EventBundle\Repository\PaymentRepository');
        $payment = $this->prophet->prophesize('Stfalcon\Bundle\EventBundle\Entity\Payment');

        $doctrine->getRepository('StfalconEventBundle:Payment')
            ->shouldBeCalled()
            ->willReturn($paymentRepository);

        $doctrine->getManager()->willReturn($em);

        $payment->isPending()
            ->willReturn(true)
            ->shouldBeCalled();
        $payment->markedAsPaid()->shouldBeCalled();

        $container->has('doctrine')->willReturn(true);
        $container->get('doctrine')->willReturn($doctrine);

        $paymentRepository->findOneBy(Argument::any())->willReturn($payment);

        $container->get('stfalcon_event.interkassa.service')
            ->willReturn($interkassaService);
        $container->get('stfalcon_event.payment.service')
            ->willReturn($paymentService);

        $interkassaService->checkPayment(Argument::any(), Argument::any())
            ->willReturn(true)
            ->shouldBeCalled();
        $paymentService->setTicketsCostAsSold(Argument::any())->shouldBeCalled();
        $paymentService->calculateTicketsPromocode(Argument::any())->shouldBeCalled();

        $em->flush()->shouldBeCalled();


        $interkassaController = new InterkassaController();
        $interkassaController->setContainer($container->reveal());
        $result = $interkassaController->interactionAction($request->reveal());

        $this->assertEquals($result, new Response('SUCCESS', 200));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\Exception
     * @expectedExceptionMessage Платеж №1 не найден!
     *
     */
    public function testIteractionActionIfPaymentNotFound()
    {
        $request = $this->prophet->prophesize('Symfony\Component\HttpFoundatio\Request');
        $request->willExtend('Symfony\Component\HttpFoundation\Request');

        $container = $this->prophet->prophesize('Symfony\Component\DependencyInjection\Container');
        $doctrine = $this->prophet->prophesize('Doctrine\Bundle\DoctrineBundle\Registry');
        $em = $this->prophet->prophesize('Doctrine\ORM\EntityManager');
        $paymentRepository = $this->prophet->prophesize('Stfalcon\Bundle\EventBundle\Repository\PaymentRepository');

        $request->get('ik_pm_no')->willReturn(1);
        $request->get('ik_pm_no')->shouldBeCalled();

        $doctrine->getManager()->willReturn($em);
        $doctrine->getRepository('StfalconEventBundle:Payment')
            ->shouldBeCalled()
            ->willReturn($paymentRepository);


        $container->has('doctrine')->willReturn(true);
        $container->get('doctrine')->willReturn($doctrine);

        $paymentRepository->findOneBy(Argument::any())->willReturn(false);

        $interkassaController = new InterkassaController();
        $interkassaController->setContainer($container->reveal());
        $interkassaController->interactionAction($request->reveal());
    }
}
