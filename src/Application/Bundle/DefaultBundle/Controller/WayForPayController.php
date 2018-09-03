<?php

namespace Application\Bundle\DefaultBundle\Controller;

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
            throw new Exception(sprintf('Платеж №%s не найден!', $this->getArrMean($response['orderReference'])));
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

            $this->get('session')->set('way_for_pay_payment', $response['orderReference']);
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
            throw new Exception(sprintf('Платеж №%s не найден!', $this->getArrMean($response['orderReference'])));
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

            $this->get('session')->set('way_for_pay_payment', $response['orderReference']);
            if ($request->isXmlHttpRequest()) {
                return new Response('ok', 200);
            }

            return $this->redirectToRoute('show_success');
        }

        $this->get('logger')->addCritical(
            'Interkassa interaction Fail!',
            $this->getRequestDataToArr($response, $payment)
        );

        return new Response('FAIL-payment_service_interaction', 400);
    }

    /**
     * @Route("/success", name="show_success")
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
     * @Route("/payment/fail", name="payment_fail",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
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
     * @param Request $request
     *
     * @Route("/payment/pending", name="payment_pending",
     *     methods={"POST"},
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     *
     * @Template("@ApplicationDefault/Interkassa/pending.html.twig")
     *
     * @return array|Response
     */
    public function pendingAction(Request $request)
    {
        /** @var Payment $payment */
        $payment = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Payment')
            ->findOneBy(array('id' => $request->get('ik_pm_no')));

        if (!$payment) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $event = $em->getRepository('StfalconEventBundle:Event')->find(10); //TODO: js-2015
            $paymentRepository = $em->getRepository('StfalconEventBundle:Payment');
            $payment = $paymentRepository->findPaymentByUserAndEvent($user, $event);
            if (!$payment) {
                return $this->forward('ApplicationDefaultBundle:Interkassa:fail');
            }
        }

        if ($payment->isPaid()) {
            return $this->forward('ApplicationDefaultBundle:Interkassa:success');
        }

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
