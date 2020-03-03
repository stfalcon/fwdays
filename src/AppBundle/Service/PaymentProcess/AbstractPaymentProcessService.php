<?php

declare(strict_types=1);

namespace App\Service\PaymentProcess;

use App\Entity\Payment;
use App\Entity\WayForPayLog;
use App\Exception\UnprocessedPaymentStatusException;
use App\Service\ReferralService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * AbstractPaymentProcessService.
 */
abstract class AbstractPaymentProcessService implements PaymentProcessInterface
{
    public const SESSION_PAYMENT_KEY = 'session_payment';

    public const TRANSACTION_APPROVED_AND_SET_PAID_STATUS = 'approved_and_set_paid';
    public const TRANSACTION_STATUS_PENDING = 'pending';
    public const TRANSACTION_STATUS_FAIL = 'fail';

    protected $transactionStatus = [];

    /** @var string */
    protected $transactionStatusKey = '';
    protected $appConfig;
    protected $translator;
    protected $request;
    protected $locale;
    protected $logger;
    protected $referralService;
    protected $em;
    protected $session;
    protected $router;

    /**
     * @param array               $appConfig
     * @param TranslatorInterface $translator
     * @param RequestStack        $requestStack
     * @param Router              $router
     * @param EntityManager       $em
     * @param Logger              $logger
     * @param ReferralService     $referralService
     * @param Session             $session
     */
    public function __construct($appConfig, TranslatorInterface $translator, RequestStack $requestStack, Router $router, EntityManager $em, Logger $logger, ReferralService $referralService, Session $session)
    {
        $this->appConfig = $appConfig;
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->locale = null !== $this->request ? $this->request->getLocale() : 'uk';
        $this->logger = $logger;
        $this->referralService = $referralService;
        $this->em = $em;
        $this->session = $session;
        $this->router = $router;
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
     * @return string
     */
    abstract public function getOrderNumberKey(): string;

    /**
     * @param array  $data
     * @param string $paymentIdKey
     * @param string $paymentGate
     *
     * @return string
     *
     * @throws UnprocessedPaymentStatusException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function processSystemData(array $data, string $paymentIdKey, string $paymentGate): string
    {
        /** @var Payment $payment */
        $payment = $this->em
            ->getRepository(Payment::class)
            ->find($data[$paymentIdKey])
        ;

        if (!$payment) {
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

        $status = $this->getStatusFromData($data);
        $this->saveDataLog(null, $data, \sprintf('%s status %s!', $this->getSystemName(), $status));

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
     * @return string
     */
    abstract protected function getSystemName(): string;

    /**
     * @param array $data
     *
     * @return string
     *
     * @throws UnprocessedPaymentStatusException
     */
    protected function getStatusFromData(array $data): string
    {
        $status = $data[$this->transactionStatusKey] ?? self::TRANSACTION_STATUS_FAIL;

        if (isset($this->transactionStatus[$status])) {
            return $this->transactionStatus[$status];
        }

        $fwdaysResponse = \sprintf('%s status %s!', $this->getSystemName(), $status);
        $this->saveDataLog(null, $data, $fwdaysResponse, 'unprocessed');

        throw new UnprocessedPaymentStatusException($status, $this->getSystemName());
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
            $this->saveDataLog(null, $checkArray, \sprintf('%s: bad content', $this->getSystemName()));
            throw new BadRequestHttpException('bad content');
        }

        foreach ($keysArray as $key) {
            if (!\array_key_exists($key, $checkArray)) {
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
     * @param string|null  $status
     */
    protected function saveDataLog(?Payment $payment, ?array $data, ?string $fwdaysResponse = null, string $status = null): void
    {
        $status = null === $status ? $this->getStatusFromData($data) : $status;

        $logEntry = (new WayForPayLog())
            ->setPayment($payment)
            ->setStatus($status)
            ->setResponseData(\serialize($data))
            ->setFwdaysResponse($fwdaysResponse)
        ;
        $this->em->persist($logEntry);

        $this->em->flush($logEntry);
    }
}
