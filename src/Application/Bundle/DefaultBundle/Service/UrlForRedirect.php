<?php

namespace Application\Bundle\DefaultBundle\Service;

use JMS\I18nRoutingBundle\Router\I18nRouter;

class UrlForRedirect
{
    /** @var I18nRouter $router */
    protected $router;

    /** @var $homePages */
    private $homePages = [];
    private $authorizationUrls = [];

    /**
     * GetUrlForRedirect constructor.
     * @param I18nRouter $router
     * @param array      $locales
     */
    public function __construct($router, $locales)
    {
        $this->router = $router;

        $this->authorizationUrls[] = $this->router->generate('fos_user_security_login', [], true);
        $this->authorizationUrls[] = trim($this->router->generate('fos_user_registration_register', [], true), '\/');
        $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_check_email', [], true);
        $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_send_email', [], true);

        $this->homePages[] = trim($router->generate('homepage', [], true), '\/');
        $this->homePages[] = trim($router->generate('cabinet', [], true), '\/');
        foreach ($locales as $locale) {
            $this->homePages[] = $router->generate('homepage', ['_locale' => $locale], true);
            $this->homePages[] = $router->generate('cabinet', ['_locale' => $locale], true);

            $this->authorizationUrls[] = trim($this->router->generate('fos_user_registration_register', ['_locale' => $locale], true), '\/');
            $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_check_email', ['_locale' => $locale], true);
            $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_send_email', ['_locale' => $locale], true);
        }
    }

    /**
     * get redirect url for referral url
     *
     * @param string $referralUrl
     * @param string $host
     *
     * @return string
     */
    public function getRedirectUrl($referralUrl, $host = '')
    {
        $clearReferrer = trim(preg_replace('/(\?.*)/', '', $referralUrl), '\/');
        if (in_array($clearReferrer, $this->homePages)) {
            return $this->router->generate('cabinet');
        }

        if (in_array($clearReferrer, $this->authorizationUrls)) {
            return $this->router->generate('cabinet');
        }

        if (!empty($host) && false === strpos($clearReferrer, $host)) {
            return $this->router->generate('cabinet');
        }

        return $referralUrl;
    }
}
