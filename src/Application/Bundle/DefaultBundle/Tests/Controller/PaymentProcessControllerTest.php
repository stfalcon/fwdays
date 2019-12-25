<?php

namespace Application\Bundle\DefaultBundle\Tests;

use Application\Bundle\DefaultBundle\Controller\PaymentProcessController;
use Application\Bundle\DefaultBundle\Service\PaymentProcess\AbstractPaymentProcessService;
use Application\Bundle\DefaultBundle\Service\PaymentProcess\PaymentProcessInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class PaymentProcessControllerTest extends TestCase
{
    /** @var ContainerInterface|MockObject */
    private $container;

    /** @var PaymentProcessController */
    private $controller;

    /** set up fixtures */
    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->controller = new PaymentProcessController();
        $this->controller->setContainer($this->container);
    }

    /** destroy */
    protected function tearDown(): void
    {
        unset(
            $this->controller,
            $this->container,
        );
    }

    /**
     * @dataProvider paymentInteractionProvider
     */
    public function testPaymentInteraction(string $status, bool $isRedirect, int $responseCode): void
    {
        $request = $this->createMock(Request::class);
        $postRequest = $this->createMock(ParameterBag::class);
        $request->request = $postRequest;
        $router = $this->createMock(Router::class);
        $router
            ->method('generate')
            ->willReturn('/payment/success')
        ;

        $response = [];
        $request->request
            ->expects(self::once())
            ->method('all')
            ->willReturn($response)
        ;

        $paymentSystem = $this->createMock(PaymentProcessInterface::class);

        $this->container
            ->method('get')
            ->withConsecutive(['app.payment_system.service'], ['router'])
            ->willReturnOnConsecutiveCalls($paymentSystem, $router)
        ;

        $paymentSystem
            ->expects(self::once())
            ->method('processData')
            ->with($response)
            ->willReturn($status)
        ;

        $paymentSystem
            ->expects(self::once())
            ->method('isUseRedirectByStatus')
            ->willReturn($isRedirect)
        ;

        $response = $this->controller->interactionAction($request);
        self::assertEquals($responseCode, $response->getStatusCode());
    }

    public function paymentInteractionProvider(): \Generator
    {
        yield ['status' => AbstractPaymentProcessService::TRANSACTION_APPROVED_AND_SET_PAID_STATUS, 'isRedirect' => true, 'responseCode' => 302];
        yield ['status' => AbstractPaymentProcessService::TRANSACTION_APPROVED_AND_SET_PAID_STATUS, 'isRedirect' => false, 'responseCode' => 200];
        yield ['status' => AbstractPaymentProcessService::TRANSACTION_STATUS_FAIL, 'isRedirect' => true, 'responseCode' => 302];
        yield ['status' => AbstractPaymentProcessService::TRANSACTION_STATUS_FAIL, 'isRedirect' => false, 'responseCode' => 400];
        yield ['status' => AbstractPaymentProcessService::TRANSACTION_STATUS_PENDING, 'isRedirect' => true, 'responseCode' => 302];
    }

    public function testPaymentInteractionFail(): void
    {
        $request = $this->createMock(Request::class);
        $postRequest = $this->createMock(ParameterBag::class);
        $request->request = $postRequest;
        $router = $this->createMock(Router::class);
        $router
            ->method('generate')
            ->willReturn('/')
        ;

        $response = [];
        $request->request
            ->expects(self::once())
            ->method('all')
            ->willReturn($response)
        ;

        $paymentSystem = $this->createMock(PaymentProcessInterface::class);

        $this->container
            ->method('get')
            ->withConsecutive(['app.payment_system.service'], ['router'])
            ->willReturnOnConsecutiveCalls($paymentSystem, $router)
        ;

        $paymentSystem
            ->expects(self::once())
            ->method('processData')
            ->with($response)
            ->willThrowException(new BadRequestHttpException())
        ;

        $paymentSystem
            ->expects(self::never())
            ->method('isUseRedirectByStatus')
        ;

        $response = $this->controller->interactionAction($request);
        self::assertEquals(302, $response->getStatusCode());
    }
}
