<?php

namespace Application\Bundle\DefaultBundle\Service\PaymentProcess;

use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Service\ReferralService;
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
     * @param array $response
     *
     * @return string|null
     */
    public function getPaymentIdFromResponse(array $response): ?string
    {
        $this->assertArrayKeysExists(['ik_pm_no', 'ik_co_id', 'ik_inv_st'], $response);

        return $this->isValidShop($response) && $this->isApproved($response) ? $response['ik_pm_no'] : null;
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

        $description = \str_replace(['"', "'"], '-', $description);

        if (\mb_strlen($description) > 255) {
            $description = \mb_substr($description, 0, 255);
        }

        $params = [
            'ik_co_id' => $this->appConfig['interkassa']['shop_id'],
            'ik_pm_no' => $payment->getId(),
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
     * @param array|null $response
     *
     * @return string
     */
    public function processResponse(?array $response): string
    {
        $this->assertArrayKeysExists(['ik_pm_no', 'ik_co_id', 'ik_am', 'ik_sign', 'ik_inv_st'], $response);

        return $this->processSystemResponse($response, 'ik_pm_no', Payment::INTERKASSA_GATE);
    }

    /**
     * @param array $response
     *
     * @return bool
     */
    private function isApproved(array $response): bool
    {
        return self::IK_TRANSACTION_APPROVED_STATUS === $this->getStatusFromResponse($response);
    }

    /**
     * @param array $response
     *
     * @return bool
     */
    private function isValidShop(array $response): bool
    {
        return $this->appConfig['interkassa']['shop_id'] === $response['ik_co_id'];
    }

    /**
     * @return string
     */
    protected function getSystemName(): string
    {
        return self::PAYMENT_SYSTEM_NAME;
    }

    /**
     * @param array $response
     * @param bool  $isUnprocessedTransaction
     *
     * @return string
     */
    protected function getStatusFromResponse(array $response, bool $isUnprocessedTransaction = false): string
    {
        if (!isset($response['ik_inv_st']) || $isUnprocessedTransaction) {
            return self::TRANSACTION_STATUS_FAIL;
        }

        return $response['ik_inv_st'];
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
            'request_amount' => $response['ik_am'],
            'request_status' => $this->getStatusFromResponse($response),
            'is_hash_valid' => ($response['ik_sign'] === $this->getSignHash($response)),
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
        if ($this->isValidShop($response) &&
            (float) $response['ik_am'] === $payment->getAmount() &&
            $this->isApproved($response) &&
            $response['ik_sign'] === $this->getSignHash($response)
        ) {
            return true;
        }

        return false;
    }
}
