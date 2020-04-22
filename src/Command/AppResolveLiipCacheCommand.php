<?php

namespace App\Command;

use App\Exception\Console\InvalidParameterException;
use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AppResolveLiipCacheCommand.
 */
class AppResolveLiipCacheCommand extends Command implements ContainerAwareInterface
{
    /** @var OutputInterface */
    private $output;
    /** @var CacheManager */
    private $cacheManager;
    /** @var FilterManager */
    private $filterManager;
    /** @var DataManager */
    private $dataManager;

    /** @var ContainerInterface */
    private $container;

    private const FILTER_MAPPING = [
        'speaker' => 'speaker_photo',
        'partner' => 'sponsor_image',
        'upload_image' => 'upload_image',
    ];

    /**
     * @param CacheManager  $cacheManager
     * @param FilterManager $filterManager
     * @param DataManager   $dataManager
     */
    public function __construct(CacheManager $cacheManager, FilterManager $filterManager, DataManager $dataManager)
    {
        parent::__construct();

        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

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

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $filter = $input->getArgument('filter');
        $doForce = (bool) $input->getOption('force');

        if (!\is_string($filter)) {
            throw new InvalidParameterException('Argument `filter` is not a string');
        }

        if (!isset(self::FILTER_MAPPING[$filter])) {
            throw new InvalidParameterException('Unknown filter');
        }

        $fileSystemName = self::FILTER_MAPPING[$filter];

        /** @var FilesystemInterface $filesystem */
        $filesystem = $this->container->get(\sprintf('oneup_flysystem.%s_filesystem', $fileSystemName));

        $contents = $filesystem->listContents('/', true);
        foreach ($contents as $contentItem) {
            if ('file' !== $contentItem['type'] || !empty($contentItem['dirname'])) {
                continue;
            }
            $this->doCacheResolve($contentItem['path'], $filter, $doForce);
        }

        return 0;
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
