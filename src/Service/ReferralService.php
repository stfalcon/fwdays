<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\Ticket;
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

    const REFERRAL_CODE = 'REFERRALCODE';
    const REFERRAL_BONUS = 100;
    const SPECIAL_REFERRAL_BONUS = 500;
    const SPECIAL_BONUS_EVENT = 'js-fwdays-2019';

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

        if ($userReferral instanceof User) {
            $tickets = $payment->getTickets();
            /** @var Ticket $firstTicket */
            $firstTicket = $tickets->count() > 0 ? $tickets[0] : null;
            $bonus = (null !== $firstTicket && self::SPECIAL_BONUS_EVENT === $firstTicket->getEvent()->getSlug()) ? self::SPECIAL_REFERRAL_BONUS : self::REFERRAL_BONUS;

            $balance = $userReferral->getBalance() + $bonus;
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

            if (false === $request->cookies->has(self::REFERRAL_CODE)) {
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
                $expire = time() + (10 * 365 * 24 * 3600);
                //@todo check this
                $response->headers->setCookie(new Cookie(self::REFERRAL_CODE, $code, $expire));
                $response->send();
            }
        }
    }
}
