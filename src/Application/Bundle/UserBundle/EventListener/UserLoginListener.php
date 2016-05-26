<?php

namespace Application\Bundle\UserBundle\EventListener;

use Application\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Stfalcon\Bundle\EventBundle\Service\ReferralService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * UserLoginListener
 */
class UserLoginListener
{
    /**
     * @var UserManagerInterface $userManager User manager
     */
    protected $userManager;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param UserManagerInterface $userManager User manager
     * @param Container $container
     */
    public function __construct(UserManagerInterface $userManager, $container)
    {
        $this->userManager = $userManager;
        $this->container = $container;
        $this->request   = $this->container->get('request_stack');

    }

    /**
     * On user login
     *
     * @param InteractiveLoginEvent $event Event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /**
         * @var User $user
         */
        $user = $event->getAuthenticationToken()->getUser();
        $referralService = $this->container->get('stfalcon_event.referral.service');

        if ($this->request->cookies->has(ReferralService::REFERRAL_CODE)) {
            $referralCode = $this->request->cookies->get(ReferralService::REFERRAL_CODE);

            //check self referral code
            if ($referralService->getReferralCode($user) !== $referralCode) {

                $userReferral = $this->userManager->findUserBy(['referralCode' => $referralCode]);

                if ($userReferral) {
                    $user->setUserReferral($userReferral);
                }

                $this->userManager->updateUser($user);
            }
        }
    }
}
