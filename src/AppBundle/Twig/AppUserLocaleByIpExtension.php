<?php

namespace App\Twig;

use App\Service\LocalsRequiredService;
use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * AppUserLocaleByIpExtension.
 */
class AppUserLocaleByIpExtension extends AbstractExtension
{
    private const UKRAINE_COUNTRY_CODE = 'UA';

    private $requestStack;
    private $geoIpService;

    /**
     * @param RequestStack $requestStack
     * @param GeoipManager $geoIpService
     */
    public function __construct(RequestStack $requestStack, GeoipManager $geoIpService)
    {
        $this->requestStack = $requestStack;
        $this->geoIpService = $geoIpService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('app_get_user_locale', [$this, 'getUserLocale']),
        ];
    }

    /**
     * @return string
     */
    public function getUserLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $requestLocal = $request instanceof Request ? $request->getLocale() : LocalsRequiredService::UK_EMAIL_LANGUAGE;
        $ip = $request instanceof Request ? $request->getClientIp() : null;
        $ipLocal = null;

        if (null !== $ip && false !== $this->geoIpService->lookup($ip)) {
            if (self::UKRAINE_COUNTRY_CODE === $this->geoIpService->getCountryCode()) {
                $ipLocal = LocalsRequiredService::UK_EMAIL_LANGUAGE;
            } else {
                $ipLocal = LocalsRequiredService::EN_EMAIL_LANGUAGE;
            }
        }

        return ($ipLocal === $requestLocal && LocalsRequiredService::EN_EMAIL_LANGUAGE === $ipLocal) ?
            LocalsRequiredService::EN_EMAIL_LANGUAGE : LocalsRequiredService::UK_EMAIL_LANGUAGE;
    }
}
