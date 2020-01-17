<?php

namespace App\Service;

use App\Traits\RouterTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class UrlForRedirect.
 */
class UrlForRedirect
{
    use RouterTrait;

    /** @var array */
    private $homePages = [];
    /** @var array */
    private $authorizationUrls = [];
    private $locales;

    /**
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * get redirect url for referral url.
     *
     * @param string $referralUrl
     * @param string $host
     *
     * @return string
     */
    public function getRedirectUrl($referralUrl, $host = ''): string
    {
        $this->prepare();
        $clearReferrer = trim(preg_replace('/(\?.*)/', '', $referralUrl), '\/');

        if (\in_array($clearReferrer, $this->authorizationUrls)) {
            return $this->router->generate('homepage');
        }

        if (!empty($host) && false === strpos($clearReferrer, $host)) {
            return $this->router->generate('homepage');
        }

        return $referralUrl;
    }

    /**
     * prepare url array.
     */
    private function prepare(): void
    {
        $this->homePages = [];
        $this->authorizationUrls = [];

        $this->authorizationUrls[] = $this->router->generate('fos_user_security_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->authorizationUrls[] = trim($this->router->generate('fos_user_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
        $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_check_email', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_send_email', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->authorizationUrls[] = $this->router->generate('password_already_requested', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->homePages[] = trim($this->router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
        $this->homePages[] = trim($this->router->generate('cabinet', [], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
        foreach ($this->locales as $locale) {
            $this->homePages[] = $this->router->generate('homepage', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->homePages[] = $this->router->generate('cabinet', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->authorizationUrls[] = trim($this->router->generate('fos_user_registration_register', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL), '\/');
            $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_check_email', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->authorizationUrls[] = $this->router->generate('fos_user_resetting_send_email', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->authorizationUrls[] = $this->router->generate('password_already_requested', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
        }
    }
}
