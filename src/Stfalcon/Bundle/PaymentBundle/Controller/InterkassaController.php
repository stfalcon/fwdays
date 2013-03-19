<?php

namespace Stfalcon\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Stfalcon\Bundle\PaymentBundle\Form\Payments\Interkassa\PayType;
use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

class InterkassaController extends Controller
{

    /**
     * @Template()
     * @Secure(roles="ROLE_USER")
     * @return array
     */
    public function payAction($event, $user, $payment)
    {
        $config = $this->container->getParameter('stfalcon_payment.config');

        $data = array(
            'ik_shop_id' => $config['interkassa']['shop_id'],
            'ik_payment_desc' => 'Оплата участия в конференции ' . $event->getName() . '. Плательщик ' . $user->getFullname() . ' (#' . $user->getId() . ')',
            'ik_sign_hash' => $this->_getSignHash($payment->getId(), $payment->getAmount()));

        return array(
            'data' => $data,
            'event' => $event,
            'payment' => $payment
        );
    }

    /**
     * Принимает ответ от шлюза
     * @Route("/payments/interkassa/status")
     * @Template()
     * @return array
     */
    public function statusAction()
    {
//        $params = $this->getRequest()->request->all();
        $params = $_POST;
        $payment = $this->getDoctrine()
                     ->getRepository('StfalconPaymentBundle:Payment')
                     ->findOneBy(array('id' => $params['ik_payment_id']));

        if ($payment->getStatus() == Payment::STATUS_PENDING && $this->_checkPaymentStatus($params)) {
            $payment->setStatus(Payment::STATUS_PAID);
            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();
            $message = 'Проверка контрольной подписи данных о платеже успешно пройдена!';
        } else {
            $message = 'Проверка контрольной подписи данных о платеже провалена!';
        }
        return array('message' => $message);
    }

    /**
     * Проверяет валидность и статус платежа
     *
     * @param array $params
     * @return boolean
     */
    private function _checkPaymentStatus($params)
    {
        if (!array_key_exists('ik_shop_id', $params) ||
            !array_key_exists('ik_payment_amount', $params) ||
            !array_key_exists('ik_payment_id', $params) ||
            !array_key_exists('ik_paysystem_alias', $params) ||
            !array_key_exists('ik_baggage_fields', $params) ||
            !array_key_exists('ik_payment_state', $params) ||
            !array_key_exists('ik_trans_id', $params) ||
            !array_key_exists('ik_currency_exch', $params) ||
            !array_key_exists('ik_fees_payer', $params)) {
            return false;
        }

        $config = $this->container->getParameter('stfalcon_payment.config');

        $crc = md5(
            $params['ik_shop_id'] .':'.
            $params['ik_payment_amount'] .':'.
            $params['ik_payment_id'] .':'.
            $params['ik_paysystem_alias'] .':'.
            $params['ik_baggage_fields'] .':'.
            $params['ik_payment_state'] .':'.
            $params['ik_trans_id'] .':'.
            $params['ik_currency_exch'] .':'.
            $params['ik_fees_payer'] .':'.
            $config['interkassa']['secret']
        );
        if (strtoupper($params['ik_sign_hash']) === strtoupper($crc) &&
                $params['ik_payment_state'] == 'success') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * CRC-подпись для запроса на шлюз
     * @param $paymentId
     * @param $sum
     * @return string
     */
    protected function _getSignHash($paymentId, $sum)
    {
        $config = $this->container->getParameter('stfalcon_payment.config');

        $params['ik_shop_id'] = $config['interkassa']['shop_id'];
        $params['ik_payment_amount'] = $sum;
        $params['ik_payment_id'] = $paymentId;
        $params['ik_paysystem_alias'] = '';
        $params['ik_baggage_fields'] = '';

        $hash = md5(
            $params['ik_shop_id'] .':'.
            $params['ik_payment_amount'] .':'.
            $params['ik_payment_id'] .':'.
            $params['ik_paysystem_alias'] .':'.
            $params['ik_baggage_fields'] .':'.
            $config['interkassa']['secret']
        );

        return $hash;
    }

}