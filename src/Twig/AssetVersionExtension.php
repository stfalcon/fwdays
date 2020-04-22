<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * AssetVersionExtension.
 *
 * @author Timur Bolotiukh <timur.bolotyuh@gmail.com>
 */
class AssetVersionExtension extends AbstractExtension
{
    /** @var string */
    private $webRoot;

    const REV_MANIFEST_FILE = 'rev-manifest.json';

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $path = \realpath($projectDir.'/public');
        $this->webRoot = $path ? $path : '';
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
        $path = \pathinfo(\sprintf('%s/%s', $this->webRoot, $asset));
        $manifestFile = $path['dirname'].\DIRECTORY_SEPARATOR.self::REV_MANIFEST_FILE;

        if (!\file_exists($manifestFile)) {
            throw new \Exception(\sprintf('Cannot find manifest file: "%s"', $manifestFile));
        }

        $fileContent = \file_get_contents($manifestFile);
        $manifestPaths = $fileContent ? \json_decode($fileContent, true) : [];

        if (!isset($manifestPaths[$path['basename']])) {
            throw new \Exception(\sprintf('There is no file "%s" in the version manifest!', $path['basename']));
        }

        return \pathinfo($asset)['dirname'].\DIRECTORY_SEPARATOR.$manifestPaths[$path['basename']];
    }
}
