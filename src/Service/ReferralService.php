<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use App\Traits\EntityManagerTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Сервис для работы с реферальной программой.
 */
class ReferralService
{
    use EntityManagerTrait;

    const REFERRAL_COOKIE_NAME = 'REFERRALCODE';
    const REFERRAL_COOKIE_LIFETIME = 3600 * 24 * 365 * 10;
    const REFERRAL_BONUS = 100;

    private $userService;
    private $userRepository;

    /**
     * @param UserService    $userService
     * @param UserRepository $userRepository
     */
    public function __construct(UserService $userService, UserRepository $userRepository)
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
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
            $user = $this->userService->getCurrentUser();
        }

        $referralCode = $user->getReferralCode();

        if (true === empty($referralCode)) {
            $user->setReferralCode(\md5($user->getEmail().time()));

            $this->persistAndFlush($user);
        }

        return $user->getReferralCode();
    }

    /**
     * @param Payment $payment
     *
     * @throws \Exception
     */
    public function chargingReferral(Payment $payment): void
    {
        $userReferral = $payment->getUser()->getUserReferral();

        if ($userReferral) {
            $balance = $userReferral->getBalance() + self::REFERRAL_BONUS;
            $userReferral->setBalance($balance);
            $this->em->flush();
        }
    }

    /**
     * @param Payment $payment
     */
    public function utilizeBalance(Payment $payment): void
    {
        if ($payment->getFwdaysAmount() > 0) {
            $user = $payment->getUser();
            $userBalance = $payment->getUser()->getBalance();
            $balance = $userBalance - $payment->getFwdaysAmount();
            $user->setBalance($balance);

            $this->em->flush();
        }
    }

    /**
     * @param string $referralCode
     *
     * @return User|null
     *
     * @throws \Exception
     */
    public function getUserByReferralCode(string $referralCode): ?User
    {
        return $this->userRepository->findOneBy(['referralCode' => $referralCode]);
    }

    /**
     * Save ref code in cookies.
     *
     * @param Request $request
     */
    public function handleRequest(Request $request): void
    {
        if ($request->query->has('ref')) {
            $code = $request->query->get('ref');

            //уже используется реф. код
            if (false === $request->cookies->has(self::REFERRAL_COOKIE_NAME)) {
                $user = $this->userService->getCurrentUser(UserService::RESULT_RETURN_IF_NULL);

                if ($user instanceof User) {
                    if ($user->getReferralCode() == $code) {
                        return;
                    }

                    $userReferral = $this->getUserByReferralCode($code);

                    if ($userReferral) {
                        $user->setUserReferral($userReferral);

                        $this->persistAndFlush($user);
                    }
                }

                $response = new Response();
                $expire = time() + self::REFERRAL_COOKIE_LIFETIME;

                $response->headers->setCookie(new Cookie(self::REFERRAL_COOKIE_NAME, $code, $expire));
                $response->send();
            }
        }
    }
}
