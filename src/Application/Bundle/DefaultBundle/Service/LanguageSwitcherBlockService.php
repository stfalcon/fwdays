<?php

namespace Application\Bundle\DefaultBundle\Service;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LanguageSwitcherBlockService.
 */
class LanguageSwitcherBlockService extends AbstractBlockService
{
    /** @var Request */
    private $request;

    /** @var array */
    private $locales;

    /**
     * ProgramEventBlockService constructor.
     *
     * @param string          $name
     * @param EngineInterface $templating
     * @param RequestStack    $requestStack
     * @param array           $locales
     */
    public function __construct($name, EngineInterface $templating, RequestStack $requestStack, array $locales)
    {
        parent::__construct($name, $templating);

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
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'ApplicationDefaultBundle:Redesign:language_switcher.html.twig',
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
    private function getInnerSubstring($string, $delim, $keyNumber = 1)
    {
        $string = explode($delim, $string, 3);

        return isset($string[$keyNumber]) ? $string[$keyNumber] : '';
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
