<?php

namespace Stfalcon\Bundle\PaymentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\PaymentsBundle\Form\Payments\Interkassa\PayType;
use Stfalcon\Bundle\PaymentsBundle\Entity\Payment;

class InterkassaController extends Controller
{

    /**
     * @Route("/payments/interkassa/pay")
     * @Template()
     * @return array
     */
    public function payAction()
    {
        /** @var $token \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken */
        $token = $this->container->get('security.context')->getToken();
        //@todo пускать только авторизованого пользователя
        //        if ($token->isAuthenticated()) {
        //            $user = $token->getUser();
        //        } else {
        //
        //        }
        
        $user = $token->getUser();

        $sum = 150; //@todo подставлять из конфига

        $payment = new Payment();
        $payment->setStatus(Payment::STATUS_PENDING);
        $payment->setUserId($user->getId());
        $payment->setSum($sum);

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($payment);
        $em->flush();

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->createForm(new PayType());
        $form->setData(
            array(
                'amount' => $payment->getSum(),
                'ik_shop_id' => '8EEAE9AF-2BDA-441B-275C-EC193BB7560D', //@todo shop_id, подставлять из конфига;
                'ik_payment_amount' => $payment->getSum(),
                'ik_payment_id' => $payment->getId(),
                'ik_payment_desc' => 'Оплата участия в конференции',
                'ik_sign_hash' => $this->_getSignHash($payment->getId(), $payment->getSum()),
            )
        );
        return array('form' => $form->createView());
    }

    /**
     * Принимает ответ от шлюза
     * @Route("/payments/interkassa/status")
     * @Template()
     * @return array
     */
    public function statusAction()
    {
        $params = $this->getRequest()->request->all();
        $payment = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconPaymentsBundle:Payment')
                     ->findBy(array('id' => $params['ik_payment_id']));

        if ($this->_checkPaymentStatus($params)) {
            $payment->setStatus(Payment::STATUS_PAID);
            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($payment);
            $em->flush();
            $message = 'Проверка контрольной подписи данных о платеже успешно пройдена!';
        } else {
            $message = 'Проверка контрольной подписи данных о платеже провалена!';
        }
        return array('message' => $message);
    }

    /**
     * @Route("/payments/interkassa/success")
     * @Template()
     * @return array
     */
    public function successAction()
    {
        return array('message' => 'Спасибо за оплату!');
    }

    /**
     * @Route("/payments/interkassa/fail")
     * @Template()
     * @return array
     */
    public function failAction()
    {
        return array('message' => 'Платеж не выполен!');
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
            'N5vZX2kWJe67ChUt' //@todo secret key, подставлять из конфига
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
        $params['ik_shop_id'] = '8EEAE9AF-2BDA-441B-275C-EC193BB7560D'; //@todo shop_id, подставлять из конфига;
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
            'N5vZX2kWJe67ChUt' //@todo secret key, подставлять из конфига
        );

        return $hash;
    }

}
