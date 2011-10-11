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
     * @Route("/payments/interkassa/pay", name="payments_pay")
     * @Template()
     * @return array
     */
    public function payAction()
    {
        $config = $this->container->getParameter('stfalcon_payments.config');

        /** @var $token \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken */
        $token = $this->container->get('security.context')->getToken();
        //@todo пускать только авторизованого пользователя
        //        if ($token->isAuthenticated()) {
        //            $user = $token->getUser();
        //        } else {
        //
        //        }

        $user = $token->getUser();

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        
        $paid = $em->getRepository('StfalconPaymentsBundle:Payment')
            ->findBy(array('userId' => $user->getId(), 'status' => Payment::STATUS_PAID));
        if (count($paid) > 0) {
            $response = $this->forward(
                'StfalconPaymentsBundle:Interkassa:success',
                array('message' => 'Вы уже оплатили участие в конференции Zend Framework Day')
            );
            return $response;
        }

        $sum = 150; //@todo подставлять из конфига

        $payment = new Payment();
        $payment->setStatus(Payment::STATUS_PENDING);
        $payment->setUserId($user->getId());
        $payment->setSum($sum);

        $em->persist($payment);
        $em->flush();

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->createForm(new PayType());

        // @todo данные брать с билета
        $form->setData(
            array(
                'amount' => $payment->getSum(),
                'ik_shop_id' => $config['interkassa']['shop_id'],
                'ik_payment_amount' => $payment->getSum(),
                'ik_payment_id' => $payment->getId(),
                'ik_payment_desc' => 'Оплата участия в конференции Zend Framework Day. Плательщик ' . $user->getFullname() . ' (#' . $user->getId() . ').',
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
//        $params = $this->getRequest()->request->all();
        $params = $_POST;
        $payment = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconPaymentsBundle:Payment')
                     ->findOneBy(array('id' => $params['ik_payment_id']));

        if ($payment->getStatus() == Payment::STATUS_PENDING && $this->_checkPaymentStatus($params)) {
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
     * @param string $message
     * @return array
     */
    public function successAction($message = 'Спасибо за оплату!')
    {
        return array('message' => $message);
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

        $config = $this->container->getParameter('stfalcon_payments.config');

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
        $config = $this->container->getParameter('stfalcon_payments.config');

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
