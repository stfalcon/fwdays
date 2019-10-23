<?php

namespace Application\Bundle\DefaultBundle\Service\PaymentProcess;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Service\ReferralService;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * WayForPayService.
 */
class WayForPayService extends AbstractPaymentProcessService
{
    public const WFP_PAY_BY_WIDGET = 'wfp_pay_widget';
    public const WFP_PAY_BY_SECURE_PAGE = 'wfp_pay_secure_page';

    private const WFP_TRANSACTION_APPROVED_STATUS = 'Approved';
    private const WFP_TRANSACTION_PENDING_STATUS = 'Pending';
    private const WFP_TRANSACTION_FAIL_STATUS = 'Fail';

    protected const TRANSACTION_STATUS = [
        self::TRANSACTION_STATUS_PENDING => self::WFP_TRANSACTION_PENDING_STATUS,
        self::TRANSACTION_STATUS_FAIL => self::WFP_TRANSACTION_FAIL_STATUS,
    ];

    private const PAYMENT_SYSTEM_NAME = 'WayForPay';

    private const WFP_SECURE_PAGE = 'https://secure.wayforpay.com/pay';

    protected $securityToken;

    /**
     * @param array                 $appConfig
     * @param TranslatorInterface   $translator
     * @param RequestStack          $requestStack
     * @param Router                $router
     * @param TokenStorageInterface $securityToken
     * @param EntityManager         $em
     * @param Logger                $logger
     * @param ReferralService       $referralService
     * @param Session               $session
     */
    public function __construct(array $appConfig, TranslatorInterface $translator, RequestStack $requestStack, Router $router, TokenStorageInterface $securityToken, EntityManager $em, Logger $logger, ReferralService $referralService, Session $session)
    {
        parent::__construct($appConfig, $translator, $requestStack, $router, $em, $logger, $referralService, $session);

        $this->securityToken = $securityToken;
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
     * @param array $response
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $response): ?array
    {
        $result = null;

        if (isset($response['orderReference'])) {
            $result = [
                'orderReference' => $response['orderReference'],
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
        $usersId = '';
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            $usersId .= ','.$ticket->getUser()->getId();
        }
        $usersId = \mb_substr($usersId, 1);

        $description = $this->translator->trans(
            'interkassa.payment.description',
            [
                '%event_name%' => $event->getName(),
                '%user_name%' => $payment->getUser()->getFullname(),
                '%user_id%' => $payment->getUser()->getId(),
                '%ids_array%' => $usersId,
            ]
        );

        $description = \str_replace('\'', ' ', $description);

        if (\mb_strlen($description) > 255) {
            $description = \mb_substr($description, 0, 255);
        }

        $params = [
            'merchantAccount' => $this->appConfig['wayforpay']['shop_id'],
            'merchantDomainName' => $this->request->getSchemeAndHttpHost(),
            'orderReference' => $payment->getId().'-'.(new \DateTime())->getTimestamp(),
            'orderDate' => $payment->getCreatedAt()->getTimestamp(),
            'amount' => $payment->getAmount(),
            'currency' => 'UAH',
            'productName' => $description,
            'productCount' => 1,
            'productPrice' => $payment->getAmount(),
        ];

        $params['merchantSignature'] = $this->getSignHash($params);

        $user = $this->securityToken->getToken()->getUser();

        if ($user instanceof User && null !== $user->getRecToken()) {
            $params['recToken'] = $user->getRecToken();
        }

        $params['authorizationType'] = 'SimpleSignature';
        $params['merchantTransactionSecureType'] = 'AUTO';
        $params['merchantTransactionType'] = 'SALE';
        $params['orderNo'] = $payment->getId();
        $params['clientFirstName'] = $payment->getUser()->getName();
        $params['clientLastName'] = $payment->getUser()->getSurname();
        $params['clientEmail'] = $payment->getUser()->getEmail();
        $params['clientPhone'] = $payment->getUser()->getPhone();
        $params['language'] = 'uk' === $this->locale ? 'ua' : $this->locale;
        $params['defaultPaymentSystem'] = 'card';
        $params['orderTimeout'] = '49000';
        $params['returnUrl'] = $this->router->generate('payment_interaction', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $params['serviceUrl'] = $this->router->generate('payment_service_interaction', ['_locale' => 'uk'], UrlGeneratorInterface::ABSOLUTE_URL);

        return $params;
    }

    /**
     * @param array|null $response
     *
     * @return string
     */
    public function processResponse(?array $response): string
    {
        $this->assertArrayKeysExists(['transactionStatus', 'orderNo', 'merchantSignature'], $response);

        return $this->processSystemResponse($response, 'orderNo', Payment::WAYFORPAY_GATE);
    }

    /**
     * @return string
     */
    protected function getSystemName(): string
    {
        return self::PAYMENT_SYSTEM_NAME;
    }

    /**
     * @return array
     */
    protected function getTransactionStatus(): array
    {
        return self::TRANSACTION_STATUS;
    }

    /**
     * @param array $response
     * @param bool  $isUnprocessedTransaction
     *
     * @return string
     */
    protected function getStatusFromResponse(array $response, bool $isUnprocessedTransaction = false): string
    {
        return $response['transactionStatus'] ?? self::TRANSACTION_STATUS_FAIL;
    }

    /**
     * @param array        $response
     * @param Payment|null $payment
     *
     * @return array
     */
    protected function getRequestDataToArr(array $response, ?Payment $payment): array
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
            'request_amount' => $this->getArrMean($response['amount']),
            'request_status' => $this->getArrMean($response['reasonCode']).' '.$this->getArrMean($response['reason']),
        ];
    }

    /**
     * @param Payment $payment
     * @param array   $response
     *
     * @return bool
     */
    protected function checkPayment(Payment $payment, array $response): bool
    {
        if ($this->appConfig['wayforpay']['shop_id'] === $this->getArrMean($response['merchantAccount']) &&
            (float) $this->getArrMean($response['amount']) === $payment->getAmount() &&
            self::WFP_TRANSACTION_APPROVED_STATUS === $this->getArrMean($response['transactionStatus'])
        ) {
            $params = [
                'merchantAccount' => $this->getArrMean($response['merchantAccount']),
                'orderReference' => $this->getArrMean($response['orderReference']),
                'amount' => $this->getArrMean($response['amount']),
                'currency' => $this->getArrMean($response['currency']),
                'authCode' => $this->getArrMean($response['authCode']),
                'cardPan' => $this->getArrMean($response['cardPan']),
                'transactionStatus' => $this->getArrMean($response['transactionStatus']),
                'reasonCode' => $this->getArrMean($response['reasonCode']),
            ];

            return $response['merchantSignature'] === $this->getSignHash($params);
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
        $sign = \hash_hmac('md5', $signString, $this->appConfig['wayforpay']['secret']);

        return $sign;
    }
}
