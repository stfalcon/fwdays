<?php

namespace Application\Bundle\DefaultBundle\EventListener;

use Application\Bundle\DefaultBundle\Controller\WayForPayController;
use Doctrine\Common\Persistence\ObjectRepository;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Repository\PaymentRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;

/**
 * Class CheckPaymentPaidOnRequestListener.
 */
class CheckPaymentPaidOnRequestListener
{
    /** @var Session */
    private $session;

    /** @var PaymentRepository */
    private $paymentRepository;

    /** @var Router */
    private $router;

    /**
     * @param Session          $session
     * @param ObjectRepository $paymentRepository
     * @param router           $router
     *
     * LocaleUrlResponseListener constructor
     */
    public function __construct(Session $session, ObjectRepository $paymentRepository, Router $router)
    {
        $this->session = $session;
        $this->paymentRepository = $paymentRepository;
        $this->router = $router;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ('show_success' !== $request->get('_route') && $this->session->has(WayForPayController::WAY_FOR_PAY_PAYMENT_ID)) {
            $paymentId = $this->session->get(WayForPayController::WAY_FOR_PAY_PAYMENT_ID);
            $payment = null;
            if ($paymentId) {
                $payment = $this->paymentRepository->find($paymentId);
            }

            if ($payment instanceof Payment && $payment->isPaid()) {
                $event->setResponse(new RedirectResponse($this->router->generate('show_success', ['returnUrl' => $request->getUri()])));
            }
        }
    }
}
