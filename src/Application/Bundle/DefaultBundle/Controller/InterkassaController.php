<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\Bundle\DefaultBundle\Service\InterkassaService;
use Stfalcon\Bundle\EventBundle\Entity\Payment;

/**
 * Контроллер оплаты и статусов платежей через Интеркассу.
 */
class InterkassaController extends Controller
{
    /**
     * Здесь мы получаем уведомления о статусе платежа и отмечаем платеж как
     * успешный (или не отмечаем)
     * Также рассылаем письма и билеты всем, кто был привязан к платежу.
     *
     * @Route("/payment/interaction", name="payment_interaction")
     *
     * @param Request $request
     *
     * @return array|Response
     */
    public function interactionAction(Request $request)
    {
        /** @var Payment $payment */
        $payment = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Payment')
            ->findOneBy(array('id' => $request->get('ik_pm_no')));

        if (!$payment) {
            throw new Exception(sprintf('Платеж №%s не найден!', $request->get('ik_pm_no')));
        }

        /** @var InterkassaService $interkassa */
        $interkassa = $this->get('stfalcon_event.interkassa.service');
        if ($payment->isPending() && $interkassa->checkPayment($payment, $request)) {
            $payment->markedAsPaid();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            try {
                $referralService = $this->get('stfalcon_event.referral.service');
                // начисляем средства за реферала
                $referralService->chargingReferral($payment);

                // списываем реферельные средства
                $referralService->utilizeBalance($payment);
            } catch (\Exception $e) {
            }

            return new Response('SUCCESS', 200);
        }

        return new Response('FAIL', 400);
    }

    /**
     * Платеж проведен успешно. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/success", name="payment_success")
     *
     * @Template()
     *
     * @return array
     */
    public function successAction()
    {
        return [];
    }

    /**
     * Возникла ошибка при проведении платежа. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/fail", name="payment_fail")
     *
     * @Template()
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
     * @Route("/payment/pending", name="payment_pending")
     *
     * @Template()
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
}
