<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Class WayForPayController.
 */
class WayForPayController extends Controller
{
    /** @var array */
    protected $itemVariants = ['javascript', 'php', 'frontend', 'highload', 'net.'];

    /**
     * @Route("/payment/interaction", name="payment_interaction",
     *     methods={"POST"},
     *     options={"expose"=true})
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function interactionAction(Request $request)
    {
        $response = $request->get('response');
        if (null === $response) {
            $response = $request->request->all();
        }
        $payment = null;

        if (is_array($response) && isset($response['orderNo'])) {
            /** @var Payment $payment */
            $payment = $this->getDoctrine()
                ->getRepository('StfalconEventBundle:Payment')
                ->findOneBy(['id' => $response['orderNo']]);
        }

        if (!$payment) {
            throw new Exception(sprintf('Платеж №%s не найден!', $this->getArrMean($response['orderNo'])));
        }

        $wayForPay = $this->get('app.way_for_pay.service');
        if ($payment->isPending() && $wayForPay->checkPayment($payment, $response)) {
            $payment->setPaidWithGate(Payment::WAYFORPAY_GATE);
            if (isset($response['recToken'])) {
                $user = $this->getUser();
                $user->setRecToken($response['recToken']);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            try {
                $referralService = $this->get('stfalcon_event.referral.service');
                $referralService->chargingReferral($payment);
                $referralService->utilizeBalance($payment);
            } catch (\Exception $e) {
            }

            $this->get('session')->set('way_for_pay_payment', $response['orderNo']);
            if ($request->isXmlHttpRequest()) {
                return new Response('ok', 200);
            }

            return $this->redirectToRoute('show_success');
        }

        $this->get('logger')->addCritical(
            'Interkassa interaction Fail!',
            $this->getRequestDataToArr($response, $payment)
        );

        return new Response('FAIL', 400);
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
        if (null === $response) {
            $this->get('logger')->addCritical(
                'WayForPay interaction Fail! bad content'
            );

            return new JsonResponse(['error' => 'bad content'], 400);
        }
        $wayForPay = $this->get('app.way_for_pay.service');

        $payment = null;
        if (is_array($response) && isset($response['orderNo'])) {
            /** @var Payment $payment */
            $payment = $this->getDoctrine()
                ->getRepository('StfalconEventBundle:Payment')
                ->findOneBy(['id' => $response['orderNo']]);
        }

        if (!$payment) {
            $this->get('logger')->addCritical(
                'WayForPay interaction Fail! payment not found'
            );
            $wayForPay->saveResponseLog(null, $response, 'payment not found');

            return new JsonResponse(['error' => 'payment not found'], 400);
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
                $referralService = $this->get('stfalcon_event.referral.service');
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
            $result = $wayForPay->getResponseOnServiceUrl($response);

            return new JsonResponse($result);
        }

        $wayForPay->saveResponseLog($payment, $response, $this->getArrMean($response['transactionStatus']));
        $result = $wayForPay->getResponseOnServiceUrl($response);

        return new JsonResponse($result);
    }

    /**
     * @Route("/payment/success", name="show_success")
     *
     * @return Response
     */
    public function showSuccessAction()
    {
        $paymentId = $this->get('session')->get('way_for_pay_payment');
        $this->get('session')->remove('way_for_pay_payment');

        /** @var Payment $payment */
        $payment = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Payment')
            ->findOneBy(['id' => $paymentId]);

        $eventName = '';
        $eventType = '';
        if ($payment) {
            $tickets = $payment->getTickets();
            $eventName = count($tickets) > 0 ? $tickets[0]->getEvent()->getName() : '';
            $eventType = $this->getItemVariant($eventName);
        }

        return $this->render('@ApplicationDefault/Interkassa/success.html.twig', [
            'payment' => $payment,
            'event_name' => $eventName,
            'event_type' => $eventType,
        ]);
    }

    /**
     * Возникла ошибка при проведении платежа. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/fail", name="payment_fail")
     *
     * @Template("@ApplicationDefault/Interkassa/fail.html.twig")
     *
     * @return array
     */
    public function failAction()
    {
        return [];
    }

    /**
     * Оплата не завершена. Ожидаем ответ шлюза.
     *
     * @Route("/payment/pending", name="payment_pending")
     *
     * @Template("@ApplicationDefault/Interkassa/pending.html.twig")
     *
     * @return array|Response
     */
    public function pendingAction()
    {
        return [];
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
}
