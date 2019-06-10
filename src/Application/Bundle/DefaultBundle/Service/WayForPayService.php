<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\WayForPayLog;
use Application\Bundle\DefaultBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Translator;

/**
 * Class WayForPayService.
 */
class WayForPayService
{
    /** @var mixed */
    protected $stfalconConfig;
    protected $translator;
    protected $locale;
    protected $router;
    protected $securityToken;
/** @var Request|null */
    protected $request;

    /** @var EntityManager */
    protected $em;

    /**
     * @param mixed                 $stfalconConfig
     * @param Translator            $translator
     * @param RequestStack          $requestStack
     * @param Router                $router
     * @param TokenStorageInterface $securityToken
     * @param EntityManager         $em
     */
    public function __construct($stfalconConfig, Translator $translator, RequestStack $requestStack, Router $router, TokenStorageInterface $securityToken, EntityManager $em)
    {
        $this->stfalconConfig = $stfalconConfig;
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = null !== $this->request ? $this->request->getLocale() : 'uk';
        $this->router = $router;
        $this->securityToken = $securityToken;
        $this->em = $em;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getSignHash($params)
    {
        $signString = implode(';', $params);
        $signString = htmlspecialchars($signString, ENT_QUOTES);
        $sign = hash_hmac('md5', $signString, $this->stfalconConfig['wayforpay']['secret']);

        return $sign;
    }

    /**
     * @param array $response
     *
     * @return array|null
     */
    public function getResponseOnServiceUrl(array $response)
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
     * @param Payment|null $payment
     * @param array        $response
     * @param string       $fwdaysResponse
     */
    public function saveResponseLog($payment, array $response, $fwdaysResponse = null)
    {
        $logEntry = (new WayForPayLog())
            ->setPayment($payment)
            ->setStatus($this->getArrMean($response['transactionStatus'], 'empty'))
            ->setResponseData(\serialize($response))
            ->setFwdaysResponse($fwdaysResponse)
        ;
        $this->em->persist($logEntry);

        $this->em->flush($logEntry);
    }

    /**
     * @param Payment $payment
     * @param array   $response
     *
     * @return bool
     */
    public function checkPayment(Payment $payment, array $response)
    {
        if ($this->stfalconConfig['wayforpay']['shop_id'] === $this->getArrMean($response['merchantAccount']) &&
            (float) $this->getArrMean($response['amount']) === $payment->getAmount() &&
            'Approved' === $this->getArrMean($response['transactionStatus'])
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
            if (isset($response['merchantSignature'])) {
                return $response['merchantSignature'] === $this->getSignHash($params);
            }
        }

        return false;
    }

    /**
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

        $description = str_replace('\'', ' ', $description);

        if (mb_strlen($description) > 255) {
            $description = mb_substr($description, 0, 255);
        }

        $params = [
            'merchantAccount' => $this->stfalconConfig['wayforpay']['shop_id'],
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
//        $params['returnUrl'] = $this->router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $params['serviceUrl'] = $this->router->generate('payment_service_interaction', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $params;
    }

    /**
     * @param mixed  $var
     * @param string $default
     *
     * @return string
     */
    private function getArrMean(&$var, $default = '')
    {
        return isset($var) ? $var : $default;
    }
}
