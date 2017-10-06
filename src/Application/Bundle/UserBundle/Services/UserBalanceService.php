<?php

namespace Application\Bundle\UserBundle\Services;

use Application\Bundle\UserBundle\Entity\User;
use Application\Bundle\UserBundle\Entity\UserBalance;
use Doctrine\ORM\EntityManager;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Service\ReferralService;

class UserBalanceService
{
    const INCOME = 1;
    const OUTCOME = -1;

    /** @var  EntityManager */
    private $em;

    public function __construct($entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Add motion for user balance
     *
     * @param Payment $payment
     * @param int     $direction Direction of operation. Income or Outcome.
     *  Income - user got amount for referral payment
     *  Outcome - user spend amount for pay
     * @param string  $description
     * @throws
     */
    public function setUserBalance($payment, $direction, $description = '')
    {
        if (!in_array($direction, [self::INCOME, self::OUTCOME])) {
            throw new \Exception('bad direction argument!', 400);
        }
        $operationAmount = 0;
        $user = null;

        switch ($direction) {
            case self::INCOME:
                $user = $payment->getUser()->getUserReferral();
                $operationAmount = ReferralService::REFERRAL_BONUS;
                break;
            case self::OUTCOME:
                $user = $payment->getUser();
                $operationAmount = -$payment->getFwdaysAmount();
                break;
        }
        if ($user && 0 != $operationAmount) {
            $currentUserBalance = $this->em->getRepository('ApplicationUserBundle:UserBalance')
                ->findOneBy(['user' => $user], ['id' => 'DESC']);
            if (!$currentUserBalance) {
                $balance = $this->setFirstUserBalance($user);
            } else {
                $balance = $currentUserBalance->getBalance();
            }

            $userBalance = new UserBalance();
            $userBalance
                ->setUser($user)
                ->setPayment($payment)
                ->setOperationAmount($operationAmount)
                ->setBalance($balance + $operationAmount)
                ->setDescription($description);
            ;

            $user->setBalance($userBalance->getBalance());

            $this->em->persist($userBalance);
            $this->em->flush();
        }
    }

    /**
     * Set start balance from user entity
     *
     * @param User $user
     *
     * @return float
     */
    public function setFirstUserBalance($user)
    {
        $userBalance = new UserBalance();
        $userBalance
            ->setUser($user)
            ->setBalance($user->getBalance())
            ->setDescription('Set start balance')
        ;
        $this->em->persist($userBalance);
        $this->em->flush();

        return $user->getBalance();
    }
}
