<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Сервис для работы с реферальной программой.
 */
class ReferralService
{
    const REFERRAL_COOKIE_NAME = 'REFERRALCODE';
    const REFERRAL_COOKIE_LIFETIME = 3600 * 24 * 365 * 10;
    const REFERRAL_BONUS = 100;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->request = $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * Ger referral code.
     *
     * @param User|null $user User
     *
     * @return mixed
     */
    public function getReferralCode($user = null)
    {
        if (null === $user) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }

        $referralCode = $user->getReferralCode();

        if (true === empty($referralCode)) {
            $user->setReferralCode(md5($user->getEmail().time()));
            $em = $this->container->get('doctrine.orm.default_entity_manager');

            $em->persist($user);
            $em->flush();
        }

        return $user->getReferralCode();
    }

    /**
     * Начисляет рефералы.
     *
     * @param Payment $payment
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function chargingReferral(Payment $payment)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $userReferral = $payment->getUser()->getUserReferral();

        if ($userReferral) {
            $balance = $userReferral->getBalance() + self::REFERRAL_BONUS;
            $userReferral->setBalance($balance);

            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    public function utilizeBalance(Payment $payment)
    {
        if ($payment->getFwdaysAmount() > 0) {
            $em = $this->container->get('doctrine.orm.default_entity_manager');
            $user = $payment->getUser();
            $userBalance = $payment->getUser()->getBalance();
            $balance = $userBalance - $payment->getFwdaysAmount();
            $user->setBalance($balance);

            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param string $referralCode
     *
     * @return User|null
     *
     * @throws \Exception
     */
    public function getUserByReferralCode($referralCode)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $referralUser = $em->getRepository(User::class)
            ->findOneBy(['referralCode' => $referralCode]);

        return $referralUser;
    }

    /**
     * Save ref code in cookies.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function handleRequest($request)
    {
        if ($request->query->has('ref')) {
            $code = $request->query->get('ref');

            //уже используется реф. код
            if (false == $request->cookies->has(self::REFERRAL_COOKIE_NAME)) {
                $user = $this->getUser();

                //user authorize
                if (null !== $user) {
                    if ($user->getReferralCode() == $code) {
                        return false;
                    }

                    $userReferral = $this->getUserByReferralCode($code);

                    if ($userReferral) {
                        $em = $this->container->get('doctrine.orm.default_entity_manager');

                        $user->setUserReferral($userReferral);

                        $em->persist($user);
                        $em->flush();
                    }
                }

                $response = new Response();
                $expire = time() + self::REFERRAL_COOKIE_LIFETIME;

                $response->headers->setCookie(new Cookie(self::REFERRAL_COOKIE_NAME, $code, $expire));
                $response->send();
            }
        }
    }

    /**
     * Get user.
     *
     * @return User|null
     *
     * @throws \Exception
     */
    private function getUser()
    {
        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}