<?php

namespace Application\Bundle\DefaultBundle\Twig;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class AppImagineExtension extends \Twig_Extension
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * Constructor.
     *
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('app_imagine_filter', [$this, 'filter']),
        );
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     * @param string $resolver
     *
     * @return string
     */
    public function filter($path, $filter, array $runtimeConfig = array(), $resolver = null)
    {
        if (false !== \strpos($path, '://')) {
            $path = \str_replace('://', '', $path);
            $path = \substr($path, \strpos($path, '/') + 1);
        }

        return $this->cacheManager->getBrowserPath($path, $filter, $runtimeConfig, $resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_liip_imagine';
    }
}
