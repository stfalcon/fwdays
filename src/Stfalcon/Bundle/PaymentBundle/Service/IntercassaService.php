<?php

namespace Stfalcon\Bundle\PaymentBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use Zend\Validator\File\Md5;

/**
 * Class IntercassaService
 */
class IntercassaService
{
    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Проверяет валидность и статус платежа
     *
     * @param array $params Array of parameters
     *
     * @return boolean
     */
    public function checkPaymentStatus($params)
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
            $params['ik_shop_id'] . ':' .
            $params['ik_payment_amount'] . ':' .
            $params['ik_payment_id'] . ':' .
            $params['ik_paysystem_alias'] . ':' .
            $params['ik_baggage_fields'] . ':' .
            $params['ik_payment_state'] . ':' .
            $params['ik_trans_id'] . ':' .
            $params['ik_currency_exch'] . ':' .
            $params['ik_fees_payer'] . ':' .
            $config['interkassa']['secret']
        );

        $paymentIsSuccess = ('success' == $params['ik_payment_state']);

        if (strtoupper($params['ik_sign_hash']) === strtoupper($crc) && $paymentIsSuccess) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * CRC-подпись для запроса на шлюз
     *
     * @param int    $paymentId   Payment ID
     * @param float  $sum         Sum
     * @param string $description Payment description
     *
     * @return string
     */
    public function getSignHash($paymentId, $sum, $description)
    {
        $config = $this->container->getParameter('stfalcon_payment.config');

        $params['ik_co_id'] = $config['interkassa']['shop_id'];
        $params['ik_am']    = $sum;
        $params['ik_pm_no'] = $paymentId;
        $params['ik_desc']  = $description;

        // сортируем по ключам в алфавитном порядке элементы массива
        ksort($params, SORT_STRING);
        // добавляем в конец массива "секретный ключ"
        array_push($params, $config['interkassa']['secret']);
        // конкатенируем значения через символ ":"
        $signString = implode(':', $params);
        // берем MD5 хэш в бинарном виде по сформированной строке и кодируем в BASE64
        $sign = base64_encode(md5($signString, true));

//        $hash = md5(
//            $params['ik_shop_id'] . ':' .
//            $params['ik_payment_amount'] . ':' .
//            $params['ik_payment_id'] . ':' .
//            $params['ik_paysystem_alias'] . ':' .
//            $params['ik_baggage_fields'] . ':' .
//            $config['interkassa']['secret']
//        );

        return $sign;
    }
}