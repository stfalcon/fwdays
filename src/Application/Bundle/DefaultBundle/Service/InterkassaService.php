<?php

namespace Application\Bundle\DefaultBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InterkassaService.
 */
class InterkassaService
{
    /** @var mixed */
    protected $stfalconConfig;

    /** @var Translator */
    protected $translator;

    /** @var string */
    protected $locale;

    /**
     * @param mixed        $stfalconConfig
     * @param Translator   $translator
     * @param RequestStack $requestStack
     */
    public function __construct($stfalconConfig, $translator, $requestStack)
    {
        $this->stfalconConfig = $stfalconConfig;
        $this->translator = $translator;
        $currentRequest = $requestStack->getCurrentRequest();
        $this->locale = null !== $currentRequest ? $currentRequest->getLocale() : 'uk';
    }

    /**
     * CRC-подпись для запроса на шлюз.
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

        // добавляем в конец массива "секретный ключ"
        array_push($params, $this->stfalconConfig['interkassa']['secret']);

        // конкатенируем значения через символ ":"
        $signString = implode(':', $params);
        // берем MD5 хэш в бинарном виде по сформированной строке и кодируем в BASE64
        $sign = base64_encode(md5($signString, true));

        return $sign;
    }

    /**
     * Проверка платежа.
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
        if ($this->stfalconConfig['interkassa']['shop_id'] == $request->get('ik_co_id') &&
            $request->get('ik_am') == $payment->getAmount() &&
            'success' == $request->get('ik_inv_st') &&
            $request->get('ik_sign') == $this->getSignHash($request->query->all())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает необходимые данные для формы оплаты.
     *
     * @param Payment $payment
     * @param Event   $event
     *
     * @return array
     */
    public function getData(Payment $payment, Event $event)
    {
        if (!$payment || !$event) {
            return [];
        }

        $usersId = '';
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            $usersId .= ','.$ticket->getUser()->getId();
        }
        $usersId = mb_substr($usersId, 1);

        $description = $this->translator->trans(
            'interkassa.payment.description',
            [
                '%event_name%' => $event->getName(),
                '%user_name%' => $payment->getUser()->getFullname(),
                '%user_id%' => $payment->getUser()->getId(),
                '%ids_array%' => $usersId,
            ]
        );

        if (mb_strlen($description) > 255) {
            $description = mb_substr($description, 0, 255);
        }

        $params['ik_co_id'] = $this->stfalconConfig['interkassa']['shop_id'];
        $params['ik_am'] = $payment->getAmount();
        $params['ik_pm_no'] = $payment->getId();
        $params['ik_desc'] = $description;
        $params['ik_loc'] = $this->locale;

        return [
            'ik_co_id' => $this->stfalconConfig['interkassa']['shop_id'],
            'ik_desc' => $description,
            'ik_loc' => $this->locale,
            'ik_sign' => $this->getSignHash($params),
        ];
    }
}
