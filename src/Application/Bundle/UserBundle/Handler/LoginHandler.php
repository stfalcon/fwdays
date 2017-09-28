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
     * @var UserManagerInterface $userManager User manager
     */
    protected $userManager;

    /** @var I18nRouter $router */
    protected $router;

    protected $referralService;

    /** @var $homePages */
    private $homePages = [];

    public function __construct(I18nRouter $router, $locales, $referralService, $userManager)
    {
        $this->router = $router;
        $this->referralService = $referralService;
        $this->userManager = $userManager;
        $this->homePages[] = trim($router->generate('homepage', [], true), '\/');
        foreach ($locales as $locale) {
            $this->homePages[] = $router->generate('homepage', ['_locale' => $locale], true);
        }
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

        $loginCodeUrl = $this->router->generate('fos_user_security_login',[], true);
        $registerCodeUrl = $this->router->generate('fos_user_registration_register',[], true);
        $referrer = $request->headers->get('referer');
        $cabinetUrl = $this->router->generate('cabinet');

        $session = $request->getSession();
        if ($session->has('request_params')) {
            $requestParams = $session->get('request_params');
            $request->getSession()->remove('request_params');

            if ($request->query->has('exception_login')) {
                $url = $referrer;
                if ('event_pay' === $requestParams['_route']) {
                    if (in_array($referrer, [$loginCodeUrl, $registerCodeUrl])) {
                        $url = $this->router->generate('homepage');
                    }
                    $response = new RedirectResponse($url);
                    $cookie = new Cookie('event', $requestParams['event_slug'], time() + 3600, '/', null, false, false);
                    $response->headers->setCookie($cookie);

                    return $response;
                }

                return new RedirectResponse($this->router->generate($requestParams['_route'], $requestParams['_route_params']));
            }
        }

        $clearReferrer = trim(preg_replace('/(\?.*)/', '', $referrer), '\/');

        if (in_array($clearReferrer, $this->homePages)) {
            return new RedirectResponse($cabinetUrl);
        }

        if (in_array($referrer, [$loginCodeUrl, $registerCodeUrl])) {
            return new RedirectResponse($this->router->generate('homepage'));
        } else {
            return new RedirectResponse($referrer);
        }
    }
}
