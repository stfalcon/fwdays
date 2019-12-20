<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AssetVersionExtension.
 *
 * @author Timur Bolotiukh <timur.bolotyuh@gmail.com>
 */
class AssetVersionExtension extends AbstractExtension
{
    /** @var string */
    private $webRoot;

    /** @var string */
    private $environment;

    const REV_MANIFEST_FILE = 'rev-manifest.json';

    /**
     * AssetVersionExtension constructor.
     *
     * @param string $projectDir
     * @param string $environment
     */
    public function __construct(string $projectDir, string $environment)
    {
        $this->webRoot = \realpath($projectDir.'/../web');
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('app_asset_version', [$this, 'getAssetVersion']),
        ];
    }

    /**
     * @param string $asset
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAssetVersion($asset)
    {
        $path = pathinfo($this->webRoot.\DIRECTORY_SEPARATOR.$asset);
        $manifestFile = $path['dirname'].\DIRECTORY_SEPARATOR.self::REV_MANIFEST_FILE;

        if (!file_exists($manifestFile)) {
            throw new \Exception(sprintf('Cannot find manifest file: "%s"', $manifestFile));
        }

        $manifestPaths = json_decode(file_get_contents($manifestFile), true);

        if (!isset($manifestPaths[$path['basename']])) {
            throw new \Exception(sprintf('There is no file "%s" in the version manifest!', $path['basename']));
        }

        return pathinfo($asset)['dirname'].\DIRECTORY_SEPARATOR.$manifestPaths[$path['basename']];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_asset_version';
    }
}
