<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Service\WayForPayService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class WayForPayController.
 */
class WayForPayController extends Controller
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

        try {
            $transactionStatus = $this->processWayForPayResponse($response);
        } catch (BadRequestHttpException $e) {
            return new Response(['error' => $e->getMessage()], 400);
        }

        if (WayForPayService::WFP_TRANSACTION_APPROVED_AND_SET_PAID_STATUS === $transactionStatus) {
            return $this->redirectToRoute('payment_success');
        }
        if (WayForPayService::WFP_TRANSACTION_PENDING_STATUS === $transactionStatus) {
            return $this->redirectToRoute('payment_pending');
        }
        if (WayForPayService::WFP_TRANSACTION_FAIL_STATUS === $transactionStatus) {
            return $this->redirectToRoute('payment_fail');
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
        $wayForPay = $this->get('app.way_for_pay.service');
        try {
            $this->processWayForPayResponse($response);
        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
        $result = $wayForPay->getResponseOnServiceUrl($response);

        return new JsonResponse($result);
    }

    /**
     * @Route("/payment/success", name="payment_success")
     *
     * @return Response
     */
    public function showSuccessAction()
    {
        $paymentId = $this->get('session')->get(WayForPayService::WFP_PAYMENT_KEY);
        $this->get('session')->remove(WayForPayService::WFP_PAYMENT_KEY);

        /** @var Payment|null $payment */
        $payment = $paymentId ? $this->getDoctrine()->getRepository('ApplicationDefaultBundle:Payment')->find($paymentId) : null;

        $eventName = '';
        $eventType = '';
        if ($payment) {
            $tickets = $payment->getTickets();
            $eventName = count($tickets) > 0 ? $tickets[0]->getEvent()->getName() : '';
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

    /**
     * @param array        $response
     * @param Payment|null $payment
     *
     * @return array
     */
    private function getRequestDataToArr($response, $payment)
    {
        $paymentId = '-';
        $paymentStatus = '-';
        $paymentAmount = '-';

        if ($payment instanceof Payment) {
            $paymentId = $payment->getId();
            $paymentStatus = $payment->getStatus();
            $paymentAmount = $payment->getAmount();
        }

        return [
            'payment_id' => $paymentId,
            'payment_status' => $paymentStatus,
            'payment_amount' => $paymentAmount,
            'request_amount' => $this->getArrMean($response['amount']),
            'request_status' => $this->getArrMean($response['reasonCode']).' '.$this->getArrMean($response['reason']),
        ];
    }

    /**
     * @param mixed  $var
     * @param string $default
     *
     * @return string
     */
    private function getArrMean(&$var, $default = '')
    {
        return isset($var) ? $var : $default;
    }

    /**
     * @param array|null $response
     *
     * @return string
     */
    private function processWayForPayResponse(?array $response): string
    {
        $wayForPay = $this->get('app.way_for_pay.service');
        if (null === $response || !isset($response['transactionStatus'])) {
            $this->get('logger')->addCritical('WayForPay interaction Fail! bad content');
            $wayForPay->saveResponseLog(null, $response, 'bad content');
            throw new BadRequestHttpException('bad content');
        }

        $payment = null;
        if (\is_array($response) && isset($response['orderNo'])) {
            $payment = $this->getDoctrine()
                ->getRepository('ApplicationDefaultBundle:Payment')->find($response['orderNo']);
        }

        if (!$payment) {
            $this->get('logger')->addCritical('WayForPay interaction Fail! payment not found');
            $wayForPay->saveResponseLog(null, $response, 'payment not found');

            throw new BadRequestHttpException('payment not found');
        }

        if ($payment->isPending() && $wayForPay->checkPayment($payment, $response)) {
            $payment->setPaidWithGate(Payment::WAYFORPAY_GATE);
            if (isset($response['recToken'])) {
                $user = $payment->getUser();
                if ($user instanceof User) {
                    $user->setRecToken($response['recToken']);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            try {
                $referralService = $this->get('app.referral.service');
                $referralService->chargingReferral($payment);
                $referralService->utilizeBalance($payment);
            } catch (\Exception $e) {
                $this->get('logger')->addCritical(
                    $e->getMessage(),
                    $this->getRequestDataToArr($response, $payment)
                );
            }
            $this->get('session')->set('way_for_pay_payment', $response['orderNo']);
            $wayForPay->saveResponseLog($payment, $response, 'set paid');

            return WayForPayService::WFP_TRANSACTION_APPROVED_AND_SET_PAID_STATUS;
        }

        return $response['transactionStatus'];
    }
}
