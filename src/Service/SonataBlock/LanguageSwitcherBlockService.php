<?php

namespace App\Service\SonataBlock;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * LanguageSwitcherBlockService.
 */
class LanguageSwitcherBlockService extends AbstractBlockService
{
    /** @var Request */
    private $request;

    /** @var array */
    private $locales;

    /**
     * @param Environment  $twig
     * @param RequestStack $requestStack
     * @param array        $locales
     */
    public function __construct(Environment $twig, RequestStack $requestStack, array $locales)
    {
        parent::__construct($twig);

        $this->request = $requestStack->getCurrentRequest();
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $position = $blockContext->getSetting('position');
        $localesArr = [];
        foreach ($this->locales as $locale) {
            $localesArr[$locale] = $this->localizeRoute($this->request, $locale);
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'locales' => $localesArr,
            'position' => $position,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/language_switcher.html.twig',
            'position' => 'header',
        ]);
    }

    /**
     * Get inner sub string in position number.
     *
     * @param string $string
     * @param string $delim
     * @param int    $keyNumber
     *
     * @return string
     */
    private function getInnerSubstring(string $string, string $delim, int $keyNumber = 1): string
    {
        $result = \explode($delim, $string, 3);

        return (\is_array($result) && isset($result[$keyNumber])) ? (string) $result[$keyNumber] : '';
    }

    /**
     * Localize current route.
     *
     * @param Request $request
     * @param string  $locale
     *
     * @return string
     */
    private function localizeRoute($request, $locale)
    {
        $path = $request->getPathInfo();
        $currentLocal = $this->getInnerSubstring($path, '/');
        if (\in_array($currentLocal, $this->locales)) {
            $path = preg_replace('/^\/'.$currentLocal.'\//', '/', $path);
        }
        $params = $request->query->all();

        return $request->getBaseUrl().'/'.$locale.$path.($params ? '?'.http_build_query($params) : '');
    }
}
