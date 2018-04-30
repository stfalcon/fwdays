<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WayForPayService.
 */
class WayForPayService
{
    /** @var mixed */
    protected $stfalconConfig;

    /** @var Translator */
    protected $translator;

    /** @var string */
    protected $locale;

    /** @var Router */
    protected $router;

    /** @var TokenStorageInterface */
    protected $securityToken;

    /**
     * @param mixed                 $stfalconConfig
     * @param Translator            $translator
     * @param RequestStack          $requestStack
     * @param Router                $router
     * @param TokenStorageInterface $securityToken
     */
    public function __construct($stfalconConfig, $translator, $requestStack, $router, $securityToken)
    {
        $this->stfalconConfig = $stfalconConfig;
        $this->translator = $translator;
        $currentRequest = $requestStack->getCurrentRequest();
        $this->locale = null !== $currentRequest ? $currentRequest->getLocale() : 'uk';
        $this->router = $router;
        $this->securityToken = $securityToken;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getSignHash($params)
    {
        unset($params['merchantSignature']);
        $signString = implode(';', $params);
        $sign = hash_hmac("md5", $signString, $this->stfalconConfig['wayforpay']['secret']);

        return $sign;
    }

    /**
     * @param Payment $payment
     * @param Request $request
     *
     * @return bool
     */
    public function checkPayment(Payment $payment, Request $request)
    {
        if ($this->stfalconConfig['wayforpay']['shop_id'] === $request->get('merchantAccount') &&
            $request->get('amount') === $payment->getAmount() &&
            'Approved' === $request->get('transactionStatus')
        ) {
            $params = [
                'merchantAccount' => $request->get('merchantAccount'),
                'orderReference' => $request->get('orderReference'),
                'amount' => $request->get('amount'),
                'currency'  => $request->get('currency'),
                'authCode' => $request->get('authCode'),
                'cardPan' => $request->get('cardPan'),
                'transactionStatus' => $request->get('transactionStatus'),
                'reasonCode' => $request->get('reasonCode'),
            ];

            return $request->get('merchantSignature') === $this->getSignHash($params);
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

        if (mb_strlen($description) > 255) {
            $description = mb_substr($description, 0, 255);
        }

        $params = [
            'merchantAccount' => $this->stfalconConfig['wayforpay']['shop_id'],
            'merchantDomainName' => 'www.fwdays.com',
            'orderReference' => $payment->getId(),
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

        $params['authorizationType'] = "SimpleSignature";
        $params['merchantTransactionSecureType'] = "AUTO";
        $params['merchantTransactionType'] = "SALE";
        $params["clientFirstName"] = $payment->getUser()->getName();
        $params["clientLastName"] = $payment->getUser()->getSurname();
        $params["clientEmail"] = $payment->getUser()->getEmail();
        $params["clientPhone"] =  "380631234567";
        $params["defaultPaymentSystem"] = "card";
        $params["orderTimeout"] = "49000";
        $params["apiVersion"] = "1";
        $params["returnUrl"] = $this->router->generate('payment_interaction', [], UrlGeneratorInterface::ABSOLUTE_URL);
//        $params["serviceUrl"] = $this->router->generate('payment_interaction', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $params;
    }
}
