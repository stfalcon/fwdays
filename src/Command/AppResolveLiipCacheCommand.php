<?php

namespace App\Command;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AppResolveLiipCacheCommand.
 */
class AppResolveLiipCacheCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;
    /** @var CacheManager */
    protected $cacheManager;
    /** @var FilterManager */
    protected $filterManager;
    /** @var DataManager */
    protected $dataManager;

    private const FILTER_MAPPING = [
        'speaker' => 'speaker_photo',
        'partner' => 'sponsor_image',
        'upload_image' => 'upload_image',
    ];

    /**
     * Set options.
     */
    protected function configure(): void
    {
        $this
            ->setName('app:liip_imagine:cache:resolve')
            ->setDescription('liip imagine resolve cache for all files by filter')
            ->addArgument('filter', InputArgument::REQUIRED, 'Filter name to resolve caches for')
            ->addOption('force', 'F', InputOption::VALUE_OPTIONAL, 'Force asset cache resolution (ignoring whether it already cached)')
        ;
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        $filter = $input->getArgument('filter');
        $doForce = (bool) $input->getOption('force');
        try {
            $fileSystemName = self::FILTER_MAPPING[$filter];
        } catch (\Exception $e) {
            $this->writeActionDetail($e->getMessage());
            exit;
        }

        $container = $this->getContainer();
        $filesystem = $container->get('oneup_flysystem.'.$fileSystemName.'_filesystem');
        $this->cacheManager = $container->get('liip_imagine.cache.manager');
        $this->filterManager = $container->get('liip_imagine.filter.manager');
        $this->dataManager = $container->get('liip_imagine.data.manager');

        $contents = $filesystem->listContents('/', true);
        foreach ($contents as $contentItem) {
            if ('file' !== $contentItem['type'] || !empty($contentItem['dirname'])) {
                continue;
            }
            $this->doCacheResolve($contentItem['path'], $filter, $doForce);
        }
    }

    /**
     * @param string      $filter
     * @param string|null $target
     */
    protected function writeActionStart($filter, $target = null): void
    {
        $this->output->write(sprintf('%s[%s] ', $target ?: '*', $filter));
    }

    /**
     * @param string $result
     * @param bool   $continued
     */
    protected function writeActionResult($result, $continued = true): void
    {
        $this->output->write($continued ? sprintf('%s: ', $result) : $result);

        if (!$continued) {
            $this->writeNewline();
        }
    }

    /**
     * @param string $detail
     */
    protected function writeActionDetail($detail): void
    {
        $this->output->write($detail);
        $this->writeNewline();
    }

    /**
     * @param int $count
     */
    private function writeNewline($count = 1): void
    {
        $this->output->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * @param string $target
     * @param string $filter
     * @param bool   $forced
     */
    private function doCacheResolve(string $target, string $filter, bool $forced): void
    {
        $this->writeActionStart($filter, $target);

        try {
            if ($forced || !$this->cacheManager->isStored($target, $filter)) {
                $this->cacheManager->store($this->filterManager->applyFilter($this->dataManager->find($filter, $target), $filter), $target, $filter);
                $this->writeActionResult('resolved');
            } else {
                $this->writeActionResult('skipped');
            }

            $this->writeActionDetail($this->cacheManager->resolve($target, $filter));
        } catch (\Exception $e) {
            $this->writeActionDetail($e->getMessage());
        }
    }
}
