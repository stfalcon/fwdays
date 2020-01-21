<?php

namespace App\Service;

use JMS\I18nRoutingBundle\Router\I18nRouter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class UrlForRedirect.
 */
class UrlForRedirect
{
    /** @var I18nRouter $router */
    protected $router;

    /** @var $homePages */
    private $homePages = [];
    private $authorizationUrls = [];

    /**
     * GetUrlForRedirect constructor.
     *
     * @param I18nRouter $router
     * @param array      $locales
     */
    public function __construct($router, $locales)
    {
        $this->router = $router;

        $this->authorizationUrls[] = $this->router->generate('fos_user_security_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->authorizationUrls[] = trim($this->router->generate('fos_user_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
        $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_check_email', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_send_email', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->authorizationUrls[] = $this->router->generate('password_already_requested', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->homePages[] = trim($router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
        $this->homePages[] = trim($router->generate('cabinet', [], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
        foreach ($locales as $locale) {
            $this->homePages[] = $router->generate('homepage', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->homePages[] = $router->generate('cabinet', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->authorizationUrls[] = trim($this->router->generate('fos_user_registration_register', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
            $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_check_email', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_send_email', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->authorizationUrls[] = $this->router->generate('password_already_requested', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
        }
    }

    /**
     * get redirect url for referral url.
     *
     * @param string $referralUrl
     * @param string $host
     *
     * @return string
     */
    public function getRedirectUrl(?string $referralUrl, string $host = ''): string
    {
        $clearReferrer = trim(preg_replace('/(\?.*)/', '', $referralUrl), '\/');
        $homePage = $this->router->generate('homepage');

        if (\in_array($clearReferrer, $this->authorizationUrls, true)) {
            return $homePage;
        }

        if (!empty($host) && false === strpos($clearReferrer, $host)) {
            return $homePage;
        }

        return $referralUrl ?? $homePage;
    }
}
