<?php

namespace Application\Bundle\UserBundle\Handler;

use Application\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\I18nRoutingBundle\Router\I18nRouter;
use Stfalcon\Bundle\EventBundle\Service\ReferralService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var UserManagerInterface User manager
     */
    protected $userManager;

    /** @var I18nRouter $router */
    protected $router;

    protected $referralService;

    protected $urlForRedirectService;

    public function __construct(I18nRouter $router, $referralService, $userManager, $urlForRedirectService)
    {
        $this->router = $router;
        $this->referralService = $referralService;
        $this->urlForRedirectService = $urlForRedirectService;
        $this->userManager = $userManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return $this->processAuthSuccess($request, $token->getUser());
    }

    public function processAuthSuccess(Request $request, User $user)
    {
        if ($request->cookies->has(ReferralService::REFERRAL_CODE)) {
            $referralCode = $request->cookies->get(ReferralService::REFERRAL_CODE);

            //check self referral code
            if ($this->referralService->getReferralCode($user) !== $referralCode) {
                $userReferral = $this->userManager->findUserBy(['referralCode' => $referralCode]);

                if ($userReferral) {
                    $user->setUserReferral($userReferral);
                }

                $this->userManager->updateUser($user);
            }
        }

        $key = '_security.main.target_path';
        if ($request->getSession()->has($key)) {
            $url = $request->getSession()->get($key);
            $request->getSession()->remove($key);

            return new RedirectResponse($url);
        }

        $referrer = $request->headers->get('referer');

        $session = $request->getSession();
        if ($session->has('request_params')) {
            $requestParams = $session->get('request_params');
            $request->getSession()->remove('request_params');

            if ($request->query->has('exception_login') || $session->has('login_by_provider')) {
                if ($session->has('login_by_provider')) {
                    $request->getSession()->remove('login_by_provider');
                }

                $url = $referrer;
                if ('event_pay' === $requestParams['_route']) {
                    $response = new RedirectResponse($url);
                    $cookie = new Cookie('event', $requestParams['eventSlug'], time() + 3600, '/', null, false, false);
                    $response->headers->setCookie($cookie);

                    return $response;
                }

                return new RedirectResponse($this->router->generate($requestParams['_route'], $requestParams['_route_params']));
            }
        }

        return new RedirectResponse($this->urlForRedirectService->getRedirectUrl($referrer));
    }
}
