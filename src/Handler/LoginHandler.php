<?php

namespace App\Handler;

use App\Entity\Referer\Referer;
use App\Entity\User;
use App\Model\UserManager;
use App\Service\RefererService;
use App\Service\ReferralService;
use App\Service\UrlForRedirect;
use App\Traits\RouterTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * LoginHandler.
 */
class LoginHandler implements AuthenticationSuccessHandlerInterface
{
    use RouterTrait;

    private $userManager;
    private $referralService;
    private $urlForRedirectService;

    /** @var RefererService */
    private $refererService;

    /**
     * @param ReferralService $referralService
     * @param UserManager     $userManager
     * @param UrlForRedirect  $urlForRedirectService
     * @param RefererService  $refererService
     */
    public function __construct(ReferralService $referralService, UserManager $userManager, UrlForRedirect $urlForRedirectService, RefererService $refererService)
    {
        $this->referralService = $referralService;
        $this->urlForRedirectService = $urlForRedirectService;
        $this->userManager = $userManager;
        $this->refererService = $refererService;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $user = $user instanceof User ? $user : null;

        return $this->processAuthSuccess($request, $user);
    }

    /**
     * @param Request   $request
     * @param User|null $user
     *
     * @return RedirectResponse
     */
    public function processAuthSuccess(Request $request, ?User $user)
    {
        if ($request->cookies->has(ReferralService::REFERRAL_COOKIE_NAME)) {
            $referralCode = $request->cookies->get(ReferralService::REFERRAL_COOKIE_NAME);
            if ($user && $this->referralService->getReferralCode($user) !== $referralCode) {
                $userReferral = $this->userManager->findUserBy(['referralCode' => $referralCode]);
                if ($userReferral) {
                    $user->setUserReferral($userReferral);
                }
                $this->userManager->updateUser($user);
            }
        }

        if ($user instanceof User) {
            $refererCookieId = $request->cookies->get(Referer::COOKIE_KEY, null);
            if (\is_string($refererCookieId)) {
                $this->refererService->checkUserCookieRelation($user, $refererCookieId);
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
