<?php

namespace Application\Bundle\DefaultBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Payment,
    Stfalcon\Bundle\EventBundle\Entity\Event;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

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
     * @param Payment $params Параметры на основвании которых строим подпись
     *
     * @return string
     */
    public function getSignHash($params)
    {
        // исключаем из параметров подпись
        unset($params['ik_sign']);
        // сортируем по ключам в алфавитном порядке элементы массива
        ksort($params, SORT_STRING);

        $config = $this->container->getParameter('stfalcon_event.config');
        // добавляем в конец массива "секретный ключ"
        array_push($params, $config['interkassa']['secret']);

        // конкатенируем значения через символ ":"
        $signString = implode(':', $params);
        // берем MD5 хэш в бинарном виде по сформированной строке и кодируем в BASE64
        $sign = base64_encode(md5($signString, true));

        return $sign;
    }

    /**
     * Проверка платежа
     *
     * Несмотря на то, что уведомление формируется на стороне SCI, ВСЕГДА проверяйте такую
     * информацию в уведомлении о платеже, как:
     * — Идентификатор кассы (параметр "ik_co_id"). Должен соответствовать Вашему идентификатору кассы.
     * — Сумма платежа (параметр "ik_am"). Должна соответствовать сумме Вашего заказа для которого был выставлен счет.
     * — Состояние платежа (параметр "ik_inv_st"). Должно соответствовать значению "success" (проведен).
     * — Цифровая подпись (параметр "ik_sign"). см. Формирование цифровой подписи.
     *
     * @param Payment $payment
     * @param Request $request
     *
     * @return bool
     */
    public function checkPayment(Payment $payment, Request $request)
    {
        $config = $this->container->getParameter('stfalcon_event.config');

        if ($request->get('ik_co_id') == $config['interkassa']['shop_id'] &&
            $request->get('ik_am') == $payment->getAmount() &&
            $request->get('ik_inv_st') == 'success' &&
            $request->get('ik_sign') == $this->getSignHash($request->query->all())
        ) {
            return true;
        }

        return false;
    }


    /**
     * Возвращает необходимые данные для формы оплаты
     *
     * @param Payment $payment
     * @param Event $event
     * @return array
     */
    public function getData(Payment $payment, Event $event)
    {

        $config = $this->container->getParameter('stfalcon_event.config');

        $description = 'Оплата участия в конференции '
            . $event->getName()
            . '. Плательщик '
            . $payment->getUser()->getFullname()
            . ' (#' . $payment->getUser()->getId()
            . ')';

        $params['ik_co_id'] = $config['interkassa']['shop_id'];
        $params['ik_am'] = $payment->getAmount();
        $params['ik_pm_no'] = $payment->getId();
        $params['ik_desc'] = $description;
        $params['ik_loc'] = 'ru';

        return [
            'ik_co_id' => $config['interkassa']['shop_id'],
            'ik_desc' => $description,
            'ik_sign' => $this->getSignHash($params)
        ];
    }
}