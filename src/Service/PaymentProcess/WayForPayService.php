<?php

namespace App\Service\PaymentProcess;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * WayForPayService.
 */
class WayForPayService extends AbstractPaymentProcessService
{
    public const WFP_PAY_BY_WIDGET = 'wfp_pay_widget';
    public const WFP_PAY_BY_SECURE_PAGE = 'wfp_pay_secure_page';

    public const WFP_TRANSACTION_APPROVED_STATUS = 'Approved';

    private const WFP_TRANSACTION_PENDING_STATUS = 'Pending';
    private const WFP_TRANSACTION_IN_PROCESSING_STATUS = 'InProcessing';
    private const WFP_TRANSACTION_WAITING_AUTO_COMPLETE_STATUS = 'WaitingAuthComplete';
    private const WFP_TRANSACTION_REFUND_IN_PROCESSING_STATUS = 'RefundInProcessing';

    private const WFP_TRANSACTION_FAIL_STATUS = 'Fail';
    private const WFP_TRANSACTION_EXPIRED_STATUS = 'Expired';
    private const WFP_TRANSACTION_DECLINED_STATUS = 'Declined';
    private const WFP_TRANSACTION_REFUNDED_STATUS = 'Refunded/Voided';

    private const ORDER_NUMBER_KEY = 'orderNo';

    /** @var array */
    protected $transactionStatus = [
        self::WFP_TRANSACTION_APPROVED_STATUS => self::TRANSACTION_APPROVED_AND_SET_PAID_STATUS,

        self::WFP_TRANSACTION_PENDING_STATUS => self::TRANSACTION_STATUS_PENDING,
        self::WFP_TRANSACTION_IN_PROCESSING_STATUS => self::TRANSACTION_STATUS_PENDING,
        self::WFP_TRANSACTION_WAITING_AUTO_COMPLETE_STATUS => self::TRANSACTION_STATUS_PENDING,
        self::WFP_TRANSACTION_REFUND_IN_PROCESSING_STATUS => self::TRANSACTION_STATUS_PENDING,

        self::WFP_TRANSACTION_FAIL_STATUS => self::TRANSACTION_STATUS_FAIL,
        self::WFP_TRANSACTION_EXPIRED_STATUS => self::TRANSACTION_STATUS_FAIL,
        self::WFP_TRANSACTION_REFUNDED_STATUS => self::TRANSACTION_STATUS_FAIL,
        self::WFP_TRANSACTION_DECLINED_STATUS => self::TRANSACTION_STATUS_FAIL,
        self::TRANSACTION_STATUS_FAIL => self::TRANSACTION_STATUS_FAIL,
    ];

    private const PAYMENT_SYSTEM_NAME = 'WayForPay';
    private const WFP_SECURE_PAGE = 'https://secure.wayforpay.com/pay';

    protected $transactionStatusKey = 'transactionStatus';

    /**
     * @param array $data
     *
     * @return string|null
     */
    public function getPaymentIdFromData(array $data): ?string
    {
        $this->assertArrayKeysExists([self::ORDER_NUMBER_KEY], $data);

        return $data[self::ORDER_NUMBER_KEY];
    }

    /**
     * @return bool
     */
    public function isUseRedirectByStatus(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAgreeWithConditionsRequired(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getFormAction(): string
    {
        return self::WFP_SECURE_PAGE;
    }

    /**
     * @param array $data
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $data): ?array
    {
        $result = null;

        if (isset($data['orderReference'])) {
            $result = [
                'orderReference' => $data['orderReference'],
                'status' => 'accept',
                'time' => (int) (new \DateTime())->getTimestamp(),
            ];

            $result['signature'] = $this->getSignHash($result);
        }

        return $result;
    }

    /**
     * @param Payment $payment
     * @param Event   $event
     *
     * @return array
     */
    public function getData(Payment $payment, Event $event): array
    {
        $userIds = [];
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            $userIds[] = $ticket->getUser()->getId();
        }

        $description = $this->translator->trans(
            'interkassa.payment.description',
            [
                '%event_name%' => $event->getName(),
                '%user_name%' => $payment->getUser()->getFullname(),
                '%user_id%' => $payment->getUser()->getId(),
                '%ids_array%' => \implode(',', $userIds),
            ]
        );

        $description = \str_replace('\'', ' ', $description);

        if (\mb_strlen($description) > 255) {
            $description = \mb_substr($description, 0, 255);
        }

        $params = [
            'merchantAccount' => $this->appConfig['wayforpay']['shop_id'],
            'merchantDomainName' => $this->getRequest()->getSchemeAndHttpHost(),
            'orderReference' => $payment->getId().'-'.(new \DateTime())->getTimestamp(),
            'orderDate' => $payment->getCreatedAt()->getTimestamp(),
            'amount' => $payment->getAmount(),
            'currency' => 'UAH',
            'productName' => $description,
            'productCount' => 1,
            'productPrice' => $payment->getAmount(),
        ];

        $params['merchantSignature'] = $this->getSignHash($params);

        $user = $payment->getUser();

        if (null !== $user->getRecToken()) {
            $params['recToken'] = $user->getRecToken();
        }

        $params['authorizationType'] = 'SimpleSignature';
        $params['merchantTransactionSecureType'] = 'AUTO';
        $params['merchantTransactionType'] = 'SALE';
        $params[self::ORDER_NUMBER_KEY] = $payment->getId();
        $params['clientFirstName'] = $user->getName();
        $params['clientLastName'] = $user->getSurname();
        $params['clientEmail'] = $user->getEmail();
        $params['clientPhone'] = $user->getPhone();
        $params['language'] = 'uk' === $this->getCurrentLocale() ? 'ua' : $this->getCurrentLocale();
        $params['defaultPaymentSystem'] = 'card';
        $params['orderTimeout'] = '49000';
        $params['returnUrl'] = $this->router->generate('payment_interaction', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $params['serviceUrl'] = $this->router->generate('payment_service_interaction', ['_locale' => 'uk'], UrlGeneratorInterface::ABSOLUTE_URL);

        return $params;
    }

    /**
     * @param array|null $data
     *
     * @return string
     */
    public function processData(?array $data): string
    {
        $this->assertArrayKeysExists([$this->transactionStatusKey, self::ORDER_NUMBER_KEY, 'merchantSignature'], $data);

        return $this->processSystemData($data, self::ORDER_NUMBER_KEY, Payment::WAYFORPAY_GATE);
    }

    /**
     * @return string
     */
    public function getOrderNumberKey(): string
    {
        return self::ORDER_NUMBER_KEY;
    }

    /**
     * @return string
     */
    protected function getSystemName(): string
    {
        return self::PAYMENT_SYSTEM_NAME;
    }

    /**
     * @param array        $data
     * @param Payment|null $payment
     *
     * @return array
     */
    protected function getRequestDataToArr(array $data, ?Payment $payment): array
    {
        $paymentId = '-';
        $paymentStatus = '-';
        $paymentAmount = '-';

        if ($payment instanceof Payment) {
            $paymentId = $payment->getId();
            $paymentStatus = $payment->getStatus();
            $paymentAmount = $payment->getAmount();
        }

        return [
            'payment_id' => $paymentId,
            'payment_status' => $paymentStatus,
            'payment_amount' => $paymentAmount,
            'request_amount' => $this->getArrMean($data['amount']),
            'request_status' => $this->getArrMean($data['reasonCode']).' '.$this->getArrMean($data['reason']),
        ];
    }

    /**
     * @param Payment $payment
     * @param array   $data
     *
     * @return bool
     */
    protected function checkPayment(Payment $payment, array $data): bool
    {
        if ($this->appConfig['wayforpay']['shop_id'] === $this->getArrMean($data['merchantAccount']) &&
            self::WFP_TRANSACTION_APPROVED_STATUS === $this->getArrMean($data[$this->transactionStatusKey]) &&
            (float) $this->getArrMean($data['amount']) === $payment->getAmount()
        ) {
            $params = [
                'merchantAccount' => $this->getArrMean($data['merchantAccount']),
                'orderReference' => $this->getArrMean($data['orderReference']),
                'amount' => $this->getArrMean($data['amount']),
                'currency' => $this->getArrMean($data['currency']),
                'authCode' => $this->getArrMean($data['authCode']),
                'cardPan' => $this->getArrMean($data['cardPan']),
                $this->transactionStatusKey => $this->getArrMean($data[$this->transactionStatusKey]),
                'reasonCode' => $this->getArrMean($data['reasonCode']),
            ];

            return $data['merchantSignature'] === $this->getSignHash($params);
        }

        return false;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function getSignHash(array $params): string
    {
        $signString = \implode(';', $params);
        $signString = \htmlspecialchars($signString, ENT_QUOTES);

        return \hash_hmac('md5', $signString, $this->appConfig['wayforpay']['secret']);
    }
}
