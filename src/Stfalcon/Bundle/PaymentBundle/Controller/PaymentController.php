<?php

namespace Stfalcon\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use JMS\SecurityExtraBundle\Annotation\Secure;

//use Stfalcon\Bundle\PaymentBundle\Form\Payments\Interkassa\PayType;
//use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * Здесь собраны общие для всех платежей экшены
 */
class PaymentController extends Controller {

    /**
     * Платеж проведен успешно. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/success", name="payment_success")
     * @Template()
     *
     * @param string $message
     * @return array
     */
    public function successAction($message = 'Спасибо за оплату!')
    {
        // @todo: refact
        return array('message' => $message);
    }

    /**
     * Возникла ошибка при проведении платежа. Показываем пользователю соответствующее сообщение.
     *
     * @Route("/payment/fail", name="payment_fail")
     * @Template()
     *
     * @param string $message
     * @return array
     */
    public function failAction($message = 'Платеж не выполен!')
    {
        // @todo: refact
        return array('message' => $message);
    }

}