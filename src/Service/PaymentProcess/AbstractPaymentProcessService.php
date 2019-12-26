<?php

declare(strict_types=1);

namespace App\Service\PaymentProcess;

use App\Entity\Payment;
use App\Entity\WayForPayLog;
use App\Service\ReferralService;
use App\Traits;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * AbstractPaymentProcessService.
 */
abstract class AbstractPaymentProcessService implements PaymentProcessInterface
{
    use Traits\RouterTrait;
    use Traits\TranslatorTrait;
    use Traits\EntityManagerTrait;
    use Traits\LoggerTrait;
    use Traits\SessionTrait;
    use Traits\RequestStackTrait;

    public const SESSION_PAYMENT_KEY = 'session_payment';

    public const TRANSACTION_APPROVED_AND_SET_PAID_STATUS = 'approved_and_set_paid';
    public const TRANSACTION_STATUS_PENDING = 'pending';
    public const TRANSACTION_STATUS_FAIL = 'fail';

    protected const TRANSACTION_STATUS = [
        self::TRANSACTION_STATUS_PENDING => self::TRANSACTION_STATUS_PENDING,
        self::TRANSACTION_STATUS_FAIL => self::TRANSACTION_STATUS_FAIL,
    ];

    /** @var array  */
    protected $appConfig;

    /** @var ReferralService */
    protected $referralService;

    /**
     * @param array           $appConfig
     * @param ReferralService $referralService
     */
    public function __construct(array $appConfig, ReferralService $referralService)
    {
        $this->appConfig = $appConfig;
        $this->referralService = $referralService;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return string
     */
    public function getCurrentLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return null !== $request ? $request->getLocale() : 'uk';
    }

    /**
     * @param array $data
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $data): ?array
    {
        return [];
    }

    /**
     * @param array  $data
     * @param string $paymentIdKey
     * @param string $paymentGate
     *
     * @return string
     */
    protected function processSystemData(array $data, string $paymentIdKey, string $paymentGate): string
    {
        /** @var Payment|null $payment */
        $payment = $this->em
            ->getRepository(Payment::class)
            ->find($data[$paymentIdKey])
        ;

        if (!$payment instanceof Payment) {
            $this->logger->addCritical(\sprintf('%s interaction Fail! payment not found', $this->getSystemName()));
            $this->saveDataLog(null, $data, \sprintf('%s: payment not found', $this->getSystemName()));

            throw new BadRequestHttpException('payment not found');
        }

        if ($payment->isPending() && $this->checkPayment($payment, $data)) {
            $payment->setPaidWithGate($paymentGate);

            $this->em->flush();

            $this->session->set(self::SESSION_PAYMENT_KEY, $data[$paymentIdKey]);
            $this->processReferral($payment, $data);
            $this->saveDataLog($payment, $data, \sprintf('%s: set paid', $this->getSystemName()));

            return self::TRANSACTION_APPROVED_AND_SET_PAID_STATUS;
        }

        $transactionStatus = $this->getTransactionStatus();

        switch ($this->getStatusFromData($data)) {
            case $transactionStatus[self::TRANSACTION_STATUS_PENDING]:
                $status = self::TRANSACTION_STATUS_PENDING;
                break;
            case $transactionStatus[self::TRANSACTION_STATUS_FAIL]:
                $status = self::TRANSACTION_STATUS_FAIL;
                $this->logger->addCritical(\sprintf('%s interaction Fail!', $this->getSystemName()), $this->getRequestDataToArr($data, $payment));
                $this->saveDataLog(null, $data, \sprintf('%s interaction Fail!', $this->getSystemName()));
                break;
            default:
                $status = $this->getStatusFromData($data, true);
        }

        return $status;
    }

    /**
     * @param Payment $payment
     * @param array   $data
     *
     * @return bool
     */
    abstract protected function checkPayment(Payment $payment, array $data): bool;

    /**
     * @param array        $data
     * @param Payment|null $payment
     *
     * @return array
     */
    abstract protected function getRequestDataToArr(array $data, ?Payment $payment): array;

    /**
     * @param array $data
     * @param bool  $isUnprocessedTransaction
     *
     * @return string
     */
    abstract protected function getStatusFromData(array $data, bool $isUnprocessedTransaction = false): string;

    /**
     * @return string
     */
    abstract protected function getSystemName(): string;

    /**
     * @return array
     */
    protected function getTransactionStatus(): array
    {
        return self::TRANSACTION_STATUS;
    }

    /**
     * @param mixed  $var
     * @param string $default
     *
     * @return string
     */
    protected function getArrMean(&$var, $default = '')
    {
        return isset($var) ? $var : $default;
    }

    /**
     * @param array $keysArray
     * @param array $checkArray
     */
    protected function assertArrayKeysExists(array $keysArray, ?array $checkArray): void
    {
        if (!\is_array($checkArray)) {
            $this->logger->addCritical(\sprintf('%s interaction Fail! bad content', $this->getSystemName()));
            $this->saveDataLog(null, $checkArray, \sprintf('%s: bad content', $this->getSystemName()));
            throw new BadRequestHttpException('bad content');
        }

        foreach ($keysArray as $key) {
            if (!\array_key_exists($key, $checkArray)) {
                $this->logger->addCritical(\sprintf('%s interaction Fail! bad content', $this->getSystemName()));
                $this->saveDataLog(null, $checkArray, \sprintf('%s: bad content', $this->getSystemName()));

                throw new BadRequestHttpException(\sprintf('data key %s not found', $key));
            }
        }
    }

    /**
     * @param Payment $payment
     * @param array   $data
     */
    protected function processReferral(Payment $payment, array $data): void
    {
        try {
            $this->referralService->chargingReferral($payment);
            $this->referralService->utilizeBalance($payment);
        } catch (\Exception $e) {
            $this->logger->addCritical(
                $e->getMessage(),
                $this->getRequestDataToArr($data, $payment)
            );
        }
    }

    /**
     * @param Payment|null $payment
     * @param array|null   $data
     * @param string|null  $fwdaysResponse
     */
    protected function saveDataLog(?Payment $payment, ?array $data, ?string $fwdaysResponse = null): void
    {
        $logEntry = (new WayForPayLog())
            ->setPayment($payment)
            ->setStatus($this->getStatusFromData($data))
            ->setResponseData(\serialize($data))
            ->setFwdaysResponse($fwdaysResponse)
        ;
        $this->em->persist($logEntry);

        $this->em->flush($logEntry);
    }
}
