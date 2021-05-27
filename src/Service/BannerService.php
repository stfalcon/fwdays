<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Banner;
use App\Repository\BannerRepository;
use App\Traits\RequestStackTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BannerService.
 */
class BannerService
{
    use RequestStackTrait;

    public const BANNER_COOKIE = 'closed_banners';
    private const MAX_COOKIE_VALUE = 4096;

    /** @var BannerRepository */
    private $bannerRepository;

    /**
     * @param BannerRepository $bannerRepository
     */
    public function __construct(BannerRepository $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * @param Banner   $banner
     * @param Response $response
     */
    public function addBannerToCloseResponseCookie(Banner $banner, Response $response): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $cookie = $request->cookies->get(self::BANNER_COOKIE, null);
        $cookiesArray = $this->decodeCookie($cookie);

        $cookiesArray[] = $banner->getId();

        $encodedCookie = $this->encodeCookie($cookiesArray);

        if (\strlen($encodedCookie) >= self::MAX_COOKIE_VALUE) {
            $encodedCookie = $this->refactorBannerCookie($cookiesArray);
        }

        $expire = time() + (10 * 365 * 24 * 3600);
        $response->headers->setCookie(new Cookie(self::BANNER_COOKIE, $encodedCookie, $expire));
    }

    /**
     * @return Banner|null
     */
    public function getActiveBannerWithOutCookieClosed(): ?Banner
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $cookie = $request->cookies->get(self::BANNER_COOKIE, null);
        $cookiesArray = $this->decodeCookie($cookie);

        $banners = $this->bannerRepository->getActiveBannersWithOutIncluded($cookiesArray);

        $currentUri = $request->getRequestUri();

        foreach ($banners as $banner) {
            if (\preg_match('~'.$banner->getUrl().'~', $currentUri)) {
                return $banner;
            }
        }

        return null;
    }

    /**
     * @param array $cookiesArray
     *
     * @return string
     */
    private function refactorBannerCookie(array $cookiesArray): string
    {
        $activeBanners = $this->bannerRepository->getActiveBannersIncluded($cookiesArray);

        $cookiesArray = [];
        foreach ($activeBanners as $banner) {
            $cookiesArray[] = $banner->getId();
        }

        return $this->encodeCookie($cookiesArray);
    }

    /**
     * @param array $cookiesArray
     *
     * @return string|null
     */
    private function encodeCookie(array $cookiesArray): ?string
    {
        $cookiesArray = \array_unique($cookiesArray);

        $encoded = \json_encode($cookiesArray);
        if (!\is_string($encoded)) {
            return null;
        }

        return \base64_encode($encoded);
    }

    /**
     * @param string|null $cookie
     *
     * @return array
     */
    private function decodeCookie(?string $cookie): array
    {
        $result = null !== $cookie ? \json_decode(\base64_decode($cookie)) : [];

        if (!\is_array($result)) {
            $result = [];
        }

        return $result;
    }
}
