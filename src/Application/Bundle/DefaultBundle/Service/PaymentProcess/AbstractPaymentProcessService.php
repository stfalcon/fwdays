<?php

declare(strict_types=1);

namespace Application\Bundle\DefaultBundle\Service\PaymentProcess;

use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\WayForPayLog;
use Application\Bundle\DefaultBundle\Service\ReferralService;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    protected const TRANSACTION_STATUS = [
        self::TRANSACTION_STATUS_PENDING => self::TRANSACTION_STATUS_PENDING,
        self::TRANSACTION_STATUS_FAIL => self::TRANSACTION_STATUS_FAIL,
    ];

    protected $appConfig;
    protected $translator;
    protected $request;
    protected $locale;
    protected $logger;
    protected $referralService;
    protected $em;
    protected $session;

    /**
     * @param array               $appConfig
     * @param TranslatorInterface $translator
     * @param RequestStack        $requestStack
     * @param EntityManager       $em
     * @param Logger              $logger
     * @param ReferralService     $referralService
     * @param Session             $session
     */
    public function __construct($appConfig, TranslatorInterface $translator, RequestStack $requestStack, EntityManager $em, Logger $logger, ReferralService $referralService, Session $session)
    {
        $this->appConfig = $appConfig;
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->locale = null !== $this->request ? $this->request->getLocale() : 'uk';
        $this->logger = $logger;
        $this->referralService = $referralService;
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * @param array $response
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $response): ?array
    {
        return [];
    }

    /**
     * @param array  $response
     * @param string $paymentIdKey
     * @param string $paymentGate
     *
     * @return string
     */
    protected function processSystemResponse(array $response, string $paymentIdKey, string $paymentGate): string
    {
        /** @var Payment $payment */
        $payment = $this->em
            ->getRepository('ApplicationDefaultBundle:Payment')
            ->find($response[$paymentIdKey])
        ;

        if (!$payment) {
            $this->logger->addCritical(\sprintf('%s interaction Fail! payment not found', $this->getSystemName()));
            $this->saveResponseLog(null, $response, \sprintf('%s: payment not found', $this->getSystemName()));

            throw new BadRequestHttpException('payment not found');
        }

        if ($payment->isPending() && $this->checkPayment($payment, $response)) {
            $payment->setPaidWithGate($paymentGate);

            $this->em->flush();

            $this->session->set(self::SESSION_PAYMENT_KEY, $response[$paymentIdKey]);
            $this->processReferral($payment, $response);
            $this->saveResponseLog($payment, $response, \sprintf('%s: set paid', $this->getSystemName()));

            return self::TRANSACTION_APPROVED_AND_SET_PAID_STATUS;
        }

        switch ($this->getStatusFromResponse($response)) {
            case self::TRANSACTION_STATUS[self::TRANSACTION_STATUS_PENDING]:
                $status = self::TRANSACTION_STATUS_PENDING;
                break;
            case self::TRANSACTION_STATUS[self::TRANSACTION_STATUS_FAIL]:
                $status = self::TRANSACTION_STATUS_FAIL;
                $this->logger->addCritical(\sprintf('%s interaction Fail!', $this->getSystemName()), $this->getRequestDataToArr($response, $payment));
                $this->saveResponseLog(null, $response, \sprintf('%s interaction Fail!', $this->getSystemName()));
                break;
            default:
                $status = $this->getStatusFromResponse($response, true);
        }

        return $status;
    }

    /**
     * @param Payment $payment
     * @param array   $response
     *
     * @return bool
     */
    abstract protected function checkPayment(Payment $payment, array $response): bool;

    /**
     * @param array        $response
     * @param Payment|null $payment
     *
     * @return array
     */
    abstract protected function getRequestDataToArr(array $response, ?Payment $payment): array;

    /**
     * @param array $response
     * @param bool  $isUnprocessedTransaction
     *
     * @return string
     */
    abstract protected function getStatusFromResponse(array $response, bool $isUnprocessedTransaction = false): string;

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
            $this->saveResponseLog(null, $checkArray, \sprintf('%s: bad content', $this->getSystemName()));
            throw new BadRequestHttpException('bad content');
        }

        foreach ($keysArray as $key) {
            if (!\array_key_exists($key, $checkArray)) {
                $this->logger->addCritical(\sprintf('%s interaction Fail! bad content', $this->getSystemName()));
                $this->saveResponseLog(null, $checkArray, \sprintf('%s: bad content', $this->getSystemName()));

                throw new BadRequestHttpException(\sprintf('response key %s not found', $key));
            }
        }
    }

    /**
     * @param Payment $payment
     * @param array   $response
     */
    protected function processReferral(Payment $payment, array $response): void
    {
        try {
            $this->referralService->chargingReferral($payment);
            $this->referralService->utilizeBalance($payment);
        } catch (\Exception $e) {
            $this->logger->addCritical(
                $e->getMessage(),
                $this->getRequestDataToArr($response, $payment)
            );
        }
    }

    /**
     * @param Payment     $payment
     * @param array|null  $response
     * @param string|null $fwdaysResponse
     */
    protected function saveResponseLog(Payment $payment, ?array $response, ?string $fwdaysResponse = null): void
    {
        $logEntry = (new WayForPayLog())
            ->setPayment($payment)
            ->setStatus($this->getStatusFromResponse($response))
            ->setResponseData(\serialize($response))
            ->setFwdaysResponse($fwdaysResponse)
        ;
        $this->em->persist($logEntry);

        $this->em->flush($logEntry);
    }
}
