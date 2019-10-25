<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Service\PaymentProcess\AbstractPaymentProcessService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * PaymentProcessController.
 */
class PaymentProcessController extends Controller
{
    /** @var array */
    protected $itemVariants = ['javascript', 'php', 'frontend', 'highload', 'net.'];

    /**
     * @Route("/payment/interaction", name="payment_interaction", methods={"POST"})
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function interactionAction(Request $request)
    {
        $response = $request->request->all();
        $paymentSystem = $this->get('app.payment_system.service');

        try {
            $transactionStatus = $paymentSystem->processResponse($response);
        } catch (BadRequestHttpException $e) {
            return $this->redirectToRoute('homepage');
        }

        if ($paymentSystem->isUseRedirectByStatus()) {
            if (AbstractPaymentProcessService::TRANSACTION_APPROVED_AND_SET_PAID_STATUS === $transactionStatus) {
                return $this->redirectToRoute('payment_success');
            }
            if (AbstractPaymentProcessService::TRANSACTION_STATUS_PENDING === $transactionStatus) {
                return $this->redirectToRoute('payment_pending');
            }
            if (AbstractPaymentProcessService::TRANSACTION_STATUS_FAIL === $transactionStatus) {
                return $this->redirectToRoute('payment_fail');
            }
        } elseif (AbstractPaymentProcessService::TRANSACTION_APPROVED_AND_SET_PAID_STATUS === $transactionStatus) {
            return new Response('SUCCESS', 200);
        }

        return new Response('FAIL transaction status:'.$transactionStatus, 400);
    }

    /**
     * @Route("/payment/service-interaction", name="payment_service_interaction",
     *     methods={"POST"},
     *     options={"expose"=true})
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function serviceInteractionAction(Request $request)
    {
        $json = $request->getContent();
        $response = \json_decode($json, true);

        $paymentSystem = $this->get('app.payment_system.service');

        try {
            $paymentSystem->processResponse($response);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
        $result = $paymentSystem->getResponseOnServiceUrl($response);

        return new JsonResponse($result);
    }

    /**
     * @Route("/payment/success", name="payment_success")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function showSuccessAction(Request $request)
    {
        $session = $this->get('session');
        $paymentId = $session->get(AbstractPaymentProcessService::SESSION_PAYMENT_KEY);
        $session->remove(AbstractPaymentProcessService::SESSION_PAYMENT_KEY);

        if (null === $paymentId) {
            $response = $request->query->all();
            $paymentId = $this->get('app.payment_system.service')->getPaymentIdFromResponse($response);
            if (null === $paymentId) {
                throw new BadRequestHttpException();
            }
        }

        /** @var Payment|null $payment */
        $payment = $this->getDoctrine()->getRepository('ApplicationDefaultBundle:Payment')->find($paymentId);

        $eventName = '';
        $eventType = '';
        if ($payment) {
            $tickets = $payment->getTickets();
            $eventName = \count($tickets) > 0 ? $tickets[0]->getEvent()->getName() : '';
            $eventType = $this->getItemVariant($eventName);
        }

        return $this->render('@ApplicationDefault/PaymentResult/success.html.twig', [
            'payment' => $payment,
            'event_name' => $eventName,
            'event_type' => $eventType,
        ]);
    }

    /**
     * @Route("/payment/fail", name="payment_fail")
     *
     * @return Response
     */
    public function failAction(): Response
    {
        return $this->render('@ApplicationDefault/PaymentResult/fail.html.twig');
    }

    /**
     * @Route("/payment/pending", name="payment_pending")
     *
     * @return Response
     */
    public function pendingAction(): Response
    {
        return $this->render('@ApplicationDefault/PaymentResult/pending.html.twig');
    }

    /**
     * @param string $eventName
     *
     * @return string
     */
    private function getItemVariant($eventName)
    {
        foreach ($this->itemVariants as $itemVariant) {
            $pattern = '/'.$itemVariant.'/';
            if (preg_match($pattern, strtolower($eventName))) {
                return $itemVariant;
            }
        }

        return $eventName;
    }
}
