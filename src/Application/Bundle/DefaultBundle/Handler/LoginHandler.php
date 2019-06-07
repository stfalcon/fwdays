<?php

namespace Application\Bundle\DefaultBundle\Handler;

use Application\Bundle\DefaultBundle\Service\UrlForRedirect;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Model\UserManager;
use JMS\I18nRoutingBundle\Router\I18nRouter;
use Application\Bundle\DefaultBundle\Service\ReferralService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class LoginHandler.
 */
class LoginHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var UserManager User manager
     */
    protected $userManager;

    /** @var I18nRouter $router */
    protected $router;

    /** @var ReferralService */
    protected $referralService;

    /** @var UrlForRedirect */
    protected $urlForRedirectService;

    /**
     * LoginHandler constructor.
     *
     * @param I18nRouter      $router
     * @param ReferralService $referralService
     * @param UserManager     $userManager
     * @param UrlForRedirect  $urlForRedirectService
     */
    public function __construct(I18nRouter $router, $referralService, $userManager, $urlForRedirectService)
    {
        $this->router = $router;
        $this->referralService = $referralService;
        $this->urlForRedirectService = $urlForRedirectService;
        $this->userManager = $userManager;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return $this->processAuthSuccess($request, $token->getUser());
    }

    /**
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse
     */
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
        if ($session instanceof SessionInterface && $session->has('request_params')) {
            $requestParams = $session->get('request_params');
            $session->remove('request_params');

            if ($request->query->has('exception_login') || $request->cookies->has('bye-event')) {
                $url = $this->urlForRedirectService->getRedirectUrl($referrer, $request->getHost());
                if ('event_pay' === $requestParams['_route']) {
                    $response = new RedirectResponse($url);
                    $cookie = new Cookie('event', $requestParams['eventSlug'], time() + 3600, '/', null, false, false);
                    $response->headers->setCookie($cookie);

                    return $response;
                }

                return new RedirectResponse($this->router->generate($requestParams['_route'], $requestParams['_route_params']));
            }
        }

        return new RedirectResponse($this->urlForRedirectService->getRedirectUrl($referrer, $request->getHost()));
    }
}
