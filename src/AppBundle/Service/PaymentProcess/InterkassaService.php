<?php

namespace App\Service\PaymentProcess;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Service\ReferralService;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * InterkassaService.
 */
class InterkassaService extends AbstractPaymentProcessService
{
    private const IK_SECURE_PAGE = 'https://sci.interkassa.com/';
    private const IK_TRANSACTION_APPROVED_STATUS = 'success';

    private const PAYMENT_SYSTEM_NAME = 'Interkassa';
    private const ORDER_NUMBER_KEY = 'ik_pm_no';

    protected $transactionStatus = [
        self::IK_TRANSACTION_APPROVED_STATUS => self::TRANSACTION_APPROVED_AND_SET_PAID_STATUS,
        self::TRANSACTION_STATUS_PENDING => self::TRANSACTION_STATUS_PENDING,
        self::TRANSACTION_STATUS_FAIL => self::TRANSACTION_STATUS_FAIL,
    ];

    protected $transactionStatusKey = 'ik_inv_st';
    protected $isOverrideCallbacks;

    /**
     * @param array               $appConfig
     * @param TranslatorInterface $translator
     * @param RequestStack        $requestStack
     * @param Router              $router
     * @param EntityManager       $em
     * @param Logger              $logger
     * @param ReferralService     $referralService
     * @param Session             $session
     * @param bool                $isOverrideCallbacks
     */
    public function __construct(array $appConfig, TranslatorInterface $translator, RequestStack $requestStack, Router $router, EntityManager $em, Logger $logger, ReferralService $referralService, Session $session, bool $isOverrideCallbacks)
    {
        parent::__construct($appConfig, $translator, $requestStack, $router, $em, $logger, $referralService, $session);

        $this->isOverrideCallbacks = $isOverrideCallbacks;
    }

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
        return false;
    }

    /**
     * @return bool
     */
    public function isAgreeWithConditionsRequired(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getFormAction(): string
    {
        return self::IK_SECURE_PAGE;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getSignHash(array $params): string
    {
        unset($params['ik_sign']);

        \ksort($params, SORT_STRING);
        \array_push($params, $this->appConfig['interkassa']['secret']);
        $signString = \implode(':', $params);
        $sign = \base64_encode(\md5($signString, true));

        return $sign;
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

        $description = \str_replace(['"', "'"], '-', $description);

        if (\mb_strlen($description) > 255) {
            $description = \mb_substr($description, 0, 255);
        }

        $params = [
            'ik_co_id' => $this->appConfig['interkassa']['shop_id'],
            self::ORDER_NUMBER_KEY => $payment->getId(),
            'ik_am' => $payment->getAmount(),
            'ik_cur' => 'uah',
            'ik_desc' => $description,
            'ik_loc' => $this->locale,
        ];

        if ($this->isOverrideCallbacks) {
            $params['ik_ia_u'] = $this->router->generate('payment_interaction', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $params['ik_suc_u'] = $this->router->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $params['ik_fal_u'] = $this->router->generate('payment_fail', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $params['ik_pnd_u'] = $this->router->generate('payment_pending', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $params['ik_sign'] = $this->getSignHash($params);

        return $params;
    }

    /**
     * @param array|null $data
     *
     * @return string
     */
    public function processData(?array $data): string
    {
        $this->assertArrayKeysExists([self::ORDER_NUMBER_KEY, 'ik_co_id', 'ik_am', 'ik_sign', $this->transactionStatusKey], $data);

        return $this->processSystemData($data, self::ORDER_NUMBER_KEY, Payment::INTERKASSA_GATE);
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
            'request_amount' => $data['ik_am'],
            'request_status' => $this->getStatusFromData($data),
            'is_hash_valid' => ($data['ik_sign'] === $this->getSignHash($data)),
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
        return $this->isValidShop($data) && (float) $data['ik_am'] === $payment->getAmount() &&
            $this->isApproved($data) && $data['ik_sign'] === $this->getSignHash($data)
        ;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function isApproved(array $data): bool
    {
        return self::TRANSACTION_APPROVED_AND_SET_PAID_STATUS === $this->getStatusFromData($data);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function isValidShop(array $data): bool
    {
        return $this->appConfig['interkassa']['shop_id'] === $data['ik_co_id'];
    }
}
