<?php

namespace Stfalcon\Bundle\PaymentBundle\Service;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;
use Symfony\Component\DependencyInjection\Container;
use Zend\Validator\File\Md5;

/**
 * Class InterkassaService
 */
class InterkassaService
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
     * CRC-подпись для запроса на шлюз
     *
     * @param Payment $params Параметры на основвание которых строим подпись
     *
     * @return string
     */
    public function getSignHash($params)
    {
        $config = $this->container->getParameter('stfalcon_payment.config');

        // сортируем по ключам в алфавитном порядке элементы массива
        ksort($params, SORT_STRING);
        // добавляем в конец массива "секретный ключ"
        array_push($params, $config['interkassa']['secret']);
        // конкатенируем значения через символ ":"
        $signString = implode(':', $params);
        // берем MD5 хэш в бинарном виде по сформированной строке и кодируем в BASE64
        $sign = base64_encode(md5($signString, true));

        return $sign;
    }
}