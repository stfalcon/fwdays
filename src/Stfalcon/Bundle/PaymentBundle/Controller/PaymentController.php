<?php

namespace Stfalcon\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
    public function successAction()
    {
        return array();
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
    public function failAction()
    {
        return array();
    }

}