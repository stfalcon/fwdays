<?php

namespace Stfalcon\Bundle\EventBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\DependencyInjection\Container;
use Application\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Сервис для работы с реферальной программой
 */
class ReferralService
{
    const REFERRAL_CODE  = 'REFERRALCODE';
    const REFERRAL_BONUS = 100;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->request   = $this->container->get('request');
    }

    /**
     * Ger referral code
     *
     * @param User|null $user User
     *
     * @return mixed
     */
    public function getReferralCode($user = null)
    {
        if (is_null($user)) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        $referralCode = $user->getReferralCode();

        if (true === empty($referralCode)) {

            $user->setReferralCode(md5($user->getEmail()));
            $em = $this->container->get('doctrine.orm.default_entity_manager');

            $em->persist($user);
            $em->flush();
        }

        return $user->getReferralCode();
    }

    /**
     * Начисляет рефералы
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function chargingReferral() {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        if ($this->request->cookies->has(self::REFERRAL_CODE)) {

            $referralCode = $this->request->cookies->get(self::REFERRAL_CODE);

            //check self referral code
            if ($this->getReferralCode() === $referralCode) {
                return false;
            }

            /**
             * @var User $referralUser User
             */
            $referralUser = $em->getRepository('ApplicationUserBundle:User')
                ->findOneBy(['referralCode' => $referralCode]);

            if ($referralUser) {
                $balance = $referralUser->getBalance() + 100;
                $referralUser->setBalance($balance);

                $em->persist($referralUser);
                $em->flush();

                return true;
            }
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
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        //списываем реферальные средства если они были использованы
        if ($payment->getFwdaysAmount() > 0) {

            $user = $payment->getUser();
            $userBalance = $payment->getUser()->getBalance();
            $balance = $userBalance - $payment->getFwdaysAmount();
            $user->setBalance($balance);

            $em->persist($user);
            $em->flush();
            return true;
        }

        return false;
    }
}